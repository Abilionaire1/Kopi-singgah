<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';
/** @var mysqli $koneksi */

$id_user =$_SESSION['user_id'] ?? null;

// ==========================================
// 1. HANDLER AJAX: Update Quantity Tanpa Reload
// ==========================================
if (isset($_POST['ajax_update_cart'])) {
    header('Content-Type: application/json');
    $id_produk =$_POST['id'] ?? null;
    $action    =$_POST['action_qty'] ?? null;
    $new_qty   = isset($_POST['jumlah']) ? intval($_POST['jumlah']) : null;

    if ($id_produk && isset($_SESSION['keranjang'][$id_produk])) {
        if ($action === 'plus') {
            $_SESSION['keranjang'][$id_produk]['jumlah']++;
        } elseif ($action === 'minus') {
            if ($_SESSION['keranjang'][$id_produk]['jumlah'] > 1) {
                $_SESSION['keranjang'][$id_produk]['jumlah']--;
            }
        } elseif ($new_qty !== null && $new_qty > 0) {$_SESSION['keranjang'][$id_produk]['jumlah'] =$new_qty;
        }

        $jumlah_sekarang = $_SESSION['keranjang'][$id_produk]['jumlah'];
        $harga           =$_SESSION['keranjang'][$id_produk]['harga'];$subtotal_item   = $jumlah_sekarang * $harga;

        $total = 0;
        foreach ($_SESSION['keranjang'] as $item) {$total += ($item['jumlah'] *$item['harga']);
        }

        echo json_encode([
            'status'   => 'success',
            'jumlah'   => $jumlah_sekarang,
            'subtotal' => 'Rp ' . number_format($subtotal_item, 0, ',', '.'),
            'total'    => 'Rp ' . number_format($total, 0, ',', '.')
        ]);
        exit;
    }

    echo json_encode(['status' => 'error']);
    exit;
}

// ==========================================
// 2. Add to Cart via Form Submit (POST)
// ==========================================
if (isset($_POST["add"]) && isset($_GET["id"])) {
    $id_produk =$_GET["id"];

    if (isset($_SESSION["keranjang"][$id_produk])) {$_SESSION["keranjang"][$id_produk]['jumlah'] += intval($_POST["jumlah"]);
    } else {
        $_SESSION["keranjang"][$id_produk] = array(
            'id'     => $id_produk,
            'nama'   => $_POST["hidden_nama"],
            'harga'  => $_POST["hidden_harga"],
            'foto'   => $_POST["hidden_foto"],
            'jumlah' => intval($_POST["jumlah"])
        );
    }
    header("Location: keranjang.php");
    exit;
}

// ==========================================
// 3. Add to Cart via URL Link / Direct (GET)
// ==========================================
if (isset($_GET['aksi']) && $_GET['aksi'] == 'tambah') {$id_produk = $_GET['id'] ?? $_GET['id_produk'] ?? null;
    $jumlah    = intval($_GET['jumlah'] ?? 1);

    if ($id_produk) {
        $query  = mysqli_query($koneksi, "SELECT * FROM tb_produk WHERE id = '$id_produk'");
        $produk = mysqli_fetch_assoc($query);

        if ($produk) {
            if (!isset($_SESSION['keranjang'])) {$_SESSION['keranjang'] = array();
            }

            if (isset($_SESSION['keranjang'][$id_produk])) {$_SESSION['keranjang'][$id_produk]['jumlah'] +=$jumlah;
            } else {
                $_SESSION['keranjang'][$id_produk] = array(
                    'id'     => $produk['id'],
                    'nama'   => $produk['nama_produk'] ?? $produk['nama'],
                    'harga'  => $produk['harga'],
                    'foto'   => $produk['foto'] ?? $produk['gambar'],
                    'jumlah' => $jumlah
                );
            }
        }
        header("Location: keranjang.php");
        exit;
    } else {
        echo "<script>alert('Gagal! ID Produk tidak ditemukan.'); window.location.href='menu.php';</script>";
        exit;
    }
}

// ==========================================
// 4. Aksi Hapus & Checkout
// ==========================================
if (isset($_GET["aksi"])) {
    if ($_GET["aksi"] == "hapus") {
        $id_produk =$_GET["id"];
        if (isset($_SESSION["keranjang"][$id_produk])) {
            unset($_SESSION["keranjang"][$id_produk]);
        }
        header("Location: keranjang.php");
        exit;
    } elseif ($_GET["aksi"] == "checkout") {
        if (!$id_user) {
            $_SESSION['error'] = "Silakan login terlebih dahulu untuk checkout.";
            header("Location: login.php");
            exit;
        }

        if (empty($_SESSION["keranjang"])) {
            header("Location: keranjang.php");
            exit;
        }

        header("Location: transaksi.php");
        exit;
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keranjang - Kopi Singgah</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tailwind CSS -->
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
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        .swal2-popup {
            border-radius: 1rem !important;
            font-family: 'Inter', sans-serif !important;
        }
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
                <a href="keranjang.php" class="border-b border-stone-ink pb-1 text-stone-ink">Keranjang</a>
            </div>

            <div class="flex items-center gap-5">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="hidden md:inline-block text-stone-ink/60 hover:text-stone-ink text-sm font-medium transition">Profil</a>
                    <a href="logout.php" class="hidden md:inline-block border border-stone-ink text-stone-ink text-xs font-semibold px-5 py-2.5 rounded-full hover:bg-stone-ink hover:text-white transition">Keluar</a>
                <?php else: ?>
                    <a href="login.php" class="hidden md:inline-block bg-stone-ink text-white text-xs font-semibold px-5 py-2.5 rounded-full hover:bg-clay transition">Masuk / Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="flex-grow py-12 md:py-20">
        <div class="max-w-[900px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <?php if (!empty($_SESSION["keranjang"])) { ?>
                <div class="bg-white rounded-[2rem] shadow-soft p-8 md:p-12">
                    
                    <h1 class="font-serif text-4xl text-stone-ink mb-10">Keranjang</h1>

                    <!-- Product List -->
                    <div class="flex flex-col mb-10">
                        <?php
                        $total = 0;
                        foreach ($_SESSION["keranjang"] as $key => $value) {$id_item = $value['id'] ?? $key;
                            $subtotal_item = $value["jumlah"] * $value["harga"];
                            $total +=$subtotal_item;
                        ?>
                            <div class="flex flex-col sm:flex-row items-center py-6 border-b border-sand/30 gap-6 last:border-b-0">
                                
                                <!-- Image & Details -->
                                <div class="flex items-center gap-5 w-full sm:w-2/5">
                                    <div class="w-20 h-20 bg-warm/40 rounded-xl overflow-hidden shrink-0 flex items-center justify-center">
                                        <img src="dashboard/img/<?= htmlspecialchars($value["foto"]) ?>" class="w-full h-full object-cover" alt="<?= htmlspecialchars($value["nama"]) ?>" onerror="this.src='https://via.placeholder.com/80x80?text=No+Image'">
                                    </div>
                                    <div>
                                        <h3 class="font-serif font-semibold text-lg text-stone-ink leading-tight mb-1"><?= htmlspecialchars($value["nama"]) ?></h3>
                                        <p class="text-sm text-stone-ink/60">Rp <?= number_format($value["harga"], 0, ',', '.') ?></p>
                                    </div>
                                </div>

                                <!-- Quantity Control (AJAX) -->
                                <div class="w-full sm:w-1/5 flex justify-start sm:justify-center">
                                    <div class="flex items-center border border-sand/60 rounded-lg bg-white overflow-hidden w-24">
                                        <button type="button" class="btn-qty w-8 h-8 flex items-center justify-center text-stone-ink/60 hover:bg-warm hover:text-stone-ink transition font-bold text-lg" data-id="<?= $id_item ?>" data-action="minus">-</button>
                                        <input type="number" class="input-qty w-8 text-center text-sm font-semibold text-stone-ink border-none outline-none focus:ring-0 p-0" data-id="<?= $id_item ?>" value="<?= $value["jumlah"] ?>" min="1">
                                        <button type="button" class="btn-qty w-8 h-8 flex items-center justify-center text-stone-ink/60 hover:bg-warm hover:text-stone-ink transition font-bold text-lg" data-id="<?= $id_item ?>" data-action="plus">+</button>
                                    </div>
                                </div>

                                <!-- Subtotal & Delete -->
                                <div class="w-full sm:w-2/5 flex items-center justify-between sm:justify-end gap-6">
                                    <span class="subtotal-item font-semibold text-stone-ink text-lg" id="subtotal-<?= $id_item ?>">Rp <?= number_format($subtotal_item, 0, ',', '.') ?></span>
                                    
                                    <a href="keranjang.php?aksi=hapus&id=<?= $id_item; ?>" class="text-stone-ink/30 hover:text-red-500 transition-colors shrink-0" title="Hapus Produk">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Summary Area -->
                    <div class="border-t border-sand/40 pt-8 flex flex-col sm:items-end">
                        <div class="w-full sm:w-1/2 md:w-5/12">
                            <div class="flex justify-between items-center mb-3 text-sm text-stone-ink/80">
                                <span>Subtotal</span>
                                <span class="summary-subtotal font-medium text-stone-ink">Rp <?= number_format($total, 0, ',', '.') ?></span>
                            </div>
                            
                            <div class="flex justify-between items-center mb-6 text-sm text-stone-ink/80 pb-6 border-b border-sand/30">
                                <span>Pengiriman</span>
                                <span class="text-clay font-medium">Gratis</span>
                            </div>
                            
                            <div class="flex justify-between items-center mb-8">
                                <span class="font-serif text-lg font-bold text-stone-ink">Total</span>
                                <span class="grand-total font-bold text-xl text-clay">Rp <?= number_format($total, 0, ',', '.') ?></span>
                            </div>
                            
                            <div class="flex justify-end">
                                <a href="keranjang.php?aksi=checkout" id="btnCheckout" class="w-full sm:w-auto text-center bg-stone-ink text-white px-8 py-3.5 rounded-xl font-semibold hover:bg-clay transition-colors duration-300">
                                    Check out
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

            <?php } else { ?>
                <!-- Empty Cart State -->
                <div class="bg-white rounded-[2rem] p-12 text-center shadow-soft max-w-2xl mx-auto flex flex-col items-center justify-center">
                    <div class="w-24 h-24 bg-warm rounded-full flex items-center justify-center mb-6 text-clay">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M7.354 5.646a.5.5 0 1 0-.708.708L7.793 7.5 6.646 8.646a.5.5 0 1 0 .708.708L8.5 8.207l1.146 1.147a.5.5 0 0 0 .708-.708L9.207 7.5l1.147-1.146a.5.5 0 0 0-.708-.708L8.5 6.793 7.354 5.646z"/>
                            <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zm3.915 10L3.102 4h10.796l-1.313 7h-8.17zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                        </svg>
                    </div>
                    <h5 class="font-serif text-3xl text-stone-ink mb-3">Keranjang kosong</h5>
                    <p class="text-stone-ink/60 text-sm mb-8 max-w-sm">Sepertinya kamu belum menemukan racikan yang pas. Yuk, kembali dan eksplor menu kami!</p>
                    <a href="menu.php" class="bg-stone-ink text-white font-semibold px-8 py-3.5 rounded-full hover:bg-clay transition">Mulai Belanja</a>
                </div>
            <?php } ?>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bg-[#1A1412] text-white pt-16 pb-8 border-t border-stone-ink mt-auto">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-12 grid grid-cols-1 md:grid-cols-12 gap-10 mb-12">
            <div class="md:col-span-5">
                <a href="index.php" class="font-serif text-2xl font-semibold flex items-center gap-2 mb-4 text-sand">
                    <svg class="w-6 h-6 text-clay" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                    Kopi Singgah.
                </a>
                <p class="text-sand/50 text-xs leading-relaxed max-w-xs mb-6">
                    Berhenti sejenak, nikmati rasanya. Kami menyeduh kopi terbaik dengan sepenuh hati untuk menemani hari Anda yang lebih baik.
                </p>
            </div>
            <div class="md:col-span-3">
                <h4 class="font-sans text-xs font-bold uppercase tracking-widest text-sand mb-6">EKSPLOR</h4>
                <ul class="space-y-3 text-sand/60 text-sm">
                    <li><a href="index.php" class="hover:text-clay transition">Beranda</a></li>
                    <li><a href="menu.php" class="hover:text-clay transition">Menu Lengkap</a></li>
                    <li><a href="keranjang.php" class="hover:text-clay transition">Keranjang</a></li>
                </ul>
            </div>
            <div class="md:col-span-4">
                <h4 class="font-sans text-xs font-bold uppercase tracking-widest text-sand mb-6">HUBUNGI KAMI</h4>
                <ul class="space-y-4 text-sand/60 text-sm">
                    <li class="flex items-center gap-3"><span class="w-5 text-center">📱</span> Instagram @kopisinggah</li>
                    <li class="flex items-center gap-3"><span class="w-5 text-center">✉️</span> Email: hello@kopisinggah.id</li>
                </ul>
            </div>
        </div>
        <div class="max-w-[1400px] mx-auto px-6 lg:px-12 flex flex-col md:flex-row items-center justify-between pt-6 border-t border-white/5 text-[10px] text-sand/40">
            <p>&copy; <?= date('Y') ?> Kopi Singgah. All rights reserved.</p>
        </div>
    </footer>

    <!-- SCRIPT AJAX & CHECKOUT -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Fungsi AJAX update keranjang
            function updateCart(id, actionQty, newJumlah) {
                const formData = new FormData();
                formData.append('ajax_update_cart', '1');
                formData.append('id', id);
                if (actionQty) formData.append('action_qty', actionQty);
                if (newJumlah) formData.append('jumlah', newJumlah);

                fetch('keranjang.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        const inputEl = document.querySelector(`.input-qty[data-id="${id}"]`);
                        if (inputEl) inputEl.value = data.jumlah;

                        const subtotalEl = document.getElementById(`subtotal-${id}`);
                        if (subtotalEl) subtotalEl.textContent = data.subtotal;

                        document.querySelectorAll('.summary-subtotal, .grand-total').forEach(el => {
                            el.textContent = data.total;
                        });
                    }
                })
                .catch(err => console.error('Error updating cart:', err));
            }

            // Event listener tombol + / -
            document.querySelectorAll('.btn-qty').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const action = this.dataset.action;
                    updateCart(id, action, null);
                });
            });

            // Event listener ubah angka di input secara langsung
            document.querySelectorAll('.input-qty').forEach(input => {
                input.addEventListener('change', function() {
                    const id = this.dataset.id;
                    const jumlah = this.value;
                    updateCart(id, null, jumlah);
                });
            });

            // SweetAlert Checkout (Pencegahan jika belum login)
            const btnCheckout = document.getElementById('btnCheckout');
            if (btnCheckout) {
                btnCheckout.addEventListener('click', function(e) {
                    const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
                    
                    if (!isLoggedIn) {
                        e.preventDefault();
                        
                        Swal.fire({
                            title: 'Belum Login!',
                            text: 'Silakan login terlebih dahulu untuk menyelesaikan pesanan Anda.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Login Sekarang',
                            cancelButtonText: 'Lain Kali',
                            reverseButtons: true,
                            customClass: {
                                confirmButton: 'bg-stone-ink text-white px-6 py-2.5 rounded-full font-semibold mx-2 hover:bg-clay',
                                cancelButton: 'bg-transparent border border-stone-ink text-stone-ink px-6 py-2.5 rounded-full font-semibold mx-2 hover:bg-warm'
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'login.php';
                            } else if (result.dismiss === Swal.DismissReason.cancel) {
                                window.location.href = 'menu.php';
                            }
                        });
                    }
                });
            }
        });
    </script>
</body>
</html>