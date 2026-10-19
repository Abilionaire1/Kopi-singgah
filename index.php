<?php
session_start();
include 'koneksi.php';
/** @var mysqli $koneksi */

// Ambil 4 produk terbaru untuk Best Seller (hero section)
$hero_products = [];
$query_hero = mysqli_query($koneksi, "SELECT tb_produk.*, tb_kategori.nama_kategori FROM tb_produk JOIN tb_kategori ON tb_produk.id_kategori = tb_kategori.id_kategori ORDER BY id ASC LIMIT 4");
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
        document.documentElement.classList.remove('dark');
        try { localStorage.removeItem('theme'); } catch(e) {}
        tailwind.config = {
            darkMode: 'class',
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
        /* Mobile-First CSS Reset & Responsiveness */
        *, *::before, *::after {
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        img, svg {
            max-width: 100%;
            height: auto;
        }

        .page-wrapper {
            display: flex;
            flex-direction: column;
            min-height: auto;
            width: 100%;
            max-width: 100%;
        }
        .main-content {
            flex: 1;
            background-color: #f8f5f0; 
            background-image: radial-gradient(#e5d9c5 1px, transparent 1px);
            background-size: 20px 20px;
            padding: 1.25rem;
            text-align: center;
            width: 100%;
            max-width: 100%;
        }

        /* --- STYLING FOOTER --- */
        .footer {
            background-color: #1a1814;
            color: #8b857d;
            padding: 60px 0 20px;
            width: 100%;
        }
        .footer-container {
            display: flex;
            justify-content: space-between;
            max-width: 1100px;
            width: 100%;
            margin: 0 auto;
            padding: 0 20px;
            gap: 40px;
            flex-wrap: wrap;
        }
        .footer-col {
            flex: 1;
            min-width: 180px;
            max-width: 100%;
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
            width: 100%;
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

        /* ====================================================
           MOBILE BASE (< 768px, misal 390px)
           ==================================================== */
        .nav-mobile-btn {
            display: flex !important;
        }
        .nav-desktop-links,
        .nav-desktop-auth {
            display: none !important;
        }

        .hero-section-custom {
            position: relative;
            width: 100%;
            min-height: auto;
            padding-top: 1.5rem;
            padding-bottom: 3.5rem;
            background-image: none;
            overflow: hidden;
        }

        .hero-container-custom {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 100%;
            padding: 0 1rem;
            margin: 0 auto;
        }

        .hero-text-block {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .hero-eyebrow-text {
            color: #A0634A;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
            display: block;
        }

        .hero-headline-text {
            font-family: 'Fraunces', serif;
            font-size: 40px;
            line-height: 1.15;
            color: #1E1B18;
            margin: 0 0 1rem 0;
        }

        .hero-desc-text {
            font-family: 'Inter', sans-serif;
            color: rgba(30, 27, 24, 0.75);
            font-size: 15px;
            line-height: 1.6;
            margin: 0 0 1rem 0;
            max-width: 100%;
        }

        .hero-img-block {
            width: 100%;
            display: flex;
            justify-content: center;
            position: relative;
            margin: 0.5rem 0 1rem 0;
        }

        .hero-img-element {
            height: 230px !important;
            max-height: 240px;
            width: auto;
            max-width: 100%;
            object-fit: contain;
        }

        .hero-carousel-block {
            width: 100%;
            display: flex;
            flex-direction: row;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            gap: 0.75rem;
            padding: 0.5rem 0.25rem;
            justify-content: flex-start;
            -webkit-overflow-scrolling: touch;
        }

        .hero-thumb-item-custom {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.35rem;
            flex-shrink: 0;
            scroll-snap-align: start;
            cursor: pointer;
        }

        .hero-thumb-circle-custom {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            overflow: hidden;
            background-color: #F9F6F0;
            transition: all 0.3s ease;
        }

        .hero-thumb-label-custom {
            display: block;
            font-size: 12px;
            color: rgba(30, 27, 24, 0.8);
            text-align: center;
            max-width: 105px;
            line-height: 1.25;
            font-family: 'Inter', sans-serif;
        }

        .hero-desktop-only-content {
            display: none !important;
        }

        .hero-mobile-only-content {
            display: flex !important;
            width: 100%;
            flex-direction: column;
            align-items: center;
            margin-top: 1.5rem;
        }

        .hero-cta-mobile-col {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn-pesan-sekarang {
            width: 100%;
            min-height: 48px;
            padding: 0.875rem 1.5rem;
            border-radius: 9999px;
            background-color: #1E1B18;
            color: #ffffff;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: background-color 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            font-family: 'Inter', sans-serif;
        }
        .btn-pesan-sekarang:hover {
            background-color: #A0634A;
        }

        .btn-lihat-menu {
            width: 100%;
            min-height: 48px;
            padding: 0.875rem 1.5rem;
            border-radius: 9999px;
            border: 1.5px solid #A0634A;
            background-color: transparent;
            color: #A0634A;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
        }
        .btn-lihat-menu:hover {
            background-color: #A0634A;
            color: #ffffff;
        }

        .hero-chips-wrap {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
            font-size: 13px;
            font-weight: 500;
            color: #6B5E55;
            text-align: center;
            font-family: 'Inter', sans-serif;
        }

        .hero-chip-item {
            background-color: rgba(216, 202, 179, 0.35);
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .hero-features-mobile-grid {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(216, 202, 179, 0.4);
        }

        .feature-cell {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 0.25rem;
        }

        .feature-cell svg {
            width: 24px;
            height: 24px;
            color: #A0634A;
            margin-bottom: 0.5rem;
            flex-shrink: 0;
        }

        .feature-cell span {
            font-size: 14px;
            font-weight: 600;
            color: #1E1B18;
            line-height: 1.25;
            font-family: 'Inter', sans-serif;
        }

        /* ====================================================
           DESKTOP (@media (min-width: 768px))
           ==================================================== */
        @media (min-width: 768px) {
            .nav-mobile-btn {
                display: none !important;
            }
            .nav-desktop-links,
            .nav-desktop-auth {
                display: flex !important;
            }

            .hero-section-custom {
                padding-top: 4rem;
                padding-bottom: 5rem;
                background-image: url('https://www.transparenttextures.com/patterns/cubes.png');
            }

            .hero-container-custom {
                flex-direction: row;
                align-items: center;
                max-width: 1400px;
                padding: 0 3rem;
            }

            .hero-text-block {
                width: 41.666667%;
                padding-right: 2.5rem;
                margin-bottom: 0;
            }

            .hero-headline-text {
                font-size: 4rem;
                line-height: 1.1;
                margin-bottom: 1.5rem;
            }

            .hero-desc-text {
                font-size: 16px;
                margin-bottom: 2rem;
            }

            .hero-img-block {
                width: 33.333333%;
                margin: 0;
            }

            .hero-img-element {
                height: 500px !important;
                max-height: 500px;
            }

            .hero-carousel-block {
                width: 33.333333%;
                flex-direction: column;
                align-items: flex-end;
                justify-content: flex-end;
                gap: 1.25rem;
                overflow-x: visible;
                padding: 0 1.5rem 0 0;
                margin-top: 0;
            }

            .hero-thumb-item-custom {
                align-items: flex-end;
            }

            .hero-thumb-circle-custom {
                width: 96px;
                height: 96px;
            }

            .hero-thumb-label-custom {
                display: none !important;
            }

            .hero-desktop-only-content {
                display: block !important;
            }

            .hero-mobile-only-content {
                display: none !important;
            }
        }
    </style>
</head>

<body class="bg-cream text-stone-ink font-sans antialiased overflow-x-hidden">

    <!-- NAVBAR -->
    <nav class="w-full bg-cream/90 backdrop-blur-md sticky top-0 z-50 border-b border-sand/30">
        <div class="nav-container max-w-[1400px] mx-auto flex items-center justify-between">
            
            <!-- Left: Mobile Burger Button -->
            <button id="mobileBurgerBtn" 
                    type="button" 
                    aria-expanded="false" 
                    aria-controls="mobileDrawer" 
                    aria-label="Buka menu navigasi"
                    class="nav-mobile-btn md:hidden w-11 h-11 min-w-[44px] min-h-[44px] flex items-center justify-center text-stone-ink hover:text-[#A0634A] rounded-lg transition -ml-2 focus:outline-none focus:ring-2 focus:ring-[#A0634A]/30">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>

            <!-- Logo -->
            <a href="index.php" class="font-serif text-xl md:text-2xl font-semibold flex items-center gap-2">
                <svg class="w-6 h-6 text-[#A0634A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                Kopi Singgah.
            </a>

            <!-- Desktop Center Links (Hidden on mobile < 768px) -->
            <div class="nav-desktop-links hidden md:flex items-center gap-8 lg:gap-10 text-sm font-medium">
                <a href="#" class="border-b border-stone-ink pb-1 whitespace-nowrap">Beranda</a>
                <a href="menu.php" class="text-stone-ink/60 hover:text-stone-ink transition whitespace-nowrap">Menu</a>
                <a href="#about" class="text-stone-ink/60 hover:text-stone-ink transition whitespace-nowrap">Tentang Kami</a>
                <a href="#produk" class="text-stone-ink/60 hover:text-stone-ink transition whitespace-nowrap">Produk</a>
            </div>

            <!-- Right Icons -->
            <div class="flex items-center gap-1 sm:gap-2 md:gap-5">
                <button type="button" aria-label="Cari" class="w-11 h-11 min-w-[44px] min-h-[44px] flex items-center justify-center text-stone-ink hover:text-[#A0634A] transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>
                <a href="keranjang.php" aria-label="Keranjang" class="w-11 h-11 min-w-[44px] min-h-[44px] flex items-center justify-center text-stone-ink hover:text-[#A0634A] transition relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <!-- Count Badge Example -->
                    <span class="absolute top-1.5 right-1.5 bg-[#A0634A] text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none <?= (isset($_SESSION['keranjang']) && array_sum(array_column($_SESSION['keranjang'], 'jumlah'))) > 0 ? '' : 'hidden' ?>">
                        <?= isset($_SESSION['keranjang']) ? array_sum(array_column($_SESSION['keranjang'], 'jumlah')) : 0 ?>
                    </span>
                </a>
                
                <!-- Desktop Auth Buttons (Hidden on mobile < 768px) -->
                <div class="nav-desktop-auth hidden md:flex items-center gap-5">
                    <?php if(!empty($_SESSION['user_id']) || !empty($_SESSION['id_user'])): ?>
                        <a href="profile.php" class="text-stone-ink/60 hover:text-stone-ink text-sm font-medium transition">Profil</a>
                        <a href="logout.php" class="border border-stone-ink text-stone-ink text-xs font-semibold px-5 py-2.5 rounded-full hover:bg-stone-ink hover:text-white transition">Keluar</a>
                    <?php else: ?>
                        <a href="login.php" class="border border-stone-ink text-stone-ink text-xs font-semibold px-5 py-2.5 rounded-full hover:bg-stone-ink hover:text-white transition">Masuk / Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Drawer Backdrop & Panel -->
    <div id="mobileDrawerBackdrop" class="fixed inset-0 bg-stone-ink/50 backdrop-blur-sm z-50 opacity-0 pointer-events-none transition-opacity duration-300 md:hidden"></div>

    <aside id="mobileDrawer" 
           aria-hidden="true" 
           class="fixed top-0 left-0 bottom-0 w-[290px] max-w-[85vw] bg-cream z-50 shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out flex flex-col justify-between p-6 md:hidden font-sans">
        <div>
            <!-- Header Drawer: Logo & Tombol X -->
            <div class="flex items-center justify-between pb-4 border-b border-sand/40">
                <span class="font-serif text-lg font-bold text-stone-ink flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#A0634A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                    Kopi Singgah.
                </span>
                <button id="closeDrawerBtn" 
                        type="button" 
                        aria-label="Tutup menu navigasi" 
                        class="w-11 h-11 min-w-[44px] min-h-[44px] flex items-center justify-center text-stone-ink hover:text-[#A0634A] rounded-lg transition -mr-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Item Navigasi -->
            <nav class="flex flex-col gap-2 mt-6">
                <a href="#" class="drawer-link flex items-center justify-between py-3 px-4 rounded-2xl font-medium text-stone-ink bg-sand/30">
                    <span>Beranda</span>
                    <span class="text-xs text-[#A0634A]">&bull;</span>
                </a>
                <a href="menu.php" class="drawer-link flex items-center justify-between py-3 px-4 rounded-2xl font-medium text-stone-ink/70 hover:bg-sand/20 hover:text-stone-ink transition">
                    <span>Menu</span>
                </a>
                <a href="#about" class="drawer-link flex items-center justify-between py-3 px-4 rounded-2xl font-medium text-stone-ink/70 hover:bg-sand/20 hover:text-stone-ink transition">
                    <span>Tentang Kami</span>
                </a>
                <a href="#produk" class="drawer-link flex items-center justify-between py-3 px-4 rounded-2xl font-medium text-stone-ink/70 hover:bg-sand/20 hover:text-stone-ink transition">
                    <span>Produk</span>
                </a>
            </nav>
        </div>

        <!-- Bawah Drawer: Tombol Masuk / Daftar Outline Cokelat -->
        <div class="pt-6 border-t border-sand/40 flex flex-col gap-3">
            <?php if(!empty($_SESSION['user_id']) || !empty($_SESSION['id_user'])): ?>
                <a href="profile.php" class="w-full py-3.5 px-4 text-center rounded-full font-semibold text-sm border border-[#A0634A] text-[#A0634A] hover:bg-[#A0634A] hover:text-white transition">Profil Saya</a>
                <a href="logout.php" class="w-full py-2.5 text-center text-xs text-stone-ink/60 hover:text-stone-ink transition">Keluar</a>
            <?php else: ?>
                <a href="login.php" class="w-full py-3.5 px-4 text-center rounded-full font-semibold text-sm border border-[#A0634A] text-[#A0634A] hover:bg-[#A0634A] hover:text-white transition">Masuk / Daftar</a>
            <?php endif; ?>
        </div>
    </aside>

<main>
    <!-- HERO SECTION -->
    <section class="hero-section-custom relative w-full overflow-hidden">
        <div class="hero-container-custom relative z-10">
            
            <!-- Kiri (Text di Mobile & Desktop) -->
            <div class="hero-text-block">
                <span class="hero-eyebrow-text">KOPI KEKINIAN</span>
                <h1 class="hero-headline-text">
                    Setiap Singgah<br>Terdapat Rasa
                </h1>
                <p class="hero-desc-text">
                    Jelajahi koleksi kopi kami dan temukan rasa favorit Anda. Diseduh secara ahli menggunakan biji kopi pilihan terbaik khusus untuk Anda.
                </p>

                <!-- Desktop CTA, Info & Features (Hidden on mobile < 768px, visible on desktop >= 768px) -->
                <div class="hero-desktop-only-content">
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="#produk" class="inline-flex items-center gap-3 bg-stone-ink text-white text-sm px-8 py-3.5 rounded-full font-semibold hover:bg-clay transition shadow-sm">
                            Pesan Sekarang &rarr;
                        </a>
                        <a href="menu.php" class="inline-flex items-center gap-3 border border-stone-ink text-stone-ink text-sm px-8 py-3.5 rounded-full font-semibold hover:bg-stone-ink hover:text-white transition">
                            Lihat Menu
                        </a>
                    </div>
                    <p class="text-xs text-[#8B827A] mt-4 flex items-center gap-1.5">
                        <span>Mulai Rp 18.000</span>
                        <span>·</span>
                        <span class="text-clay">★ 4,9</span>
                        <span>·</span>
                        <span>Tiba dalam 25 menit</span>
                    </p>

                    <!-- Features Desktop -->
                    <div class="flex flex-wrap gap-6 mt-12 pt-8 border-t border-sand/40">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-[#A0634A] shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <ellipse cx="12" cy="12" rx="8" ry="9" transform="rotate(-30 12 12)"/>
                                <path stroke-linecap="round" d="M8.5 5.5C11 9 10 14 15.5 18.5"/>
                            </svg>
                            <span class="text-[14px] font-semibold text-stone-ink leading-snug">Biji Kopi<br>Pilihan</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-[#A0634A] shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                            </svg>
                            <span class="text-[14px] font-semibold text-stone-ink leading-snug">Resep Autentik<br>Setiap Seduhan</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-[#A0634A] shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <circle cx="5.5" cy="17.5" r="3.5"/>
                                <circle cx="18.5" cy="17.5" r="3.5"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 6h-2.5l-3 7h6l2-4H15V6zM5.5 17.5l4-7h5.5M18.5 17.5l-2-4.5"/>
                            </svg>
                            <span class="text-[14px] font-semibold text-stone-ink leading-snug">Pengiriman<br>Cepat & Aman</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tengah: Foto Gelas Es Kopi (Tampil di Layar Pertama Tanpa Terpotong) -->
            <div class="hero-img-block">
                <img id="mainHeroImage" 
                     src="dashboard/img/display/salted-caramel-hero.png"
                     alt="Produk Utama"
                     class="hero-img-element hero-center-img transition-opacity duration-300"> 
            </div>

            <!-- Kanan: Carousel Varian Thumbnail Horizontal di Mobile, Vertikal di Desktop -->
            <div class="hero-carousel-block no-scrollbar">
                <div class="hero-thumb-item-custom hero-thumb-item" data-img="dashboard/img/display/salted-caramel-hero.png">
                    <div class="hero-thumbnail hero-thumb-circle-custom border-2 border-[#A0634A] scale-105 shadow-editorial">
                        <img src="dashboard/img/display/salted-caramel-hero.png" class="w-full h-full object-cover" alt="Es Kopi Susu Gula Aren">
                    </div>
                    <span class="hero-thumb-label-custom font-medium mt-0.5">
                        Es Kopi Susu Gula Aren · <strong class="text-stone-ink">Rp 22.000</strong>
                    </span>
                </div>

                <div class="hero-thumb-item-custom hero-thumb-item" data-img="dashboard/img/display/berry-americano-hero.png">
                    <div class="hero-thumbnail hero-thumb-circle-custom border border-stone-ink/20 opacity-70 hover:border-[#A0634A] hover:opacity-100">
                        <img src="dashboard/img/display/berry-americano-hero.png" class="w-full h-full object-cover" alt="Strawberry Latte">
                    </div>
                    <span class="hero-thumb-label-custom font-medium mt-0.5">
                        Strawberry Latte · <strong class="text-stone-ink">Rp 24.000</strong>
                    </span>
                </div>

                <div class="hero-thumb-item-custom hero-thumb-item" data-img="dashboard/img/display/creamy-matcha-hero.png">
                    <div class="hero-thumbnail hero-thumb-circle-custom border border-stone-ink/20 opacity-70 hover:border-[#A0634A] hover:opacity-100">
                        <img src="dashboard/img/display/creamy-matcha-hero.png" class="w-full h-full object-cover" alt="Matcha Latte">
                    </div>
                    <span class="hero-thumb-label-custom font-medium mt-0.5">
                        Matcha Latte · <strong class="text-stone-ink">Rp 25.000</strong>
                    </span>
                </div>
            </div>

            <!-- Mobile Only: CTA, Info Produk & Strip Fitur (Hidden on desktop >= 768px) -->
            <div class="hero-mobile-only-content">
                <!-- CTA Vertikal Selebar Penuh -->
                <div class="hero-cta-mobile-col">
                    <a href="#produk" class="btn-pesan-sekarang">
                        Pesan Sekarang &rarr;
                    </a>
                    <a href="menu.php" class="btn-lihat-menu">
                        Lihat Menu
                    </a>
                </div>

                <!-- Info Produk (Chip Kecil #6B5E55) -->
                <div class="hero-chips-wrap">
                    <span class="hero-chip-item">Mulai Rp 18.000</span>
                    <span class="hero-chip-item">
                        <span class="text-[#A0634A]">★</span> 4,9
                    </span>
                    <span class="hero-chip-item">Tiba dalam 25 menit</span>
                </div>

                <!-- Strip Fitur Mobile: 3 kolom sama lebar, ikon 24px di atas teks 14px -->
                <div class="hero-features-mobile-grid">
                    <div class="feature-cell">
                        <svg class="feature-icon" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <ellipse cx="12" cy="12" rx="8" ry="9" transform="rotate(-30 12 12)"/>
                            <path stroke-linecap="round" d="M8.5 5.5C11 9 10 14 15.5 18.5"/>
                        </svg>
                        <span class="feature-text">Biji Kopi<br>Pilihan</span>
                    </div>

                    <div class="feature-cell">
                        <svg class="feature-icon" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                        </svg>
                        <span class="feature-text">Resep Autentik<br>Setiap Seduhan</span>
                    </div>

                    <div class="feature-cell">
                        <svg class="feature-icon" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <circle cx="5.5" cy="17.5" r="3.5"/>
                            <circle cx="18.5" cy="17.5" r="3.5"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 6h-2.5l-3 7h6l2-4H15V6zM5.5 17.5l4-7h5.5M18.5 17.5l-2-4.5"/>
                        </svg>
                        <span class="feature-text">Pengiriman<br>Cepat & Aman</span>
                    </div>
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
            $query = mysqli_query($koneksi, "SELECT tb_produk.*, tb_kategori.nama_kategori FROM tb_produk JOIN tb_kategori ON tb_produk.id_kategori = tb_kategori.id_kategori ORDER BY id ASC LIMIT 6");
            
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
            <digiv class="relative z-10">
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
            localStorage.removeItem('theme');
            document.documentElement.classList.remove('dark');

            // Script Drawer Mobile
            const burgerBtn = document.getElementById("mobileBurgerBtn");
            const drawer = document.getElementById("mobileDrawer");
            const backdrop = document.getElementById("mobileDrawerBackdrop");
            const closeDrawerBtn = document.getElementById("closeDrawerBtn");

            function openDrawer() {
                if (!drawer || !backdrop || !burgerBtn) return;
                drawer.classList.remove("-translate-x-full");
                drawer.classList.add("translate-x-0");
                backdrop.classList.remove("opacity-0", "pointer-events-none");
                backdrop.classList.add("opacity-100", "pointer-events-auto");
                burgerBtn.setAttribute("aria-expanded", "true");
                drawer.setAttribute("aria-hidden", "false");
                document.body.classList.add("overflow-hidden");
            }

            function closeDrawer() {
                if (!drawer || !backdrop || !burgerBtn) return;
                drawer.classList.remove("translate-x-0");
                drawer.classList.add("-translate-x-full");
                backdrop.classList.remove("opacity-100", "pointer-events-auto");
                backdrop.classList.add("opacity-0", "pointer-events-none");
                burgerBtn.setAttribute("aria-expanded", "false");
                drawer.setAttribute("aria-hidden", "true");
                document.body.classList.remove("overflow-hidden");
            }

            if (burgerBtn) burgerBtn.addEventListener("click", openDrawer);
            if (closeDrawerBtn) closeDrawerBtn.addEventListener("click", closeDrawer);
            if (backdrop) backdrop.addEventListener("click", closeDrawer);

            document.addEventListener("keydown", function(e) {
                if (e.key === "Escape" && drawer && !drawer.classList.contains("-translate-x-full")) {
                    closeDrawer();
                }
            });

            document.querySelectorAll(".drawer-link").forEach(link => {
                link.addEventListener("click", closeDrawer);
            });

            // Script untuk Hero Thumbnail (Mobile & Desktop)
            const thumbItems = document.querySelectorAll(".hero-thumb-item");
            const mainImage = document.getElementById("mainHeroImage");

            if (mainImage && thumbItems.length > 0) {
                thumbItems.forEach(item => {
                    item.addEventListener("click", function() {
                        thumbItems.forEach(t => {
                            const ring = t.querySelector(".hero-thumbnail") || t;
                            ring.classList.remove("border-2", "border-[#A0634A]", "border-clay", "scale-105", "scale-110", "shadow-editorial");
                            ring.classList.add("border", "border-stone-ink/20", "opacity-70");
                        });

                        const activeRing = this.querySelector(".hero-thumbnail") || this;
                        activeRing.classList.remove("border", "border-stone-ink/20", "opacity-70");
                        activeRing.classList.add("border-2", "border-[#A0634A]", "scale-105", "shadow-editorial");

                        mainImage.style.opacity = 0; 
                        setTimeout(() => {
                            const newSrc = this.getAttribute("data-img") || activeRing.getAttribute("data-img");
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