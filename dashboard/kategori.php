<?php
include_once __DIR__ . '/../koneksi.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
/** @var mysqli $koneksi */
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('location: ../login.php');
    exit;
}

$pesan = '';
$tipe_pesan = '';

$aksi = $_GET['aksi'] ?? '';
$id_edit = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$nama_kategori_edit = '';

// === PROSES AMBIL DATA UNTUK MODE EDIT ===
if ($aksi === 'edit' && $id_edit > 0) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM tb_kategori WHERE id_kategori = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_edit);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($res)) {
        $nama_kategori_edit = $row['nama_kategori'];
    }
    mysqli_stmt_close($stmt);
}

// === PROSES TAMBAH / UPDATE KATEGORI ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_kategori'])) {
    $nama_kategori = trim($_POST['nama_kategori'] ?? '');
    $id_target = isset($_POST['id_kategori']) ? (int)$_POST['id_kategori'] : 0;

    if (empty($nama_kategori)) {
        $pesan = "Nama kategori tidak boleh kosong!";
        $tipe_pesan = "error";
    } else {
        if ($id_target > 0) {
            // Update
            $stmt = mysqli_prepare($koneksi, "UPDATE tb_kategori SET nama_kategori = ? WHERE id_kategori = ?");
            mysqli_stmt_bind_param($stmt, "si", $nama_kategori, $id_target);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['kategori_notice'] = "Kategori berhasil diperbarui!";
                $_SESSION['kategori_notice_type'] = "success";
                header("Location: kategori.php");
                exit;
            } else {
                $pesan = "Gagal memperbarui kategori: " . mysqli_error($koneksi);
                $tipe_pesan = "error";
            }
            mysqli_stmt_close($stmt);
        } else {
            // Insert baru
            $stmt = mysqli_prepare($koneksi, "INSERT INTO tb_kategori (nama_kategori) VALUES (?)");
            mysqli_stmt_bind_param($stmt, "s", $nama_kategori);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['kategori_notice'] = "Kategori '{$nama_kategori}' berhasil ditambahkan!";
                $_SESSION['kategori_notice_type'] = "success";
                header("Location: kategori.php");
                exit;
            } else {
                $pesan = "Gagal menambahkan kategori: " . mysqli_error($koneksi);
                $tipe_pesan = "error";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// === PROSES HAPUS KATEGORI ===
if ($aksi === 'hapus' && $id_edit > 0) {
    $mode_hapus = $_GET['mode'] ?? 'kategori_saja'; // 'kategori_saja' atau 'beserta_produk'

    mysqli_begin_transaction($koneksi);
    try {
        if ($mode_hapus === 'beserta_produk') {
            // Hapus semua produk di kategori ini terlebih dahulu
            $del_prod = mysqli_query($koneksi, "DELETE FROM tb_produk WHERE id_kategori = $id_edit");
            if (!$del_prod) throw new Exception(mysqli_error($koneksi));
        } else {
            // Ubah produk menjadi kategori NULL (Uncategorized)
            $update_prod = mysqli_query($koneksi, "UPDATE tb_produk SET id_kategori = NULL WHERE id_kategori = $id_edit");
            if (!$update_prod) throw new Exception(mysqli_error($koneksi));
        }

        // Hapus kategori dari tb_kategori
        $del_kat = mysqli_query($koneksi, "DELETE FROM tb_kategori WHERE id_kategori = $id_edit");
        if (!$del_kat) throw new Exception(mysqli_error($koneksi));

        mysqli_commit($koneksi);
        $_SESSION['kategori_notice'] = $mode_hapus === 'beserta_produk' 
            ? "Kategori dan seluruh produk di dalamnya berhasil dihapus!" 
            : "Kategori berhasil dihapus (produk dialihkan menjadi Tanpa Kategori).";
        $_SESSION['kategori_notice_type'] = "success";
        header("Location: kategori.php");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $pesan = "Gagal menghapus kategori: " . $e->getMessage();
        $tipe_pesan = "error";
    }
}

if (isset($_SESSION['kategori_notice'])) {
    $pesan = $_SESSION['kategori_notice'];
    $tipe_pesan = $_SESSION['kategori_notice_type'] ?? 'success';
    unset($_SESSION['kategori_notice'], $_SESSION['kategori_notice_type']);
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kelola Kategori · MyDashboard</title>
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..900;1,9..144,300..900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        document.documentElement.classList.remove('dark');
        try { localStorage.removeItem('theme'); } catch(e) {}
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
    </style>
</head>

<body class="bg-cream text-stone-ink font-sans antialiased dark:bg-stone-ink dark:text-cream transition-colors duration-500">
    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <aside id="sidebar" class="hidden md:flex w-64 flex-col border-r border-stone-ink/5 dark:border-cream/5 bg-cream dark:bg-stone-ink sticky top-0 h-screen overflow-y-auto flex-shrink-0">
            <div class="px-6 py-5 border-b border-stone-ink/5 dark:border-cream/5">
                <a href="dasbor.php" class="font-serif text-xl font-light tracking-tight">
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
                        <li>
                            <a href="dasbor.php" class="nav-item-soft flex items-center gap-3 py-2.5 px-3 font-sans text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Admin Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="../admin_transaksi.php" class="nav-item-soft flex items-center gap-3 py-2.5 px-3 font-sans text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Transaksi
                            </a>
                        </li>
                        <li>
                            <a href="dasbor.php#products" class="nav-item-soft flex items-center gap-3 py-2.5 px-3 font-sans text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Produk
                            </a>
                        </li>
                        <li>
                            <a href="kategori.php" class="nav-item-soft active flex items-center gap-3 py-2.5 px-3 font-sans text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                Kategori
                            </a>
                        </li>
                        <li>
                            <a href="../admin_pelanggan.php" class="nav-item-soft flex items-center gap-3 py-2.5 px-3 font-sans text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                Pelanggan
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="px-4 py-4 border-t border-stone-ink/5 dark:border-cream/5 space-y-1">
                <a href="../index.php" class="nav-item-soft flex items-center gap-3 py-2.5 px-3 text-sm font-medium text-clay">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Lihat Web Toko
                </a>
                <a href="../logout.php" class="nav-item-soft flex items-center gap-3 py-2.5 px-3 text-sm font-medium text-red-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Keluar
                </a>
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
                        <h1 class="font-serif text-3xl font-light">Kelola Kategori Menu</h1>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="dasbor.php" class="px-4 py-2 rounded-lg border border-stone-ink/10 dark:border-cream/10 text-xs font-medium hover:bg-stone-ink/5 dark:hover:bg-cream/5 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali ke Produk
                        </a>
                    </div>
                </div>
            </header>

            <div class="p-4 md:p-8 max-w-5xl">

                <!-- Alert Notifikasi -->
                <?php if (!empty($pesan)): ?>
                    <div class="mb-8 rounded-xl p-4 flex items-center justify-between border <?= $tipe_pesan === 'success' ? 'bg-sage/10 border-sage/30 text-sage dark:text-sage' : 'bg-red-500/10 border-red-500/20 text-red-600 dark:text-red-400' ?>">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <?php if ($tipe_pesan === 'success'): ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                <?php else: ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <?php endif; ?>
                            </svg>
                            <span class="text-sm font-medium"><?= htmlspecialchars($pesan) ?></span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-xs opacity-60 hover:opacity-100">✕</button>
                    </div>
                <?php endif; ?>

                <!-- Form Tambah / Edit Kategori -->
                <div class="bg-white dark:bg-stone-ink/40 border border-stone-ink/5 dark:border-cream/5 rounded-2xl p-6 md:p-8 mb-10 soft-shadow">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="font-serif text-2xl font-light"><?= $aksi === 'edit' ? 'Edit Kategori' : 'Tambah Kategori Baru' ?></h2>
                            <p class="text-xs text-stone-ink/60 dark:text-cream/60 mt-1">
                                <?= $aksi === 'edit' ? 'Ubah nama kategori menu di bawah ini.' : 'Tambahkan kategori baru untuk mengelompokkan menu kopi, pastry, dessert, dsb.' ?>
                            </p>
                        </div>
                        <?php if ($aksi === 'edit'): ?>
                            <a href="kategori.php" class="text-xs text-clay hover:underline">Batal Edit</a>
                        <?php endif; ?>
                    </div>

                    <form action="kategori.php" method="post" class="space-y-4">
                        <input type="hidden" name="id_kategori" value="<?= $id_edit ?>">

                        <div>
                            <label class="block text-xs font-medium text-stone-ink/60 dark:text-cream/60 mb-2">Nama Kategori</label>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <input type="text" name="nama_kategori" required
                                    class="input-soft flex-1 px-4 py-3 rounded-xl font-sans text-sm"
                                    placeholder="Contoh: Pastry & Bakery, Artisanal Coffee, Dessert..."
                                    value="<?= htmlspecialchars($nama_kategori_edit) ?>">
                                <button type="submit" name="simpan_kategori"
                                    class="btn-soft bg-clay text-cream px-6 py-3 rounded-xl text-sm font-medium hover:bg-clay/90 whitespace-nowrap">
                                    <?= $aksi === 'edit' ? 'Simpan Perubahan' : 'Tambah Kategori +' ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Daftar Kategori Table -->
                <div class="bg-white dark:bg-stone-ink/40 border border-stone-ink/5 dark:border-cream/5 rounded-2xl p-6 md:p-8 soft-shadow">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="font-serif text-2xl font-light">Daftar Kategori Saat Ini</h2>
                            <p class="text-xs text-stone-ink/60 dark:text-cream/60 mt-1">Kategori aktif yang ditampilkan di halaman menu pelanggan</p>
                        </div>
                        <?php
                        $count_kat = mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM tb_kategori");
                        $total_kategori = mysqli_fetch_assoc($count_kat)['jml'] ?? 0;
                        ?>
                        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-clay/10 text-clay">
                            Total: <?= $total_kategori ?> Kategori
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-stone-ink/10 dark:border-cream/10 text-left text-xs font-medium text-stone-ink/60 dark:text-cream/60 uppercase tracking-wider">
                                    <th class="px-4 py-3 w-16">ID</th>
                                    <th class="px-4 py-3">Nama Kategori</th>
                                    <th class="px-4 py-3 text-center">Jumlah Produk</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-ink/5 dark:divide-cream/5">
                                <?php
                                $query_list = mysqli_query($koneksi, "
                                    SELECT k.id_kategori, k.nama_kategori, COUNT(p.id) AS total_produk
                                    FROM tb_kategori k
                                    LEFT JOIN tb_produk p ON k.id_kategori = p.id_kategori
                                    GROUP BY k.id_kategori, k.nama_kategori
                                    ORDER BY k.id_kategori ASC
                                ");

                                if ($query_list && mysqli_num_rows($query_list) > 0) {
                                    while ($item = mysqli_fetch_assoc($query_list)) {
                                        $id_k = (int)$item['id_kategori'];
                                        $nama_k = $item['nama_kategori'];
                                        $jml_p = (int)$item['total_produk'];
                                ?>
                                        <tr class="table-row-soft">
                                            <td class="px-4 py-3.5 text-xs text-stone-ink/50 dark:text-cream/50">#<?= $id_k ?></td>
                                            <td class="px-4 py-3.5 text-sm font-medium">
                                                <?= htmlspecialchars($nama_k) ?>
                                            </td>
                                            <td class="px-4 py-3.5 text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $jml_p > 0 ? 'bg-sage/10 text-sage dark:text-sage' : 'bg-stone-ink/5 dark:bg-cream/5 text-stone-ink/40 dark:text-cream/40' ?>">
                                                    <?= $jml_p ?> Menu
                                                </span>
                                            </td>
                                            <td class="px-4 py-3.5 text-right">
                                                <div class="flex items-center justify-end gap-2 text-xs">
                                                    <a href="kategori.php?aksi=edit&id=<?= $id_k ?>" class="text-clay hover:underline font-medium px-2 py-1">
                                                        Edit
                                                    </a>
                                                    <span class="text-stone-ink/20">·</span>
                                                    <button type="button" onclick="bukaModalHapus(<?= $id_k ?>, '<?= addslashes(htmlspecialchars($nama_k)) ?>', <?= $jml_p ?>)" class="text-red-500 hover:text-red-700 font-medium px-2 py-1">
                                                        Hapus
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-stone-ink/40 dark:text-cream/40">
                                            Belum ada kategori. Silakan tambahkan kategori di atas.
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- MODAL KONFIRMASI HAPUS CERDAS -->
    <div id="modalHapus" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-ink/40 dark:bg-black/60 backdrop-blur-sm hidden">
        <div class="bg-white dark:bg-stone-ink border border-stone-ink/10 dark:border-cream/10 rounded-2xl max-w-md w-full p-6 soft-shadow text-stone-ink dark:text-cream">
            <div class="flex items-center gap-3 mb-4 text-red-500">
                <div class="w-10 h-10 rounded-full bg-red-500/10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-serif text-lg font-medium text-stone-ink dark:text-cream">Hapus Kategori</h3>
                    <p class="text-xs text-stone-ink/60 dark:text-cream/60" id="namaKategoriTarget"></p>
                </div>
            </div>

            <div class="mb-6 space-y-3" id="opsiContainer">
                <p class="text-sm text-stone-ink/80 dark:text-cream/80" id="pesanKategori"></p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2 justify-end pt-2 border-t border-stone-ink/10 dark:border-cream/10">
                <button type="button" onclick="tutupModalHapus()" class="px-4 py-2.5 rounded-xl text-xs font-medium border border-stone-ink/10 dark:border-cream/10 hover:bg-stone-ink/5 dark:hover:bg-cream/5 transition-colors">
                    Batal
                </button>
                <div id="tombolAksiHapus" class="flex flex-col sm:flex-row gap-2"></div>
            </div>
        </div>
    </div>

    <script>
        // === Modal Konfirmasi Hapus Cerdas ===
        function bukaModalHapus(id, nama, jmlProduk) {
            const modal = document.getElementById('modalHapus');
            const namaEl = document.getElementById('namaKategoriTarget');
            const pesanEl = document.getElementById('pesanKategori');
            const tombolContainer = document.getElementById('tombolAksiHapus');

            namaEl.textContent = 'Kategori: ' + nama;

            if (jmlProduk > 0) {
                pesanEl.innerHTML = `Kategori ini memiliki <strong>${jmlProduk} produk</strong> di dalamnya. Silakan pilih cara penghapusan:`;
                tombolContainer.innerHTML = `
                    <a href="kategori.php?aksi=hapus&id=${id}&mode=kategori_saja" class="px-3.5 py-2.5 rounded-xl text-xs font-medium border border-stone-ink/20 hover:bg-stone-ink/10 text-stone-ink dark:text-cream text-center">
                        Hapus Kategori Saja (Produk Tetap Ada)
                    </a>
                    <a href="kategori.php?aksi=hapus&id=${id}&mode=beserta_produk" class="px-3.5 py-2.5 rounded-xl text-xs font-medium bg-red-500 text-white hover:bg-red-600 text-center">
                        Hapus Kategori & ${jmlProduk} Produknya
                    </a>
                `;
            } else {
                pesanEl.innerHTML = `Kategori ini kosong (0 produk). Apakah Anda yakin ingin menghapusnya?`;
                tombolContainer.innerHTML = `
                    <a href="kategori.php?aksi=hapus&id=${id}&mode=kategori_saja" class="px-4 py-2.5 rounded-xl text-xs font-medium bg-red-500 text-white hover:bg-red-600 text-center">
                        Ya, Hapus Kategori
                    </a>
                `;
            }

            modal.classList.remove('hidden');
        }

        function tutupModalHapus() {
            document.getElementById('modalHapus').classList.add('hidden');
        }

        localStorage.removeItem('theme');
        document.documentElement.classList.remove('dark');

        // === Mobile Menu ===
        const menuToggle = document.getElementById('menu-toggle');
        const closeSidebar = document.getElementById('close-sidebar');
        const sidebar = document.getElementById('sidebar');

        const backdrop = document.createElement('div');
        backdrop.className = 'fixed inset-0 bg-stone-ink/20 dark:bg-black/50 z-40 hidden backdrop-blur-sm md:hidden transition-opacity';
        document.body.appendChild(backdrop);

        const toggleSidebar = () => {
            const isHidden = sidebar.classList.contains('hidden');
            if (isHidden) {
                sidebar.classList.remove('hidden');
                sidebar.classList.add('fixed', 'inset-y-0', 'left-0', 'z-50', 'flex', 'w-64', 'shadow-2xl');
                backdrop.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                sidebar.classList.add('hidden');
                sidebar.classList.remove('fixed', 'inset-y-0', 'left-0', 'z-50', 'flex', 'w-64', 'shadow-2xl');
                backdrop.classList.add('hidden');
                document.body.style.overflow = '';
            }
        };

        menuToggle.addEventListener('click', toggleSidebar);
        closeSidebar.addEventListener('click', toggleSidebar);
        backdrop.addEventListener('click', toggleSidebar);
    </script>
</body>

</html>
