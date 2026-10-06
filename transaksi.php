<?php
session_start();
include 'koneksi.php';
/** @var mysqli $koneksi */

// 1. Cek Login User
$id_user = $_SESSION['user_id'] ?? $_SESSION['id_user'] ?? $_SESSION['id'] ?? null;
if (!$id_user) {
    echo "<script>
            alert('Silakan login terlebih dahulu untuk melakukan transaksi!');
            window.location.href = 'login.php';
          </script>";
    exit;
}

// 2. Cek Keranjang
if (empty($_SESSION['keranjang'])) {
    echo "<script>
            alert('Keranjang belanja Anda masih kosong!');
            window.location.href = 'menu.php';
          </script>";
    exit;
}

// 3. Ambil Data User
$stmt_user = mysqli_prepare($koneksi, "SELECT * FROM tb_user WHERE id = ?");
mysqli_stmt_bind_param($stmt_user, "i", $id_user);
mysqli_stmt_execute($stmt_user);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_user));

if (!$user) {
    unset($_SESSION['user_id'], $_SESSION['id_user'], $_SESSION['id']);
    $_SESSION['error'] = "Akun tidak ditemukan. Silakan login kembali.";
    header("Location: login.php");
    exit;
}

$nama_penerima_default = $user['name'] ?? $user['username'] ?? '';

$pesan = "";
$status = "";
$id_transaksi_baru = null;

// 4. Proses Simpan Transaksi
if (isset($_POST['proses_checkout'])) {
    $metode_bayar = $_POST['metode_pembayaran'] ?? '';
    $metode_pengiriman = $_POST['metode_pengiriman'] ?? '';
    $alamat_input = trim($_POST['alamat'] ?? '');
    $hp_penerima = trim($_POST['hp'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');
    $metode_bayar_valid = ['QRIS', 'Transfer Bank', 'Cash / Tunai'];
    $metode_pengiriman_valid = ['antar', 'ambil'];

    if (!in_array($metode_bayar, $metode_bayar_valid, true)) {
        $pesan = "Pilih metode pembayaran yang tersedia.";
        $status = "error";
    } elseif (!in_array($metode_pengiriman, $metode_pengiriman_valid, true)) {
        $pesan = "Pilih metode pengiriman yang tersedia.";
        $status = "error";
    } elseif ($metode_pengiriman === 'antar' && $alamat_input === '') {
        $pesan = "Alamat lengkap diperlukan untuk pengiriman.";
        $status = "error";
    } elseif ($hp_penerima === '') {
        $pesan = "Nomor telepon penerima wajib diisi.";
        $status = "error";
    } else {
        $alamat = $metode_pengiriman === 'ambil' ? 'Ambil sendiri di kedai' : $alamat_input;
        $tanggal_sekarang = date('Y-m-d H:i:s');
        $total_harga = 0;
        $detail_pesanan = [];
        $stmt_produk = mysqli_prepare($koneksi, "SELECT harga FROM tb_produk WHERE id = ?");

        foreach ($_SESSION['keranjang'] as $key => $item) {
            $id_produk = (int) ($item['id'] ?? $key);
            $jumlah = (int) (is_array($item) ? ($item['jumlah'] ?? 0) : $item);
            if ($id_produk <= 0 || $jumlah <= 0) {
                continue;
            }

            mysqli_stmt_bind_param($stmt_produk, "i", $id_produk);
            if (!mysqli_stmt_execute($stmt_produk)) {
                $pesan = "Gagal memeriksa produk: " . mysqli_stmt_error($stmt_produk);
                $status = "error";
                break;
            }
            $hasil_produk = mysqli_stmt_get_result($stmt_produk);
            $produk = mysqli_fetch_assoc($hasil_produk);
            if (!$produk) {
                $pesan = "Salah satu produk di keranjang tidak ditemukan.";
                $status = "error";
                break;
            }

            $total_harga += (int) $produk['harga'] * $jumlah;
            $detail_pesanan[] = ['id_produk' => $id_produk, 'jumlah' => $jumlah];
        }

        if ($status !== "error" && !$detail_pesanan) {
            $pesan = "Keranjang Anda tidak memiliki produk yang valid.";
            $status = "error";
        }

        if ($status !== "error") {
            mysqli_begin_transaction($koneksi);
            $stmt_trx = mysqli_prepare($koneksi, "INSERT INTO tb_transaksi (id_pelanggan, tanggal, total_harga, metode_pembayaran, alamat_pengiriman, hp_pengiriman, catatan, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");

            if ($stmt_trx) {
                mysqli_stmt_bind_param($stmt_trx, "isissss", $id_user, $tanggal_sekarang, $total_harga, $metode_bayar, $alamat, $hp_penerima, $catatan);

                if (mysqli_stmt_execute($stmt_trx)) {
                    $id_transaksi_baru = mysqli_insert_id($koneksi);
                    $stmt_detail = mysqli_prepare($koneksi, "INSERT INTO tb_detail (id_transaksi, id_produk, jumlah) VALUES (?, ?, ?)");

                    if (!$stmt_detail) {
                        $pesan = "Pesanan gagal disimpan: " . mysqli_error($koneksi);
                        $status = "error";
                    } else {
                        foreach ($detail_pesanan as $detail) {
                            mysqli_stmt_bind_param($stmt_detail, "iii", $id_transaksi_baru, $detail['id_produk'], $detail['jumlah']);
                            if (!mysqli_stmt_execute($stmt_detail)) {
                                $pesan = "Pesanan gagal disimpan: " . mysqli_stmt_error($stmt_detail);
                                $status = "error";
                                break;
                            }
                        }
                    }

                    if ($status !== "error" && mysqli_commit($koneksi)) {
                        unset($_SESSION['keranjang']);
                        $pesan = "Pesanan berhasil dibuat!";
                        $status = "success";
                    } elseif ($status !== "error") {
                        $pesan = "Gagal menyelesaikan penyimpanan pesanan: " . mysqli_error($koneksi);
                        $status = "error";
                    }
                } else {
                    $pesan = "Gagal memproses transaksi: " . mysqli_stmt_error($stmt_trx);
                    $status = "error";
                }
            } else {
                $pesan = "Gagal menyiapkan transaksi: " . mysqli_error($koneksi);
                $status = "error";
            }

            if ($status !== "success") {
                mysqli_rollback($koneksi);
                $id_transaksi_baru = null;
            }
        }
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout Pembelian - Kopi Singgah</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: '#F9F6F0',
                        warm: '#F3EFE6',
                        'stone-ink': '#1E1B18',
                        clay: '#A96B51',
                        sand: '#D8CAB3',
                        sage: '#7C8A6C',
                    },
                    fontFamily: {
                        serif: ['Fraunces', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft': '0 10px 40px rgba(0, 0, 0, 0.05)',
                    }
                }
            }
        }
    </script>
    <style>
        .swal2-popup { border-radius: 1.5rem !important; font-family: 'Inter', sans-serif !important; }
    </style>
</head>
<body class="bg-cream text-stone-ink font-sans antialiased min-h-screen flex flex-col">

    <!-- NAVBAR -->
    <nav class="w-full bg-cream/90 backdrop-blur-md sticky top-0 z-50 border-b border-sand/30">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-12 py-4 flex items-center justify-between">
            <a href="index.php" class="font-serif text-xl md:text-2xl font-semibold flex items-center gap-2">
                <svg class="w-6 h-6 text-clay" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                Kopi Singgah.
            </a>
            <div class="hidden lg:flex items-center gap-10 text-sm font-medium">
                <a href="index.php" class="text-stone-ink/60 hover:text-stone-ink transition">Beranda</a>
                <a href="menu.php" class="text-stone-ink/60 hover:text-stone-ink transition">Menu</a>
                <a href="keranjang.php" class="text-stone-ink/60 hover:text-stone-ink transition">Keranjang</a>
            </div>
            <div class="flex items-center gap-5">
                <a href="profile.php" class="border-b border-stone-ink pb-1 font-semibold">Profil</a>
                <a href="logout.php" class="hidden md:inline-block border border-stone-ink text-stone-ink text-xs font-semibold px-5 py-2.5 rounded-full hover:bg-stone-ink hover:text-white transition">Keluar</a>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="flex-grow py-10">
        <div class="max-w-[1100px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-8">
                <a href="keranjang.php" class="inline-flex items-center gap-2 text-xs font-semibold text-stone-ink/60 hover:text-clay transition mb-3">
                    &larr; Kembali ke Keranjang
                </a>
                <h1 class="font-serif text-3xl md:text-4xl text-stone-ink">Konfirmasi Pembelian</h1>
                <p class="text-xs text-stone-ink/60 mt-1">Periksa kembali rincian item dan alamat pengiriman Anda.</p>
            </div>

            <form method="POST" action="transaksi.php" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- KOLOM KIRI: FORM PENGIRIMAN & PEMBAYARAN (7 Cols) -->
                <div class="lg:col-span-7 space-y-6">
                    
                    <!-- INFORMASI PENERIMA -->
                    <div class="bg-white rounded-[2rem] shadow-soft p-6 md:p-8 border border-sand/30">
                        <h2 class="font-serif text-xl mb-5 pb-3 border-b border-sand/30 flex items-center gap-2">
                            <svg class="w-5 h-5 text-clay" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Alamat Pengiriman
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-stone-ink/60 uppercase tracking-widest mb-1">Nama Penerima</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($nama_penerima_default) ?>" required
                                       class="w-full bg-warm/30 border border-sand/50 rounded-xl px-4 py-3 text-sm text-stone-ink focus:outline-none focus:border-clay focus:ring-1 focus:ring-clay">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-ink/60 uppercase tracking-widest mb-1">No. Telepon / WhatsApp</label>
                                <input type="text" name="hp" value="<?= htmlspecialchars($user['hp'] ?? '') ?>" required placeholder="08xxxxxxxxxx"
                                       class="w-full bg-warm/30 border border-sand/50 rounded-xl px-4 py-3 text-sm text-stone-ink focus:outline-none focus:border-clay focus:ring-1 focus:ring-clay">
                            </div>

                            <div id="alamat-pengiriman-wrapper">
                                <label class="block text-xs font-bold text-stone-ink/60 uppercase tracking-widest mb-1">Alamat Lengkap</label>
                                <textarea id="alamat-pengiriman" name="alamat" rows="3" placeholder="Jalan, No. Rumah, Kecamatan, Kota..."
                                          class="w-full bg-warm/30 border border-sand/50 rounded-xl px-4 py-3 text-sm text-stone-ink focus:outline-none focus:border-clay focus:ring-1 focus:ring-clay"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-ink/60 uppercase tracking-widest mb-1">Catatan Tambahan (Opsional)</label>
                                <input type="text" name="catatan" placeholder="Contoh: Kopi Less Sugar, Jangan terlalu manis"
                                       class="w-full bg-warm/30 border border-sand/50 rounded-xl px-4 py-3 text-sm text-stone-ink focus:outline-none focus:border-clay focus:ring-1 focus:ring-clay">
                            </div>
                        </div>
                    </div>

                    <!-- METODE PENGIRIMAN -->
                    <div class="bg-white rounded-[2rem] shadow-soft p-6 md:p-8 border border-sand/30">
                        <h2 class="font-serif text-xl mb-5 pb-3 border-b border-sand/30">Metode Pengiriman</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="border border-sand/50 rounded-2xl p-4 flex items-center gap-3 cursor-pointer hover:border-clay transition has-[:checked]:border-clay has-[:checked]:bg-warm/40">
                                <input type="radio" name="metode_pengiriman" value="antar" checked class="accent-clay">
                                <span class="font-bold text-sm">Diantar ke alamat</span>
                            </label>
                            <label class="border border-sand/50 rounded-2xl p-4 flex items-center gap-3 cursor-pointer hover:border-clay transition has-[:checked]:border-clay has-[:checked]:bg-warm/40">
                                <input type="radio" name="metode_pengiriman" value="ambil" class="accent-clay">
                                <span class="font-bold text-sm">Ambil sendiri di kedai</span>
                            </label>
                        </div>
                        <p class="text-xs text-stone-ink/50 mt-3">Ongkos pengiriman saat ini gratis.</p>
                    </div>

                    <!-- METODE PEMBAYARAN -->
                    <div class="bg-white rounded-[2rem] shadow-soft p-6 md:p-8 border border-sand/30">
                        <h2 class="font-serif text-xl mb-5 pb-3 border-b border-sand/30 flex items-center gap-2">
                            <svg class="w-5 h-5 text-clay" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            Metode Pembayaran
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="border border-sand/50 rounded-2xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-clay transition has-[:checked]:border-clay has-[:checked]:bg-warm/40">
                                <input type="radio" name="metode_pembayaran" value="QRIS" checked class="accent-clay">
                                <span class="font-bold text-xs">QRIS / E-Wallet</span>
                            </label>

                            <label class="border border-sand/50 rounded-2xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-clay transition has-[:checked]:border-clay has-[:checked]:bg-warm/40">
                                <input type="radio" name="metode_pembayaran" value="Transfer Bank" class="accent-clay">
                                <span class="font-bold text-xs">Transfer Bank</span>
                            </label>

                            <label class="border border-sand/50 rounded-2xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-clay transition has-[:checked]:border-clay has-[:checked]:bg-warm/40">
                                <input type="radio" name="metode_pembayaran" value="Cash / Tunai" class="accent-clay">
                                <span class="font-bold text-xs">Bayar di Tempat</span>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- KOLOM KANAN: RINGKASAN PESANAN (5 Cols) -->
                <div class="lg:col-span-5">
                    <div class="bg-white rounded-[2rem] shadow-soft p-6 md:p-8 border border-sand/30 sticky top-28">
                        <h2 class="font-serif text-xl mb-4 pb-3 border-b border-sand/30">Ringkasan Pesanan</h2>

                        <!-- DAFTAR ITEM DARI KERANJANG -->
                        <div class="space-y-3 max-h-60 overflow-y-auto pr-1 mb-4">
                            <?php
                            $subtotal_keseluruhan = 0;
                            $stmt_item = mysqli_prepare($koneksi, "SELECT * FROM tb_produk WHERE id = ?");
                            foreach ($_SESSION['keranjang'] as $key => $cart_item) {
                                $id_p = (int) (is_array($cart_item) ? ($cart_item['id'] ?? $key) : $key);
                                $qty = (int) (is_array($cart_item) ? ($cart_item['jumlah'] ?? 0) : $cart_item);
                                if ($id_p <= 0 || $qty <= 0) {
                                    continue;
                                }

                                mysqli_stmt_bind_param($stmt_item, "i", $id_p);
                                if (!mysqli_stmt_execute($stmt_item)) {
                                    throw new RuntimeException("Gagal memuat ringkasan produk: " . mysqli_stmt_error($stmt_item));
                                }
                                $hasil_item = mysqli_stmt_get_result($stmt_item);
                                if ($item = mysqli_fetch_assoc($hasil_item)) {
                                    $subtotal_item = (int) $item['harga'] * $qty;
                                    $subtotal_keseluruhan += $subtotal_item;
                            ?>
                                <div class="flex items-center justify-between text-xs py-2 border-b border-sand/20">
                                    <div>
                                        <div class="font-semibold text-stone-ink"><?= htmlspecialchars($item['nama_produk'] ?? $item['nama']) ?></div>
                                        <div class="text-stone-ink/50"><?= $qty ?> x Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
                                    </div>
                                    <div class="font-bold text-stone-ink">
                                        Rp <?= number_format($subtotal_item, 0, ',', '.') ?>
                                    </div>
                                </div>
                            <?php 
                                }
                            } 
                            ?>
                        </div>

                        <!-- RINCIAN HARGA -->
                        <div class="space-y-2 pt-2 border-t border-sand/30 text-xs">
                            <div class="flex justify-between text-stone-ink/70">
                                <span>Subtotal Produk</span>
                                <span>Rp <?= number_format($subtotal_keseluruhan, 0, ',', '.') ?></span>
                            </div>
                            <div class="flex justify-between text-stone-ink/70">
                                <span>Biaya Layanan</span>
                                <span class="text-sage font-semibold">GRATIS</span>
                            </div>
                            <div class="flex justify-between items-center text-sm font-bold text-stone-ink pt-3 border-t border-sand/30">
                                <span>Total Tagihan</span>
                                <span class="text-lg text-clay">Rp <?= number_format($subtotal_keseluruhan, 0, ',', '.') ?></span>
                            </div>
                        </div>

                        <!-- TOMBOL SUBMIT CHECKOUT -->
                        <button type="submit" name="proses_checkout"
                                class="w-full mt-6 bg-stone-ink text-white py-4 rounded-2xl font-semibold hover:bg-clay transition-colors flex items-center justify-center gap-2 shadow-soft">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Buat Pesanan Sekarang
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bg-[#1A1412] text-white pt-8 pb-6 mt-auto">
        <div class="max-w-[1400px] mx-auto px-6 text-center text-[10px] text-sand/40">
            <p>&copy; <?= date('Y') ?> Kopi Singgah. All rights reserved.</p>
        </div>
    </footer>

    <!-- SWEETALERT NOTIFIKASI -->
    <script>
        <?php if ($pesan != "") { ?>
            Swal.fire({
                title: '<?= $status == "success" ? "Pesanan Berhasil!" : "Gagal!" ?>',
                text: <?= json_encode($pesan, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
                icon: '<?= $status ?>',
                confirmButtonColor: '#A96B51'
            }).then((result) => {
                <?php if ($status == "success") { ?>
                    if (result.isConfirmed) {
                        window.location.href = 'pembayaran.php?id=<?= (int) $id_transaksi_baru ?>';
                    }
                <?php } ?>
            });
        <?php } ?>

        const alamatWrapper = document.getElementById('alamat-pengiriman-wrapper');
        const alamatField = document.getElementById('alamat-pengiriman');
        document.querySelectorAll('input[name="metode_pengiriman"]').forEach((input) => {
            input.addEventListener('change', () => {
                const perluAlamat = document.querySelector('input[name="metode_pengiriman"]:checked').value === 'antar';
                alamatWrapper.classList.toggle('hidden', !perluAlamat);
                alamatField.required = perluAlamat;
            });
        });
    </script>
</body>
</html>