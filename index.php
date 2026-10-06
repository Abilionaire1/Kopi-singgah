<?php
session_start();
include 'koneksi.php';
/** @var mysqli $koneksi */

// Ambil 4 produk terbaru untuk Best Seller (hero section)
$hero_products = [];
$query_hero = mysqli_query($koneksi, "SELECT tb_produk.*, tb_kategori.nama_kategori FROM tb_produk JOIN tb_kategori ON tb_produk.id_kategori = tb_kategori.id_kategori ORDER BY id DESC LIMIT 4");
if ($query_hero && mysqli_num_rows($query_hero) > 0) {
    while ($row = mysqli_fetch_array($query_hero)) {
        $hero_products[] = $row;
    }
}

if (isset($_POST['submit_beli'])) {
    $id_pem   = $_POST['id_pem'];
    $id_brg   = $_POST['id_brg'];
    $jml_beli = $_POST['jml_beli'];

   
    $query = "INSERT INTO pembelian (id_pem, id_brg, jml_beli) VALUES ('$id_pem', '$id_brg', '$jml_beli')";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Pembelian Berhasil!'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kopi Singgah - Seduhan Sempurna</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">

    <!-- Google Fonts: Fraunces & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@300;400;500;600;700&family=Caveat:wght@600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .page-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Bikin minimal setinggi layar monitor */
        }
        .main-content {
            flex: 1; /* Ini kunci biar konten utama ngedorong footer ke paling bawah */
            
            /* Background pattern sementara buat ngetes */
            background-color: #f8f5f0; 
            background-image: radial-gradient(#e5d9c5 1px, transparent 1px);
            background-size: 20px 20px;
            padding: 40px;
            text-align: center;
        }

        /* --- STYLING FOOTER --- */
        .footer {
            background-color: #1a1814;
            color: #8b857d;
            padding: 60px 0 20px;
        }
        .footer-container {
            display: flex;
            justify-content: space-between;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 20px;
            gap: 40px;
            flex-wrap: wrap; /* Biar responsif kalau dilayar kecil */
        }
        .footer-col {
            flex: 1;
            min-width: 200px;
        }
        
        /* Typography Footer */
        .footer-col h3 {
            color: #dfcdb6;
            margin: 0 0 15px 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .footer-col h3 i {
            color: #a49179;
            margin-right: 8px;
        }
        .footer-col h4 {
            color: #ffffff;
            font-size: 0.85rem;
            letter-spacing: 1.5px;
            margin: 0 0 20px 0;
            text-transform: uppercase;
        }
        .footer-col p {
            font-size: 0.9rem;
            line-height: 1.6;
            margin: 0 0 25px 0;
        }
        
        /* List Menu */
        .footer-col ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-col ul li {
            margin-bottom: 15px;
        }
        .footer-col ul li a {
            color: #8b857d;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }
        .footer-col ul li a:hover {
            color: #dfcdb6;
        }

        /* --- ICON BULAT KIRI (Social Links) --- */
        .social-links {
            display: flex;
            gap: 12px;
        }
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            background-color: #2a2722;
            color: #dfcdb6;
            border-radius: 50%;
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .social-links a:hover {
            background-color: #dfcdb6;
            color: #1a1814;
        }

        /* --- ICON LIST KANAN (Kontak) --- */
        .contact-links li a {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .contact-links li a i {
            font-size: 1.2rem;
            width: 20px;
            text-align: center;
            color: #6b635e;
            transition: color 0.2s ease;
        }
        .contact-links li a:hover i {
            color: #dfcdb6;
        }

        /* --- BOTTOM BAR --- */
        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1100px;
            margin: 50px auto 0;
            padding: 20px 20px 0;
            border-top: 1px solid #2a2722;
            font-size: 0.8rem;
            flex-wrap: wrap;
            gap: 10px;
        }
        .footer-bottom p {
            margin: 0;
        }
        .legal-links a {
            color: #8b857d;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .legal-links a:hover {
            color: #dfcdb6;
        }
        .legal-links span {
            margin: 0 10px;
            color: #2a2722;
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        /* Animasi Marquee */
        .animate-marquee {
            display: flex;
            width: max-content;
            animation: scrollText 20s linear infinite;
        }
        @keyframes scrollText {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
    </style>
</head>

<body class="bg-cream text-stone-ink font-sans antialiased overflow-x-hidden">

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
                <a href="#" class="border-b border-stone-ink pb-1">Beranda</a>
                <a href="menu.php" class="text-stone-ink/60 hover:text-stone-ink transition">Menu</a>
                <a href="#about" class="text-stone-ink/60 hover:text-stone-ink transition">Tentang Kami</a>
                <a href="#produk" class="text-stone-ink/60 hover:text-stone-ink transition">Produk</a>
                <a href="keranjang.php" class="text-stone-ink/60 hover:text-stone-ink transition">Keranjang</a>
            </div>

            <!-- Right Icons -->
            <div class="flex items-center gap-5">
                <button class="text-stone-ink hover:text-clay transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></button>
                <a href="keranjang.php" class="text-stone-ink hover:text-clay transition relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <!-- Count Badge Example -->
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
<main>
    <!-- HERO SECTION -->
    <section class="relative w-full pt-16 pb-20 overflow-hidden bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]">
        <!-- Background element -->
        <div class="absolute inset-0 pointer-events-none opacity-5">
            <h1 class="text-[12vw] font-black leading-none text-center pt-10">KOPI SINGGAH</h1>
        </div>

        <div class="max-w-[1400px] mx-auto px-6 lg:px-12 flex flex-col lg:flex-row items-center relative z-10">
            <!-- Kiri -->
            <div class="w-full lg:w-5/12 pr-0 lg:pr-10 mb-10 lg:mb-0">
                <span class="text-clay text-xs font-bold tracking-widest uppercase mb-4 block">Kopi Singgah</span>
                <h1 class="font-serif text-5xl lg:text-[4rem] leading-[1.1] text-stone-ink mb-6">
                    Setiap Singgah<br>Terdapat Rasa
                </h1>
                <p class="text-stone-ink/70 text-sm md:text-base leading-relaxed mb-8 max-w-md">
                    Jelajahi koleksi kopi kami dan temukan rasa favorit Anda. Diseduh secara ahli menggunakan biji kopi pilihan terbaik khusus untuk Anda.
                </p>
                <a href="#produk" class="inline-flex items-center gap-3 bg-stone-ink text-white text-sm px-8 py-3.5 rounded-full font-semibold hover:bg-clay transition">
                    Pesan Sekarang &rarr;
                </a>

                <!-- Features -->
                <div class="flex flex-wrap gap-6 mt-12 pt-8 border-t border-sand/40">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full border border-stone-ink/20 flex items-center justify-center">🌱</div>
                        <span class="text-xs font-semibold">Biji Kopi<br>Pilihan</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full border border-stone-ink/20 flex items-center justify-center">📜</div>
                        <span class="text-xs font-semibold">Resep Autentik<br>Setiap Seduhan</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full border border-stone-ink/20 flex items-center justify-center">🛵</div>
                        <span class="text-xs font-semibold">Pengiriman<br>Cepat & Aman</span>
                    </div>
                </div>
            </div>

            <!-- Tengah -->
            <div class="w-full lg:w-1/3 flex justify-center relative order-1 lg:order-2">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[280px] h-[280px] lg:w-[420px] lg:h-[420px] bg-sand/30 rounded-full -z-10 blur-sm"></div>

                <img id="mainHeroImage" src="dashboard/img/display/salted-caramel-hero.png"
                    alt="Produk Utama"
                    class="h-[340px] lg:h-[500px] object-contain hero-center-img transition-opacity duration-300"> 
            </div>

            <!-- Kanan -->
            <div class="w-full lg:w-1/3 flex lg:flex-col justify-center lg:justify-end items-center lg:items-end gap-5 order-3 overflow-x-auto no-scrollbar p-4 lg:pr-6">
                <div class="hero-thumbnail cursor-pointer w-16 h-16 lg:w-24 lg:h-24 rounded-full border-2 border-clay scale-110 shadow-editorial bg-cream overflow-hidden transition-all duration-300 hover:border-clay hover:opacity-100 flex-shrink-0"
                    data-img="dashboard/img/display/salted-caramel-hero.png">
                    <img src="dashboard/img/display/salted-caramel-hero.png" class="w-full h-full object-cover" alt="Salted Caramel">
                </div>
                <div class="hero-thumbnail cursor-pointer w-16 h-16 lg:w-24 lg:h-24 rounded-full border-2 border-stone-ink/10 opacity-70 bg-cream overflow-hidden transition-all duration-300 hover:border-clay hover:opacity-100 flex-shrink-0"
                    data-img="dashboard/img/display/berry-americano-hero.png">
                    <img src="dashboard/img/display/berry-americano-hero.png" class="w-full h-full object-cover" alt="Berry Americano">
                </div>
                <div class="hero-thumbnail cursor-pointer w-16 h-16 lg:w-24 lg:h-24 rounded-full border-2 border-stone-ink/10 opacity-70 bg-cream overflow-hidden transition-all duration-300 hover:border-clay hover:opacity-100 flex-shrink-0"
                    data-img="dashboard/img/display/creamy-matcha-hero.png">
                    <img src="dashboard/img/display/creamy-matcha-hero.png" class="w-full h-full object-cover" alt="Creamy Matcha">
                </div>
            </div>
        </div> 

    <!-- MARQUEE -->
    <div class="w-full bg-[#1A1412] text-sand py-4 overflow-hidden flex border-y border-stone-ink/20 mt-16 lg:mt-24">
        <div class="animate-marquee">
            <?php for ($i = 0; $i < 2; $i++): ?>
                <div class="flex items-center gap-10 px-5 font-sans font-semibold tracking-widest text-sm uppercase">
                    <span>MACCHIATO</span> <span class="text-clay text-[10px]">☕</span>
                    <span>MOCHA</span> <span class="text-clay text-[10px]">☕</span>
                    <span>ESPRESSO</span> <span class="text-clay text-[10px]">☕</span>
                    <span>LATTE</span> <span class="text-clay text-[10px]">☕</span>
                    <span>AMERICANO</span> <span class="text-clay text-[10px]">☕</span>
                    <span>CAPPUCCINO</span> <span class="text-clay text-[10px]">☕</span>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- TENTANG KAMI -->
    <section id="about" class="max-w-[1400px] mx-auto px-6 lg:px-12 py-24 flex flex-col md:flex-row gap-16 items-center">
        <!-- Kiri: Gambar -->
        <div class="w-full md:w-1/2 relative">
            <div class="rounded-3xl overflow-hidden aspect-[4/3] w-full max-w-[500px]">
                <img src="https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&q=80" alt="Cafe Interior" class="w-full h-full object-cover">
            </div>
            <!-- Hand drawn text -->
            <div class="absolute top-10 -left-6 transform -rotate-12 font-handwriting text-3xl text-white drop-shadow-md">
                Tempat<br>Singgah<br>Terbaik
            </div>
            <!-- Badge -->
            <div class="absolute bottom-10 right-4 md:right-10 bg-[#BC7854] text-white p-5 rounded-2xl shadow-xl flex flex-col items-center justify-center">
                <span class="text-sm font-medium">Sejak</span>
                <span class="font-serif text-2xl font-bold">2025</span>
            </div>
        </div>

        <!-- Kanan: Text -->
        <div class="w-full md:w-1/2 relative">
            <span class="text-clay text-[10px] font-bold tracking-widest uppercase mb-4 block">Tentang Kami</span>
            <h2 class="font-serif text-4xl lg:text-5xl text-stone-ink mb-6 leading-[1.1]">
                Menyajikan<br>Kehangatan di Setiap<br>Cangkir.
            </h2>
            <p class="text-stone-ink/70 text-sm leading-relaxed mb-4 max-w-md">
                Kopi Singgah lahir dari kecintaan kami terhadap biji kopi nusantara. Kami percaya bahwa secangkir kopi bukan sekadar minuman, melainkan sebuah ruang untuk singgah, bercerita, dan merayakan momen kecil dalam hidup.
            </p>
            <p class="text-stone-ink/70 text-sm leading-relaxed mb-8 max-w-md">
                Setiap racikan dibuat dengan ketelitian dan semangat untuk menghadirkan kualitas terbaik, langsung ke tangan Anda.
            </p>
            <div class="flex items-center gap-4">
                <div class="flex -space-x-3">
                    <img class="w-10 h-10 rounded-full border-2 border-cream object-cover" src="dashboard/img/avatar/WhatsApp Image 2026-09-29 at 8.47.25 AM.jpeg" alt="avatar">
                    <img class="w-10 h-10 rounded-full border-2 border-cream object-cover" src="dashboard/img/avatar/WhatsApp Image 2026-09-29 at 8.47.45 AM.jpeg" alt="avatar">
                    <img class="w-10 h-10 rounded-full border-2 border-cream object-cover" src="dashboard/img/avatar/WhatsApp Image 2026-09-29 at 8.47.59 AM.jpeg" alt="avatar">
                </div>
                <p class="text-xs font-semibold text-stone-ink">Disukai oleh 1000+ Pelanggan</p>
            </div>
            
            <!-- Illustration / Hand drawn text -->
            <div class="absolute top-0 -right-10 opacity-70 hidden lg:block text-right">
                <img src="https://cdn-icons-png.flaticon.com/512/3063/3063162.png" class="w-24 opacity-40 ml-auto mb-2" alt="icon">
                <span class="font-handwriting text-2xl text-clay transform -rotate-6 inline-block">Kopi<br>adalah<br>cerita.</span>
            </div>
        </div>
    </section>

    <!-- PILIHAN TERBAIK (PRODUK CAROUSEL) -->
    <section id="produk" class="bg-warm/50 py-20 relative">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-12">
            <!-- Header & Link -->
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-6">
                <div>
                    <span class="text-clay text-[10px] font-bold tracking-widest uppercase mb-2 block">Favorit Singgah</span>
                    <h2 class="font-serif text-3xl md:text-4xl text-stone-ink">Pilihan Terbaik Kami</h2>
                </div>
                
                <div class="flex items-center">
                    <a href="menu.php" class="text-sm font-semibold hover:text-clay transition whitespace-nowrap">Lihat Menu Lengkap &rarr;</a>
                </div>
            </div>

            <?php
            // Query ambil 6 produk terbaru
            $query = mysqli_query($koneksi, "SELECT tb_produk.*, tb_kategori.nama_kategori FROM tb_produk JOIN tb_kategori ON tb_produk.id_kategori = tb_kategori.id_kategori ORDER BY id DESC LIMIT 6");
            
            $semua_produk = [];
            if ($query && mysqli_num_rows($query) > 0) {
                while ($data = mysqli_fetch_array($query)) {
                    $semua_produk[] = $data;
                }
            }
            
            // Pecah array produk menjadi per-3 item untuk setiap slide
            $slides = !empty($semua_produk) ? array_chunk($semua_produk, 3) : [];
            ?>

            <!-- Grid Produk (Carousel Tailwind Vanilla JS) -->
            <div class="relative w-full overflow-hidden pb-4" id="carouselContainer">
                <!-- Inner track -->
                <div id="carouselTrack" class="flex transition-transform duration-500 ease-in-out w-full" style="transform: translateX(0%);">
                    <?php if (!empty($slides)): ?>
                        <?php foreach ($slides as $index => $slide_items): ?>
                            <!-- Slide -->
                            <div class="w-full flex-shrink-0">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 px-1">
                                    <?php foreach ($slide_items as $data): ?>
                                        <!-- Product Card Dynamic -->
                                        <div class="bg-white rounded-2xl p-3 shadow-editorial group hover:-translate-y-1 transition duration-300 flex flex-col h-full">
                                            <div class="relative h-48 rounded-xl overflow-hidden mb-4 bg-sand/20 shrink-0">
                                                <!-- Badge Kategori Dinamis -->
                                                <div class="absolute top-2 left-2 bg-white/90 text-[9px] font-bold px-2 py-1 rounded-md z-10 uppercase">
                                                    <?= htmlspecialchars($data['nama_kategori']) ?>
                                                </div>
                                                
                                                <!-- Gambar Produk dari DB -->
                                                <img src="dashboard/img/<?= htmlspecialchars($data['poto']) ?>" alt="<?= htmlspecialchars($data['nama_produk']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                            </div>
                                            
                                            <div class="px-2 pb-2 flex flex-col flex-grow">
                                                <h3 class="font-serif font-semibold text-lg text-stone-ink truncate mb-1" title="<?= htmlspecialchars($data['nama_produk']) ?>">
                                                    <?= htmlspecialchars($data['nama_produk']) ?>
                                                </h3>
                                                <p class="text-[11px] text-stone-ink/60 line-clamp-2 mb-4 h-8">
                                                    <?= htmlspecialchars($data['deskripsi']) ?>
                                                </p>
                                                
                                                <!-- Harga dan Tombol Beli -->
                                                <div class="flex items-center justify-between border-t border-sand/30 pt-3 mt-auto">
                                                    <span class="font-semibold text-clay text-sm">
                                                        Rp <?= number_format($data['harga'], 0, ',', '.') ?>
                                                    </span>
                                                    
                                                    <!-- Tombol Detail -->
                                                    <a href="detail.php?id=<?= $data['id'] ?>" class="text-[10px] font-bold bg-stone-ink text-white px-4 py-1.5 rounded-full hover:bg-clay transition">
                                                        Detail
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="w-full bg-white text-center py-12 rounded-2xl text-stone-ink/60 shadow-editorial border border-sand/30">Belum ada produk yang tersedia di database.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tombol Navigasi Carousel -->
            <?php if (count($slides) > 1): ?>
                <button id="prevBtn" class="absolute top-1/2 mt-8 -translate-y-1/2 left-0 md:left-4 w-10 h-10 bg-white border border-sand/50 rounded-full flex items-center justify-center text-stone-ink shadow-md hover:bg-stone-ink hover:text-white transition z-10">
                    &larr;
                </button>
                <button id="nextBtn" class="absolute top-1/2 mt-8 -translate-y-1/2 right-0 md:right-4 w-10 h-10 bg-white border border-sand/50 rounded-full flex items-center justify-center text-stone-ink shadow-md hover:bg-stone-ink hover:text-white transition z-10">
                    &rarr;
                </button>
            <?php endif; ?>

        </div>
    </section>

    <!-- AMBIENCE / VIDEO SECTION -->
    <section class="bg-[#2C2723] text-white py-20 overflow-hidden">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-12 flex flex-col lg:flex-row items-center gap-12">
            <!-- Kiri -->
            <div class="w-full lg:w-5/12">
                <span class="text-sand text-[10px] font-bold tracking-widest uppercase mb-4 block">Suasana</span>
                <h2 class="font-serif text-3xl md:text-5xl leading-tight mb-6">Lebih dari Sekadar<br>Tempat Ngopi</h2>
                <p class="text-sand/70 text-sm leading-relaxed mb-8 max-w-sm">
                    Di sini, kamu bisa menikmati kopi, makanan lezat, serta suasana yang nyaman untuk bekerja, belajar, atau sekadar bersantai.
                </p>
                <button class="flex items-center gap-3 text-sm font-semibold hover:text-clay transition">
                    <div class="w-10 h-10 rounded-full bg-hidden text-stone-ink flex items-center justify-center pl-1">
                        
                    </div>
                   
                </button>
            </div>
            
            <!-- Kanan (Staggered Images) -->
            <div class="w-full lg:w-7/12 relative h-[350px] md:h-[450px]">
                <!-- Center Image -->
                <img src="https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&q=80" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 md:w-80 h-48 md:h-64 object-cover rounded-2xl border-4 border-[#2C2723] z-20 shadow-2xl" alt="ambience">
                <!-- Top Left -->
                <img src="https://images.unsplash.com/photo-1497935586351-b67a49e012bf?auto=format&fit=crop&q=80" class="absolute top-0 left-0 md:left-10 w-48 md:w-60 h-36 md:h-48 object-cover rounded-2xl opacity-70 z-10" alt="ambience 2">
                <!-- Bottom Right -->
                <img src="https://images.unsplash.com/photo-1559925393-8be0ec4767c8?auto=format&fit=crop&q=80" class="absolute bottom-0 right-0 md:right-10 w-56 md:w-72 h-40 md:h-56 object-cover rounded-2xl opacity-70 z-10" alt="ambience 3">
                <!-- Text Detail -->
                <div class="absolute bottom-10 -right-4 font-handwriting text-2xl text-sand transform -rotate-12 z-30 hidden md:block">
                    Tempat<br>untuk semua<br>cerita baik
                </div>
            </div>
        </div>
    </section>

    <!-- TESTIMONI -->
    <section class="max-w-[1400px] mx-auto px-6 lg:px-12 py-20">
        <div class="flex flex-col sm:flex-row items-center justify-between mb-12">
            <div>
                <span class="text-clay text-[10px] font-bold tracking-widest uppercase mb-2 block">Testimoni</span>
                <h2 class="font-serif text-3xl text-stone-ink">Kata Mereka yang Singgah</h2>
            </div>
            <div class="text-right mt-4 sm:mt-0">
                <div class="font-serif text-2xl font-bold flex items-center justify-end gap-2 text-stone-ink">
                    4.9/5 <span class="text-clay text-lg">★★★★★</span>
                </div>
                <p class="text-xs text-stone-ink/50 mt-1">Dari 1.000+ ulasan pelanggan</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <!-- Nav Prev -->
            <button class="w-10 h-10 rounded-full border border-sand flex items-center justify-center text-stone-ink hover:bg-stone-ink hover:text-white transition hidden md:flex shrink-0">&larr;</button>
            
            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full">
                <!-- Card 1 -->
                <div class="bg-white p-6 rounded-2xl border border-sand/40 shadow-sm flex flex-col justify-between h-full">
                    <div>
                        <div class="text-clay text-xs mb-3 tracking-widest">★★★★★</div>
                        <p class="text-stone-ink/70 text-[13px] leading-relaxed mb-6">"Butterscotch-nya juara! Gak kemanisan, kopinya tetep berasa. Cocok banget buat nemenin nugas seharian."</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <img src="https://i.pravatar.cc/100?img=15" class="w-8 h-8 rounded-full object-cover" alt="Raka">
                        <span class="text-xs font-bold text-stone-ink">Rakha, 16</span>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="bg-white p-6 rounded-2xl border border-sand/40 shadow-sm flex flex-col justify-between h-full">
                    <div>
                        <div class="text-clay text-xs mb-3 tracking-widest">★★★★★</div>
                        <p class="text-stone-ink/70 text-[13px] leading-relaxed mb-6">"Tempat asik, kopinya proper. Favorit gue tetep Kopi Aren sih, cream-nya pas dan ga bikin eneg."</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <img src="https://i.pravatar.cc/100?img=9" class="w-8 h-8 rounded-full object-cover" alt="Dinda">
                        <span class="text-xs font-bold text-stone-ink">Dinda, 17</span>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="bg-white p-6 rounded-2xl border border-sand/40 shadow-sm flex flex-col justify-between h-full">
                    <div>
                        <div class="text-clay text-xs mb-3 tracking-widest">★★★★★</div>
                        <p class="text-stone-ink/70 text-[13px] leading-relaxed mb-6">"Harganya pas di kantong pelajar, tapi rasanya istimewa. Bakal sering ke sini kelihatannya, sepulang sekolah."</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <img src="https://i.pravatar.cc/100?img=33" class="w-8 h-8 rounded-full object-cover" alt="Rina">
                        <span class="text-xs font-bold text-stone-ink">Rina, 16</span>
                    </div>
                </div>
            </div>

            <!-- Nav Next -->
            <button class="w-10 h-10 rounded-full border border-sand flex items-center justify-center text-stone-ink hover:bg-stone-ink hover:text-white transition hidden md:flex shrink-0">&rarr;</button>
        </div>
    </section>

    <!-- PROMO / NEWSLETTER -->
    <section class="max-w-[1400px] mx-auto px-6 lg:px-12 mb-20">
        <div class="bg-[#241E1C] rounded-3xl p-8 md:p-12 flex flex-col md:flex-row items-center justify-between gap-8 relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="font-serif text-2xl md:text-3xl text-white mb-2">Jangan Lewatkan<br>Promo Spesial Kami!</h3>
                <p class="text-sand/60 text-xs max-w-sm">Dapatkan kabar terbaru, menu baru, dan promo menarik dari Kopi Singgah langsung ke email kamu.</p>
            </div>
            <div class="relative z-10 w-full md:w-auto">
                <form class="flex bg-white rounded-full p-1.5 min-w-[300px] shadow-lg">
                    <input type="email" placeholder="Masukkan email kamu..." class="bg-transparent border-none outline-none text-sm px-4 w-full text-stone-ink placeholder-stone-ink/40">
                    <button type="submit" class="bg-clay text-white text-xs font-bold px-6 py-3 rounded-full hover:bg-stone-ink transition">Berlangganan</button>
                </form>
            </div>
            
            <!-- Decor -->
            <div class="absolute right-10 bottom-4 opacity-10 font-handwriting text-5xl text-white transform -rotate-6 pointer-events-none">
                Stay<br>Connected<br>♡
            </div>
        </div>
    </section>
            </main>
    

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-container">
            <!-- Kolom 1: Kopi Singgah -->
            <div class="footer-col">
                <h3><i class="fa-solid fa-location-dot"></i> Kopi Singgah.</h3>
                <p>Berhenti sejenak, nikmati rasanya. Kami menyeduh kopi terbaik dengan sepenuh hati untuk menemani hari Anda yang lebih baik.</p>
            </div>

            <div class="footer-col">
                <h4>EKSPLOR</h4>
                <ul>
                    <li><a href="#">Beranda</a></li>
                    <li><a href="#">Menu Lengkap</a></li>
                    <li><a href="#">Tentang Kami</a></li>
                    <li><a href="#">Produk</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>HUBUNGI KAMI</h4>
                <ul class="contact-links">
                    <li><a href="#"><i class="fa-brands fa-instagram"></i> Instagram @kopisinggah</a></li>
                    <li><a href="#"><i class="fa-brands fa-tiktok"></i> TikTok @kopisinggah</a></li>
                    <li><a href="#"><i class="fa-brands fa-whatsapp"></i> WhatsApp: 0857-9523-0799</a></li>
                    <li><a href="#"><i class="fa-regular fa-envelope"></i> Email: hello@kopisinggah.id</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 Kopi Singgah. All rights reserved.</p>
            <div class="legal-links">
                <a href="#">Privacy Policy</a> | <a href="#">Syarat & Ketentuan</a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Script untuk Hero Thumbnail
            const thumbnails = document.querySelectorAll(".hero-thumbnail");
            const mainImage = document.getElementById("mainHeroImage");

            if (mainImage && thumbnails.length > 0) {
                thumbnails.forEach(thumb => {
                    thumb.addEventListener("click", function() {
                        thumbnails.forEach(t => {
                            t.classList.remove("border-clay", "scale-110", "shadow-editorial");
                            t.classList.add("border-stone-ink/10", "opacity-70");
                        });

                        this.classList.remove("border-stone-ink/10", "opacity-70");
                        this.classList.add("border-clay", "scale-110", "shadow-editorial");

                        mainImage.style.opacity = 0; 
                        setTimeout(() => {
                            const newSrc = this.getAttribute("data-img");
                            mainImage.setAttribute("src", newSrc); 
                            mainImage.style.opacity = 1; 
                        }, 150); 
                    });
                });
            }

            // Script Khusus untuk Produk Carousel (Dengan Tombol Sembunyi Otomatis)
            const track = document.getElementById("carouselTrack");
            const prevBtn = document.getElementById("prevBtn");
            const nextBtn = document.getElementById("nextBtn");
            
            if (track && prevBtn && nextBtn) {
                let currentIndex = 0;
                const slides = track.children;
                const totalSlides = slides.length;

                function updateCarousel() {
                    // Geser slide
                    track.style.transform = `translateX(-${currentIndex * 100}%)`;

                    // Sembunyikan panah kiri di slide pertama
                    if (currentIndex === 0) {
                        prevBtn.classList.add("hidden");
                        prevBtn.classList.remove("flex");
                    } else {
                        prevBtn.classList.remove("hidden");
                        prevBtn.classList.add("flex");
                    }

                    // Sembunyikan panah kanan di slide terakhir
                    if (currentIndex === totalSlides - 1) {
                        nextBtn.classList.add("hidden");
                        nextBtn.classList.remove("flex");
                    } else {
                        nextBtn.classList.remove("hidden");
                        nextBtn.classList.add("flex");
                    }
                }

                // Jalankan sekali saat web di-load
                updateCarousel();

                prevBtn.addEventListener("click", () => {
                    if (currentIndex > 0) {
                        currentIndex--;
                        updateCarousel();
                    }
                });

                nextBtn.addEventListener("click", () => {
                    if (currentIndex < totalSlides - 1) {
                        currentIndex++;
                        updateCarousel();
                    }
                });
            }
        });
    </script>
</body>
</html>