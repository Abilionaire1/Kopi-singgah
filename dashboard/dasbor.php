<?php

include '../koneksi.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);
/** @var mysqli $koneksi */
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('location: ../login.php');
    exit;
}

// === CEK MODE EDIT ===
$aksi = $_GET['aksi'] ?? '';
$id_edit = $_GET['id'] ?? '';

// Variabel default kosong
$nama = $harga = $stok = $id_kategori = $poto_lama = $deskripsi = '';

// Jika mode edit, ambil data dari database
if ($aksi == 'edit' && !empty($id_edit)) {
    $res = mysqli_query($koneksi, "SELECT * FROM tb_produk WHERE id='$id_edit'");
    if ($data = mysqli_fetch_assoc($res)) {
        $nama = $data['nama_produk'];
        $harga = $data['harga'];
        $stok = $data['stok'];
        $id_kategori = $data['id_kategori'];
        $poto_lama = $data['poto'];
        $deskripsi = $data['deskripsi'];
    }
}

// === HAPUS ===
if ($aksi == 'hapus' && !empty($id_edit)) {
    $hapus = mysqli_query($koneksi, "DELETE FROM tb_produk WHERE id='$id_edit'");
    if ($hapus > 0) {
        header('location: dasbor.php');
        exit;
    }
}

// === TAMBAH / UPDATE ===
if (isset($_POST['tambah']) || isset($_POST['update'])) {
    $nama_produk = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $poto = isset($_FILES['poto']['name']) && !empty($_FILES['poto']['name']) ? $_FILES['poto']['name'] : '';

    if ($aksi == 'edit') {
        // EDIT MODE
        if (!empty($poto)) {
            $path = "img/" . $poto;
            $file_tmp = $_FILES['poto']['tmp_name'];
            move_uploaded_file($file_tmp, $path);
        } else {
            $poto = $poto_lama;
        }
        $simpan = mysqli_query($koneksi, "UPDATE tb_produk SET nama_produk='$nama_produk', harga='$harga', stok='$stok', id_kategori='$kategori', deskripsi='$deskripsi', poto='$poto' WHERE id='$id_edit'");
        if ($simpan > 0) {
            header('Location: dasbor.php');
            exit;
        }
    } else {
        // TAMBAH MODE
        if (!empty($poto)) {
            $path = "img/" . $poto;
            $file_tmp = $_FILES['poto']['tmp_name'];
            move_uploaded_file($file_tmp, $path);
        } else {
            $poto = '';
        }
        $simpan = mysqli_query($koneksi, "INSERT INTO tb_produk (nama_produk, harga, stok, id_kategori, poto, deskripsi) VALUES ('$nama_produk', '$harga', '$stok', '$kategori', '$poto', '$deskripsi')");
        if ($simpan > 0) {
            header("location: dasbor.php");
            exit;
        }
    }
}

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>MyDashboard · Admin Panel</title>
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..900;1,9..144,300..900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'serif': ['Fraunces', 'serif'],
                        'sans': ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'cream': '#faf8f5',
                        'warm': '#f5f0e8',
                        'stone-ink': '#2c2a26',
                        'sage': '#8a9a7b',
                        'clay': '#c67b5c',
                        'sand': '#d4c5b0',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-feature-settings: "cv11", "ss01";
        }

        .soft-shadow {
            box-shadow: 0 1px 3px rgba(44, 42, 38, 0.04), 0 8px 24px rgba(44, 42, 38, 0.06);
        }

        .input-soft {
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(44, 42, 38, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .input-soft:focus {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(198, 123, 92, 0.4);
            box-shadow: 0 0 0 4px rgba(198, 123, 92, 0.08);
            outline: none;
        }

        .dark .input-soft {
            background: rgba(44, 42, 38, 0.4);
            border-color: rgba(250, 248, 245, 0.08);
            color: #faf8f5;
        }

        .dark .input-soft:focus {
            background: rgba(44, 42, 38, 0.6);
            border-color: rgba(198, 123, 92, 0.5);
        }

        .btn-soft {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-soft:hover {
            transform: translateY(-1px);
        }

        .nav-item-soft {
            position: relative;
            transition: all 0.25s ease;
            border-radius: 0.5rem;
        }

        .nav-item-soft::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 0;
            background: #c67b5c;
            border-radius: 0 3px 3px 0;
            transition: height 0.25s ease;
        }

        .nav-item-soft:hover::before,
        .nav-item-soft.active::before {
            height: 60%;
        }

        .nav-item-soft:hover,
        .nav-item-soft.active {
            background: rgba(198, 123, 92, 0.06);
        }

        .table-row-soft {
            transition: all 0.2s ease;
        }

        .table-row-soft:hover {
            background: rgba(198, 123, 92, 0.04);
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(44, 42, 38, 0.15);
            border-radius: 3px;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: rgba(250, 248, 245, 0.15);
        }

        /* Tambahkan ini di dalam tag <style> */
        .dark .table-row-soft {
            border-color: rgba(168, 162, 158, 0.25) !important;
        }

        .dark .table-row-soft:nth-child(even) {
            background: rgba(168, 162, 158, 0.04);
        }

        .dark .table-row-soft:hover {
            background: rgba(198, 123, 92, 0.1) !important;
        }

        .dark thead tr {
            border-color: rgba(168, 162, 158, 0.35) !important;
            background: rgba(168, 162, 158, 0.05);
        }
    </style>
</head>

<body class="bg-cream text-stone-ink font-sans antialiased dark:bg-stone-ink dark:text-cream transition-colors duration-500">
    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <aside id="sidebar" class="hidden md:flex w-64 flex-col border-r border-stone-ink/5 dark:border-cream/5 bg-cream dark:bg-stone-ink sticky top-0 h-screen overflow-y-auto flex-shrink-0">
            <div class="px-6 py-5 border-b border-stone-ink/5 dark:border-cream/5">
                <a href="#" class="font-serif text-xl font-light tracking-tight">
                    <span class="text-clay italic">My</span>Dashboard
                </a>
            </div>

            <button id="close-sidebar" class="md:hidden p-1 rounded-lg hover:bg-stone-ink/5 dark:hover:bg-cream/5 text-stone-ink dark:text-cream">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <nav class="flex-1 px-4 py-6 space-y-6">
                <div>
                    <ul class="space-y-1">
                        <li><a href="dasbor.php" class="nav-item-soft active flex items-center gap-3 py-2.5 px-3 font-sans text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Admin Dashboard
                            </a></li>
                        <li><a href="../admin_transaksi.php" class="nav-item-soft flex items-center gap-3 py-2.5 px-3 font-sans text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Transaksi
                            </a></li>
                        <li><a href="dasbor.php#products" class="nav-item-soft flex items-center gap-3 py-2.5 px-3 font-sans text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Produk
                            </a></li>
                        <li><a href="../admin_pelanggan.php" class="nav-item-soft flex items-center gap-3 py-2.5 px-3 font-sans text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                Pelanggan
                            </a></li>
                        
                    </ul>
                </div>

                

            <div class="px-4 py-4 border-t border-stone-ink/5 dark:border-cream/5 space-y-1">
                <a href="#" class="nav-item-soft flex items-center gap-3 py-2.5 px-3 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </a>
                <a href="../index.php" class="nav-item-soft flex items-center gap-3 py-2.5 px-3 text-sm font-medium text-clay">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Sign out
                </a>
            </div>

            <div class="px-4 py-4 border-t border-stone-ink/5 dark:border-cream/5">
                <div class="text-[10px] uppercase tracking-[0.15em] text-stone-ink/40 dark:text-cream/40 font-medium mb-2 px-1">Tampilan</div>
                <div class="relative">
                    <button id="theme-toggle" class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl bg-clay text-cream hover:bg-clay/90 transition-colors text-sm font-medium">
                        <div class="flex items-center gap-2">
                            <span id="theme-icon">☾</span>
                            <span id="theme-label">Dark</span>
                        </div>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div id="theme-dropdown" class="hidden absolute bottom-full left-0 right-0 mb-2 bg-white dark:bg-stone-ink border border-stone-ink/10 dark:border-cream/10 rounded-xl soft-shadow overflow-hidden">
                        <button data-theme="light" class="theme-option w-full text-left px-4 py-3 text-sm hover:bg-clay/10 flex items-center gap-3"><span>☀</span><span>Light</span></button>
                        <button data-theme="dark" class="theme-option w-full text-left px-4 py-3 text-sm hover:bg-clay/10 flex items-center gap-3 border-t border-stone-ink/5 dark:border-cream/5"><span>☾</span><span>Dark</span></button>
                        <button data-theme="auto" class="theme-option w-full text-left px-4 py-3 text-sm hover:bg-clay/10 flex items-center gap-3 border-t border-stone-ink/5 dark:border-cream/5"><span>◐</span><span>Auto</span></button>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 min-w-0">
            <header class="sticky top-0 z-30 bg-cream/80 dark:bg-stone-ink/80 backdrop-blur-xl border-b border-stone-ink/5 dark:border-cream/5">
                <div class="flex items-center justify-between px-8 py-4">
                    <div class="flex items-center gap-4">
                        <button id="menu-toggle" class="md:hidden p-2 rounded-lg hover:bg-stone-ink/5 dark:hover:bg-cream/5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="font-serif text-3xl font-light"><?= $aksi == 'edit' ? 'Edit Produk' : 'Admin Panel' ?></h1>
                    </div>
                    <div class="flex items-center gap-2">
                       
                             <svg xmlns="http://www.w3.org/2000/svg" width="32" height="16" viewBox="0 0 21 21">
</svg>
                        </a>

                        </a><a href="../index.php" class="px-4 py-2 rounded-lg border border-stone-ink/10 dark:border-cream/10 text-xs font-medium hover:bg-stone-ink/5 dark:hover:bg-cream/5 transition-colors flex items-center gap-2">
                             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                                 <g fill="none" fill-rule="evenodd" transform="translate(4 1)">  
                                    <path stroke="#000000" stroke-linecap="round" stroke-linejoin="round" d="M2.5 2.5h2v14h-2a2 2 0 0 1-2-2v-10a2 2 0 0 1 2-2zM7.202.513l4 1.5A2 2 0 0 1 12.5 3.886v11.228a2 2 0 0 1-1.298 1.873l-4 1.5A2 2 0 0 1 4.5 16.614V2.386A2 2 0 0 1 7.202.513z" />                               
                                    <circle cx="6.5" cy="9.5" r="1" fill="#000000" />
                                </g>
                            </svg>
                             Home
                        </a>
                    </div>
                </div>
            </header>

            <div class="p-4 md:p-8 max-w-6xl">

                <!-- FORM CARD -->
                <div class="bg-white dark:bg-stone-ink/40 border border-stone-ink/5 dark:border-cream/5 rounded-2xl p-8 mb-12 soft-shadow">
                    <h2 class="font-serif text-2xl font-light mb-6"><?= $aksi == 'edit' ? 'Edit Produk' : 'Tambah Produk Baru' ?></h2>

                    <form action="<?= htmlspecialchars($aksi == 'edit' ? 'dasbor.php?aksi=edit&id=' . rawurlencode((string) $id_edit) : 'dasbor.php') ?>" method="post" enctype="multipart/form-data" class="space-y-5" id="product-form">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($id_edit) ?>">

                        <!-- Nama Produk -->
                        <div>
                            <label class="block text-xs font-medium text-stone-ink/60 dark:text-cream/60 mb-2">Nama Produk</label>
                            <input type="text" name="nama_produk" required
                                class="input-soft w-full px-4 py-3 rounded-xl font-sans text-sm"
                                placeholder=". . . ."
                                value="<?= htmlspecialchars($nama) ?>">
                        </div>

                        <!-- Harga & Stok -->
                        <div class="grid md:grid-cols-3 gap-5">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-stone-ink/60 dark:text-cream/60 mb-2">Harga</label>
                                <div class="flex">
                                    <span class="input-soft border-r-0 rounded-r-none px-4 py-3 text-sm flex items-center text-stone-ink/50">Rp</span>
                                    <input type="text" name="harga" required
                                        class="input-soft border-l-0 rounded-l-none w-full px-4 py-3 font-sans text-sm"
                                        placeholder="...."
                                        value="<?= htmlspecialchars($harga) ?>">
                                    <span class="input-soft border-l-0 rounded-l-none px-4 py-3 text-sm flex items-center text-stone-ink/50">.00</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-stone-ink/60 dark:text-cream/60 mb-2">Jumlah Stok</label>
                                <input type="text" name="stok" required
                                    class="input-soft w-full px-4 py-3 rounded-xl font-sans text-sm"
                                    placeholder="Jumlah Stok ..."
                                    value="<?= htmlspecialchars($stok) ?>">
                            </div>
                        </div>

                        <!-- Kategori & Foto -->
                        <div class="grid md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-medium text-stone-ink/60 dark:text-cream/60 mb-2">Kategori</label>
                                <select name="kategori" required
                                    class="input-soft w-full px-4 py-3 rounded-xl appearance-none cursor-pointer text-sm">
                                    <option value="" disabled <?= empty($id_kategori) ? 'selected' : '' ?>>Pilih Kategori ...</option>
                                    <?php
                                    $result = mysqli_query($koneksi, "SELECT * FROM tb_kategori");
                                    if ($result && mysqli_num_rows($result) > 0) {
                                        while ($list = mysqli_fetch_assoc($result)) {
                                            $selected = ($id_kategori == $list['id_kategori']) ? 'selected' : '';
                                            echo '<option value="' . $list['id_kategori'] . '" ' . $selected . '>' . htmlspecialchars($list['nama_kategori']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-stone-ink/60 dark:text-cream/60 mb-2">Foto Produk</label>
                                <div class="flex items-center gap-2">
                                    <input type="file" name="poto" id="poto" accept="image/*"
                                        class="input-soft flex-1 px-4 py-2.5 rounded-xl text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-stone-ink file:text-cream file:text-xs file:font-medium file:cursor-pointer hover:file:bg-clay transition-colors">
                                    <button type="button" id="btnCancelFile" class="hidden px-3 py-2.5 rounded-lg bg-red-500/10 text-red-500 text-xs font-medium hover:bg-red-500/20 transition-colors">✕</button>
                                </div>
                                <div class="text-xs text-stone-ink/40 dark:text-cream/40 mt-1.5">Format: JPG, PNG. Max 2MB.</div>
                                <?php if ($aksi == 'edit' && !empty($poto_lama)): ?>
                                    <img src="img/<?= htmlspecialchars($poto_lama) ?>" alt="Foto saat ini" class="mt-2 w-20 h-20 object-cover rounded-lg border border-stone-ink/10">
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div>
                            <label class="block text-xs font-medium text-stone-ink/60 dark:text-cream/60 mb-2">Deskripsi</label>
                            <input type="text" name="deskripsi"
                                class="input-soft w-full md:w-2/3 px-4 py-3 rounded-xl font-sans text-sm"
                                placeholder="Deskripsi"
                                value="<?= htmlspecialchars($deskripsi) ?>">
                        </div>

                        <!-- Tombol -->
                        <div class="flex gap-3 pt-2">
                            <button type="submit" name="<?= $aksi == 'edit' ? 'update' : 'tambah' ?>"
                                class="btn-soft bg-clay text-cream px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-clay/90">
                                <?= $aksi == 'edit' ? 'Update Data' : 'Tambah +' ?>
                            </button>
                            <?php if ($aksi == 'edit'): ?>
                                <a href="dasbor.php" class="px-6 py-2.5 rounded-lg text-sm font-medium border border-stone-ink/10 dark:border-cream/10 hover:bg-stone-ink/5 dark:hover:bg-cream/5 transition-colors">
                                    Batal
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- TABLE -->
                <section id="products">
                    <h2 class="font-serif text-3xl font-light mb-6">Daftar Produk</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-stone-ink/10 dark:border-cream/10">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-stone-ink/60 dark:text-cream/60 uppercase tracking-wider">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-stone-ink/60 dark:text-cream/60 uppercase tracking-wider">Nama</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-stone-ink/60 dark:text-cream/60 uppercase tracking-wider">Harga</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-stone-ink/60 dark:text-cream/60 uppercase tracking-wider">Stok</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-stone-ink/60 dark:text-cream/60 uppercase tracking-wider">Foto</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-stone-ink/60 dark:text-cream/60 uppercase tracking-wider">Kategori</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-stone-ink/60 dark:text-cream/60 uppercase tracking-wider">Deskripsi</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-stone-ink/60 dark:text-cream/60 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $ambil_data = mysqli_query($koneksi, "SELECT tb_produk.*, tb_kategori.nama_kategori FROM tb_produk LEFT JOIN tb_kategori ON tb_produk.id_kategori = tb_kategori.id_kategori ORDER BY tb_produk.id DESC");
                                if ($ambil_data && mysqli_num_rows($ambil_data) > 0) {
                                    while ($data = mysqli_fetch_assoc($ambil_data)) {
                                ?>
                                        <!-- GANTI class baris ini -->
                                        <tr class="table-row-soft border-b border-stone-ink/5 last:border-0" style="border-color: rgba(168, 162, 158, 0.25);">
                                            <td class="px-4 py-3 text-sm"><?= $no++; ?></td>
                                            <td class="px-4 py-3 text-sm font-medium"><?= htmlspecialchars($data['nama_produk']); ?></td>
                                            <td class="px-4 py-3 text-sm">Rp <?= number_format($data['harga'], 0, ',', '.'); ?></td>
                                            <td class="px-4 py-3 text-sm"><?= $data['stok']; ?></td>
                                            <td class="px-4 py-3 text-sm">
                                                <?php if (!empty($data['poto'])): ?>
                                                    <img src="img/<?= htmlspecialchars($data['poto']); ?>" alt="<?= htmlspecialchars($data['nama_produk']); ?>" class="w-16 h-16 object-cover rounded-lg border border-stone-ink/10">
                                                <?php else: ?>
                                                    <div class="w-16 h-16 bg-stone-ink/5 dark:bg-cream/5 rounded-lg flex items-center justify-center">
                                                        <svg class="w-8 h-8 text-stone-ink/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-clay/10 text-clay text-xs font-medium">
                                                    <?= htmlspecialchars($data['nama_kategori'] ?? 'Umum'); ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-stone-ink/70 dark:text-cream/70 max-w-[200px] truncate" title="<?= htmlspecialchars($data['deskripsi']); ?>"><?= htmlspecialchars($data['deskripsi']); ?></td>
                                            <td class="px-4 py-3 text-sm">
                                                <div class="flex gap-2">
                                                    <a href="dasbor.php?aksi=edit&id=<?= $data['id']; ?>" class="text-xs font-medium text-clay hover:text-clay/70 transition-colors">Edit</a>
                                                    <span class="text-stone-ink/20">·</span>
                                                    <a href="dasbor.php?aksi=hapus&id=<?= $data['id']; ?>" onclick="return confirm('Yakin hapus produk &quot;<?= htmlspecialchars($data['nama_produk']); ?>&quot;?')" class="text-xs font-medium text-red-500 hover:text-red-600 transition-colors">Hapus</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="8" class="px-4 py-12 text-center text-sm text-stone-ink/40 dark:text-cream/40">
                                            Belum ada produk. Tambahkan produk pertama Anda di atas.
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>
        </main>
    </div>

    <script>
        // === Theme Toggle ===
        const themeToggle = document.getElementById('theme-toggle');
        const themeDropdown = document.getElementById('theme-dropdown');
        const themeLabel = document.getElementById('theme-label');
        const themeIcon = document.getElementById('theme-icon');
        const themeOptions = document.querySelectorAll('.theme-option');

        const getStoredTheme = () => localStorage.getItem('theme') || 'dark';
        const setStoredTheme = (theme) => localStorage.setItem('theme', theme);

        const applyTheme = (theme) => {
            let actualTheme = theme;
            if (theme === 'auto') {
                actualTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            if (actualTheme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            themeLabel.textContent = theme.charAt(0).toUpperCase() + theme.slice(1);
            themeIcon.textContent = theme === 'light' ? '☀' : theme === 'dark' ? '☾' : '◐';
        };

        themeToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            themeDropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', () => {
            themeDropdown.classList.add('hidden');
        });

        themeOptions.forEach(option => {
            option.addEventListener('click', (e) => {
                e.stopPropagation();
                const theme = option.getAttribute('data-theme');
                setStoredTheme(theme);
                applyTheme(theme);
                themeDropdown.classList.add('hidden');
            });
        });

        applyTheme(getStoredTheme());

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (getStoredTheme() === 'auto') applyTheme('auto');
        });

        // === File Upload Cancel ===
        const fileInput = document.getElementById('poto');
        const btnCancel = document.getElementById('btnCancelFile');
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                btnCancel.classList.remove('hidden');
            } else {
                btnCancel.classList.add('hidden');
            }
        });
        btnCancel.addEventListener('click', function() {
            fileInput.value = '';
            btnCancel.classList.add('hidden');
        });

        // === Mobile Menu (FIXED) ===
        const menuToggle = document.getElementById('menu-toggle');
        const closeSidebar = document.getElementById('close-sidebar');
        const sidebar = document.getElementById('sidebar');

        // Buat backdrop (layar gelap) secara otomatis
        const backdrop = document.createElement('div');
        backdrop.className = 'fixed inset-0 bg-stone-ink/20 dark:bg-black/50 z-40 hidden backdrop-blur-sm md:hidden transition-opacity';
        document.body.appendChild(backdrop);

        const toggleSidebar = () => {
            const isHidden = sidebar.classList.contains('hidden');

            if (isHidden) {
                // Buka Sidebar
                sidebar.classList.remove('hidden');
                sidebar.classList.add('fixed', 'inset-y-0', 'left-0', 'z-50', 'flex', 'w-64', 'shadow-2xl');
                backdrop.classList.remove('hidden');
                document.body.style.overflow = 'hidden'; // Kunci scroll body
            } else {
                // Tutup Sidebar
                sidebar.classList.add('hidden');
                sidebar.classList.remove('fixed', 'inset-y-0', 'left-0', 'z-50', 'flex', 'w-64', 'shadow-2xl');
                backdrop.classList.add('hidden');
                document.body.style.overflow = ''; // Buka kunci scroll
            }
        };

        menuToggle.addEventListener('click', toggleSidebar);
        closeSidebar.addEventListener('click', toggleSidebar);
        backdrop.addEventListener('click', toggleSidebar); // Tutup saat klik area gelap
    </script>
</body>

</html><?php
