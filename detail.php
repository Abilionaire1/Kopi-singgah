<?php 
session_start();
include 'koneksi.php';
/** @var mysqli $koneksi */

// --- PROSES AJAX TAMBAH KERANJANG ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah_keranjang') {
    header('Content-Type: application/json');

    // Validasi apakah user sudah login
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'not_logged_in']);
        exit;
    }

    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $nama = isset($_POST['nama_produk']) ? $_POST['nama_produk'] : '';
    $harga = isset($_POST['harga']) ? (float)$_POST['harga'] : 0;
    $foto = isset($_POST['foto']) ? $_POST['foto'] : '';
    $jumlah = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 1;

    if (empty($id)) {
        echo json_encode(['status' => 'error']);
        exit;
    }

    if (!isset($_SESSION['keranjang'])) {
        $_SESSION['keranjang'] = [];
    }

    if (isset($_SESSION['keranjang'][$id])) {
        $_SESSION['keranjang'][$id]['jumlah'] += $jumlah;
    } else {
        $_SESSION['keranjang'][$id] = [
            'nama' => $nama,
            'harga' => $harga,
            'foto' => $foto,
            'jumlah' => $jumlah
        ];
    }

    $total_item = 0;
    foreach ($_SESSION['keranjang'] as $item) {
        $total_item += (int)$item['jumlah'];
    }

    echo json_encode(['status' => 'success', 'total_item' => $total_item]);
    exit;
}

// --- AMBIL DATA PRODUK ---
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$hasil = mysqli_query($koneksi, "SELECT tb_produk.*, tb_kategori.nama_kategori FROM tb_produk JOIN tb_kategori ON tb_produk.id_kategori = tb_kategori.id_kategori WHERE tb_produk.id = '$id'");
$data = mysqli_fetch_array($hasil);

if (!$data) {
    header('Location: index.php');
    exit;
}

$nama_produk = $data['nama_produk'];
$harga = $data['harga'];
$deskripsi = $data['deskripsi'];
$poto = $data['poto'];
$kategori = $data['nama_kategori'];


?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kopi Singgah</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    
    <!-- Google Fonts: Fraunces & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@300;400;500;600;700&family=Caveat:wght@600&display=swap" rel="stylesheet">
    
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
                        handwriting: ['Caveat', 'cursive'],
                    },
                    boxShadow: {
                        'editorial': '0 4px 20px rgba(0, 0, 0, 0.03)',
                    }
                }
            }
        }
    </script>
    <style>
        /* Mengurangi radius border SweetAlert agar sesuai dengan style Kopi Singgah */
        .swal2-popup {
            border-radius: 1rem !important;
            font-family: 'Inter', sans-serif !important;
        }
        .swal2-confirm {
            background-color: #1E1B18 !important;
            border-radius: 9999px !important;
        }
    </style>
</head>

<body class="bg-cream text-stone-ink font-sans antialiased overflow-x-hidden min-h-screen flex flex-col">

    <!-- NAVBAR -->
    <nav class="w-full bg-cream/90 backdrop-blur-md sticky top-0 z-50 border-b border-sand/30">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-12 py-4 flex items-center justify-between">
            <!-- Logo -->
            <a href="index.php" class="font-serif text-xl md:text-2xl font-semibold flex items-center gap-2">
                <svg class="w-6 h-6 text-clay" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                Kopi Singgah.
            </a>

            <!-- Center Links -->
            <div class="hidden lg:flex items-center gap-10 text-sm font-medium">
                <a href="index.php" class="text-stone-ink/60 hover:text-stone-ink transition">Beranda</a>
                <a href="menu.php" class="text-stone-ink/60 hover:text-stone-ink transition">Menu</a>
                <a href="index.php#about" class="text-stone-ink/60 hover:text-stone-ink transition">Tentang Kami</a>
                <a href="index.php#produk" class="border-b border-stone-ink pb-1">Produk</a>
                <a href="keranjang.php" class="text-stone-ink/60 hover:text-stone-ink transition">Keranjang</a>
            </div>

            <!-- Right Icons -->
            <div class="flex items-center gap-5">
                <button class="text-stone-ink hover:text-clay transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>
                <a href="keranjang.php" class="text-stone-ink hover:text-clay transition relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <!-- Badge jumlah item keranjang (real-time) -->
                    <span id="cartBadge" class="absolute -top-1.5 -right-2 bg-clay text-white text-[10px] font-bold px-1.5 rounded-full <?= (isset($_SESSION['keranjang']) && array_sum(array_column($_SESSION['keranjang'], 'jumlah'))) > 0 ? '' : 'hidden' ?>"><?= isset($_SESSION['keranjang']) ? array_sum(array_column($_SESSION['keranjang'], 'jumlah')) : 0 ?></span>
                </a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="hidden md:inline-block border border-stone-ink text-stone-ink text-xs font-semibold px-5 py-2.5 rounded-full hover:bg-stone-ink hover:text-white transition">Keluar</a>
                <?php else: ?>
                    <a href="login.php" class="hidden md:inline-block bg-stone-ink text-white text-xs font-semibold px-5 py-2.5 rounded-full hover:bg-clay transition">Masuk / Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="flex-grow flex items-center py-10 md:py-20">
        <div class="max-w-[1200px] mx-auto px-6 lg:px-12 w-full">
            
            <!-- Breadcrumbs -->
            <nav class="flex text-sm text-stone-ink/60 mb-8" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="index.php" class="hover:text-clay transition inline-flex items-center">Beranda</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                            </svg>
                            <a href="index.php#produk" class="ml-1 md:ml-2 hover:text-clay transition">Produk</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                            </svg>
                            <span class="ml-1 md:ml-2 font-semibold text-stone-ink"><?= htmlspecialchars($nama_produk) ?></span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Product Wrapper -->
            <div class="bg-white rounded-[2rem] p-6 md:p-10 shadow-editorial flex flex-col lg:flex-row gap-10 md:gap-16 items-center">
                
                <!-- Image Side -->
                <div class="w-full lg:w-1/2 relative">
                    <div class="bg-warm/30 rounded-3xl p-4 md:p-8 aspect-square flex items-center justify-center relative overflow-hidden">
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-3/4 h-3/4 bg-sand/20 rounded-full blur-3xl -z-10"></div>
                        
                        <!-- FIX: Path Gambar mengambil dari folder root -->
                        <img src="dashboard/img/<?= htmlspecialchars($poto) ?>" alt="<?= htmlspecialchars($nama_produk) ?>" class="w-full h-full object-cover rounded-2xl shadow-sm z-10 transition duration-500 hover:scale-105">
                        
                        <!-- Label Kategori -->
                        <span class="absolute top-6 left-6 bg-white/90 text-stone-ink text-[10px] font-bold px-3 py-1.5 rounded-md z-20 uppercase tracking-wider shadow-sm">
                            <?= isset($kategori) ? htmlspecialchars($kategori) : 'Kopi Singgah' ?>
                        </span>
                    </div>
                </div>
                
                <!-- Detail Side -->
                <div class="w-full lg:w-1/2 flex flex-col justify-center">
                    <span class="text-clay text-[10px] font-bold tracking-widest uppercase mb-3 block">Detail Produk</span>
                    <h1 class="font-serif text-3xl md:text-4xl lg:text-5xl text-stone-ink leading-tight mb-4">
                        <?= htmlspecialchars($nama_produk) ?>
                    </h1>
                    
                    <div class="text-2xl md:text-3xl font-semibold text-stone-ink mb-4">
                        Rp <?= number_format($harga, 0, ',', '.') ?>
                    </div>
                    
                    <!-- INDIKATOR STOK -->
                    <div class="mb-6">
                        <?php if (isset($data['stok']) && $data['stok'] > 0): ?>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-green-600"></span>
                                <span class="text-sm font-medium text-green-700">Stok tersedia</span>
                                <span class="text-sm text-stone-ink/50"><?= $data['stok'] ?> tersedia</span>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                <span class="text-sm font-medium text-red-600">Stok habis</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <p class="text-stone-ink/70 text-sm md:text-base leading-relaxed mb-8 max-w-lg">
                        <?= nl2br(htmlspecialchars($deskripsi)) ?>
                    </p>
                    
                    <hr class="border-sand/40 mb-8 w-full max-w-lg">
                    
                    <!-- Form Cart -->
                    <form id="formTambahKeranjang" class="w-full max-w-lg">
                        <input type="hidden" name="action" value="tambah_keranjang">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <input type="hidden" name="nama_produk" value="<?= htmlspecialchars($nama_produk) ?>">
                        <input type="hidden" name="harga" value="<?= $harga ?>">
                        <input type="hidden" name="foto" value="<?= htmlspecialchars($poto) ?>">
                        
                        <div class="flex items-center gap-6 mb-8">
                            <label class="font-semibold text-sm text-stone-ink w-16">Jumlah</label>
                            <div class="flex items-center border border-sand/60 rounded-full bg-white overflow-hidden">
                                <button type="button" class="px-4 py-2 text-stone-ink hover:bg-warm transition font-bold" onclick="decreaseQty()">-</button>
                                <input type="number" id="jumlahInput" name="jumlah" value="1" min="1" class="w-12 text-center text-sm font-semibold border-none outline-none focus:ring-0 p-0" readonly>
                                <button type="button" class="px-4 py-2 text-stone-ink hover:bg-warm transition font-bold" onclick="increaseQty()">+</button>
                            </div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button type="submit" <?= (isset($data['stok']) && $data['stok'] <= 0) ? 'disabled class="flex-grow bg-stone-ink/50 text-white font-semibold py-3.5 rounded-full cursor-not-allowed flex items-center justify-center gap-2"' : 'class="flex-grow bg-stone-ink text-white font-semibold py-3.5 rounded-full hover:bg-clay transition flex items-center justify-center gap-2"' ?>>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                <?= (isset($data['stok']) && $data['stok'] <= 0) ? 'Stok Habis' : 'Tambah ke Keranjang' ?>
                            </button>
                            <a href="menu.php" class="flex-grow sm:flex-grow-0 sm:w-1/3 border border-stone-ink text-stone-ink font-semibold py-3.5 rounded-full hover:bg-warm transition flex items-center justify-center">
                                Kembali
                            </a>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bg-[#1A1412] text-white pt-16 pb-8 border-t border-stone-ink mt-auto">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-12 grid grid-cols-1 md:grid-cols-12 gap-10 mb-12">
            <!-- Brand -->
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
            <!-- Eksplor -->
            <div class="md:col-span-3">
                <h4 class="font-sans text-xs font-bold uppercase tracking-widest text-sand mb-6">EKSPLOR</h4>
                <ul class="space-y-3 text-sand/60 text-sm">
                    <li><a href="index.php" class="hover:text-clay transition">Beranda</a></li>
                    <li><a href="menu.php" class="hover:text-clay transition">Menu Lengkap</a></li>
                    <li><a href="keranjang.php" class="hover:text-clay transition">Keranjang</a></li>
                </ul>
            </div>
            <!-- Hubungi Kami -->
            <div class="md:col-span-4">
                <h4 class="font-sans text-xs font-bold uppercase tracking-widest text-sand mb-6">HUBUNGI KAMI</h4>
                <ul class="space-y-4 text-sand/60 text-sm">
                    <li class="flex items-center gap-3"><span class="w-5 text-center">📱</span> Instagram @kopisinggah</li>
                    <li class="flex items-center gap-3"><span class="w-5 text-center">✉️</span> Email: hello@kopisinggah.id</li>
                </ul>
            </div>
        </div>
        <!-- Copyright -->
        <div class="max-w-[1400px] mx-auto px-6 lg:px-12 flex flex-col md:flex-row items-center justify-between pt-6 border-t border-white/5 text-[10px] text-sand/40">
            <p>&copy; <?= date('Y') ?> Kopi Singgah. All rights reserved.</p>
        </div>
    </footer>

    <!-- Script Custom Qty & AJAX Submit -->
    <script>
        const inputQty = document.getElementById('jumlahInput');
        
        function increaseQty() {
            <?php if(isset($data['stok'])): ?>
            if (parseInt(inputQty.value) < <?= $data['stok'] ?>) {
                inputQty.value = parseInt(inputQty.value) + 1;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Batas Stok',
                    text: 'Jumlah melebihi stok yang tersedia.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
            <?php else: ?>
            inputQty.value = parseInt(inputQty.value) + 1;
            <?php endif; ?>
        }
        
        function decreaseQty() {
            if (parseInt(inputQty.value) > 1) {
                inputQty.value = parseInt(inputQty.value) - 1;
            }
        }

        document.getElementById('formTambahKeranjang').addEventListener('submit', function(e) {
            e.preventDefault();

            // --- CEK LOGIN VIA JAVASCRIPT & PHP ---
            <?php if (!isset($_SESSION['user_id'])): ?>
                Swal.fire({
                    icon: 'warning',
                    title: 'Harap Login!',
                    text: 'Anda harus login terlebih dahulu untuk memasukkan produk ke keranjang.',
                    showCancelButton: true,
                    confirmButtonText: 'Ke Halaman Login',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#1E1B18' // Warna disesuaikan dengan tema Kopi Singgah
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'login.php';
                    }
                });
                return; // Hentikan proses jika belum login
            <?php endif; ?>
            
            // --- JIKA SUDAH LOGIN, LANJUTKAN PROSES ---
            fetch('', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update indikator jumlah di icon keranjang tanpa reload
                    const badge = document.getElementById('cartBadge');
                    if (badge) {
                        badge.textContent = data.total_item;
                        badge.classList.remove('hidden');
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Produk telah ditambahkan ke keranjang.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else if (data.status === 'not_logged_in') { // Fallback jika script frontend ter-bypass
                    Swal.fire({
                        icon: 'warning',
                        title: 'Harap Login!',
                        text: 'Silakan login terlebih dahulu.',
                        confirmButtonText: 'Ke Halaman Login',
                        confirmButtonColor: '#1E1B18'
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Gagal menambahkan produk.',
                    });
                }
            });
        });
    </script>
</body>
</html>