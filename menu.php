    <?php
session_start();
include 'koneksi.php';
/** @var mysqli $koneksi */

// Ambil semua kategori yang ada di database
$kategori_query = mysqli_query($koneksi, "SELECT * FROM tb_kategori ORDER BY id_kategori ASC");
$kategori_list = [];
if ($kategori_query && mysqli_num_rows($kategori_query) > 0) {
    while ($kat = mysqli_fetch_assoc($kategori_query)) {
        $kategori_list[] = $kat;
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menu Kami - Kopi Singgah</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">

    <!-- Google Fonts: Fraunces & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@300;400;500;600;700&family=Caveat:wght@600&display=swap" rel="stylesheet">

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
                        'soft': '0 10px 40px rgba(0, 0, 0, 0.05)',
                    }
                }
            }
        }
    </script>
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        html { scroll-behavior: smooth; }
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

            <!-- Center Links -->
            <div class="hidden lg:flex items-center gap-10 text-sm font-medium">
                <a href="index.php" class="text-stone-ink/60 hover:text-stone-ink transition">Beranda</a>
                <a href="menu.php" class="border-b border-stone-ink pb-1 font-semibold text-stone-ink">Menu</a>
                <a href="keranjang.php" class="text-stone-ink/60 hover:text-stone-ink transition">Keranjang</a>
            </div>

            <!-- Right Icons -->
            <div class="flex items-center gap-5">
                <a href="keranjang.php" class="text-stone-ink hover:text-clay transition relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span class="absolute -top-1.5 -right-2 bg-clay text-white text-[10px] font-bold px-1.5 rounded-full <?= (isset($_SESSION['keranjang']) && array_sum(array_column($_SESSION['keranjang'], 'jumlah'))) > 0 ? '' : 'hidden' ?>">
                        <?= isset($_SESSION['keranjang']) ? array_sum(array_column($_SESSION['keranjang'], 'jumlah')) : 0 ?>
                    </span>
                </a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="hidden md:inline-block text-stone-ink/60 hover:text-stone-ink text-sm font-medium transition">Profil</a>
                    <a href="logout.php" class="hidden md:inline-block border border-stone-ink text-stone-ink text-xs font-semibold px-5 py-2.5 rounded-full hover:bg-stone-ink hover:text-white transition">Keluar</a>
                <?php else: ?>
                    <a href="login.php" class="hidden md:inline-block bg-stone-ink text-white text-xs font-semibold px-5 py-2.5 rounded-full hover:bg-clay transition">Masuk / Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- HEADER MENU -->
    <header class="pt-16 pb-12 bg-warm/30">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-12 text-center">
            <span class="text-clay text-xs font-bold tracking-widest uppercase mb-4 block">Eksplorasi Rasa</span>
            <h1 class="font-serif text-4xl md:text-5xl lg:text-6xl text-stone-ink mb-6">Daftar Menu</h1>
            <p class="text-stone-ink/70 text-sm md:text-base max-w-xl mx-auto leading-relaxed">
                Dari biji kopi pilihan hingga hidangan penutup manis, temukan racikan sempurna untuk menemani waktu singgah Anda.
            </p>
        </div>
    </header>

    <!-- STICKY CATEGORY NAV -->
    <div class="sticky top-[72px] z-40 bg-cream/95 backdrop-blur-md border-b border-sand/40 py-4 shadow-sm">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-12">
            <div class="flex overflow-x-auto no-scrollbar gap-4 items-center">
                <?php foreach($kategori_list as $kat): ?>
                    <a href="#kat-<?= $kat['id_kategori'] ?>" class="whitespace-nowrap px-5 py-2 rounded-full border border-sand/60 text-xs font-bold uppercase tracking-wider text-stone-ink/70 hover:bg-stone-ink hover:text-white hover:border-stone-ink transition duration-300">
                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- MAIN MENU CONTENT -->
    <main class="flex-grow py-12">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-12">
            
            <?php 
            // Looping berdasarkan kategori
            foreach($kategori_list as $kat): 
                $id_kat = $kat['id_kategori'];
                $nama_kat = $kat['nama_kategori'];
                
                // Ambil produk berdasarkan ID kategori saat ini
                $produk_query = mysqli_query($koneksi, "SELECT * FROM tb_produk WHERE id_kategori = '$id_kat' ORDER BY nama_produk ASC");
                
                // Cek apakah kategori ini punya produk, kalau kosong skip aja
                if($produk_query && mysqli_num_rows($produk_query) > 0):
            ?>
                <!-- Kategori Section -->
                <section id="kat-<?= $id_kat ?>" class="mb-20 scroll-mt-32">
                    <div class="flex items-end justify-between mb-8 pb-4 border-b border-sand/30">
                        <h2 class="font-serif text-3xl md:text-4xl text-stone-ink"><?= htmlspecialchars($nama_kat) ?></h2>
                        <span class="text-xs font-bold text-clay tracking-widest uppercase hidden sm:block"><?= mysqli_num_rows($produk_query) ?> Menu</span>
                    </div>
                    
                    <!-- Grid Produk -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php while($data = mysqli_fetch_array($produk_query)): ?>
                            
                            <!-- Product Card Dinamis -->
                            <div class="bg-white rounded-2xl p-3 shadow-editorial group hover:-translate-y-1 transition duration-300 flex flex-col h-full border border-transparent hover:border-sand/40">
                                <div class="relative h-48 md:h-56 rounded-xl overflow-hidden mb-4 bg-sand/20 shrink-0">
                                    <div class="absolute top-2 left-2 bg-white/90 text-[9px] font-bold px-2 py-1 rounded-md z-10 uppercase">
                                        <?= htmlspecialchars($nama_kat) ?>
                                    </div>
                                    
                                    <!-- Gambar Produk dari DB -->
                                    <img src="dashboard/img/<?= htmlspecialchars($data['poto']) ?>" alt="<?= htmlspecialchars($data['nama_produk']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" onerror="this.src='https://via.placeholder.com/400x300?text=No+Image'">
                                </div>
                                
                                <div class="px-2 pb-2 flex flex-col flex-grow">
                                    <h3 class="font-serif font-semibold text-lg text-stone-ink truncate mb-1" title="<?= htmlspecialchars($data['nama_produk']) ?>">
                                        <?= htmlspecialchars($data['nama_produk']) ?>
                                    </h3>
                                    <p class="text-[11px] text-stone-ink/60 line-clamp-2 mb-4 h-8">
                                        <?= htmlspecialchars($data['deskripsi']) ?>
                                    </p>
                                    
                                    <!-- Harga dan Tombol Detail -->
                                    <div class="flex items-center justify-between border-t border-sand/30 pt-3 mt-auto">
                                        <span class="font-semibold text-clay text-[15px]">
                                            Rp <?= number_format($data['harga'], 0, ',', '.') ?>
                                        </span>
                                        
                                        <a href="detail.php?id=<?= $data['id'] ?>" class="text-[10px] font-bold border border-stone-ink text-stone-ink px-4 py-1.5 rounded-full hover:bg-stone-ink hover:text-white transition">
                                            Lihat Detail
                                        </a>
                                    </div>
                                </div>
                            </div>

                        <?php endwhile; ?>
                    </div>
                </section>
            
            <?php 
                endif; // End check empty category
            endforeach; // End loop category
            ?>

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

</body>
</html>