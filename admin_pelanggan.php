<?php
session_start();
include "koneksi.php";
/** @var mysqli $koneksi */
// -------------------------------------------------------------------
// SISTEM PROTEKSI HAK AKSES ADMIN
// -------------------------------------------------------------------
$id_user = $_SESSION['user_id'] ?? $_SESSION['id_user'] ?? $_SESSION['id'] ?? null;

// 1. Cek Apakah User Sudah Login
if (!$id_user) {
    echo "<script>
            alert('Silakan login terlebih dahulu!');
            window.location.href = 'login.php';
          </script>";
    exit;
}

// Validasi role berdasarkan akun di database, bukan hanya nilai session.
$stmt_role = mysqli_prepare($koneksi, "SELECT role FROM tb_user WHERE id = ?");
mysqli_stmt_bind_param($stmt_role, "i", $id_user);
mysqli_stmt_execute($stmt_role);
$role_user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_role))['role'] ?? '';

if ($role_user !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak.");
}
// -------------------------------------------------------------------

$csrf_token = $_SESSION['csrf_admin'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_admin'] = $csrf_token;
$pesan = "";
$tipe_pesan = "";

if (isset($_SESSION['reset_password_notice'])) {
    $pesan = $_SESSION['reset_password_notice'];
    unset($_SESSION['reset_password_notice']);
    $tipe_pesan = "success";
}

if (isset($_POST['aksi']) && $_POST['aksi'] === 'reset') {
    $id_user_reset = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($csrf_token, $token) || !$id_user_reset) {
        http_response_code(400);
        $pesan = "Permintaan reset password tidak valid.";
        $tipe_pesan = "danger";
    } else {
        $password_baru = 'user123';
        $stmt_reset = mysqli_prepare($koneksi, "UPDATE tb_user SET password = ? WHERE id = ? AND role = 'pelanggan'");
        mysqli_stmt_bind_param($stmt_reset, "si", $password_baru, $id_user_reset);

        if (mysqli_stmt_execute($stmt_reset) && mysqli_stmt_affected_rows($stmt_reset) === 1) {
            $_SESSION['reset_password_notice'] = "Password pelanggan berhasil direset menjadi user123.";
            header("Location: admin_pelanggan.php");
            exit;
        }

        $pesan = mysqli_stmt_error($stmt_reset) ?: "Pelanggan tidak ditemukan atau password tidak berubah.";
        $tipe_pesan = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Pelanggan - Admin | Kopi Singgah</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">

    <!-- Google Fonts: Fraunces & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@300;400;500;600;700&family=Caveat:wght@600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
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
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-cream text-stone-ink font-sans antialiased overflow-x-hidden">

<!-- Jika di web Anda sudah ada header/sidebar terpisah, Anda cukup include file tersebut di sini -->
<?php if (file_exists("header.php")) include "header.php"; ?>

<div class="max-w-[1400px] mx-auto px-6 lg:px-12 py-8">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
        <div>
            <span class="text-clay text-[10px] font-bold tracking-widest uppercase mb-2 block">Admin Panel</span>
            <h1 class="font-serif text-3xl md:text-4xl text-stone-ink">Data Pelanggan</h1>
            <p class="text-stone-ink/60 text-sm mt-2">Kelola informasi pengguna dan kontrol reset password akun</p>
        </div>
        <a href="dashboard/dasbor.php" class="inline-flex items-center gap-2 border border-stone-ink/20 text-stone-ink text-xs font-semibold px-5 py-2.5 rounded-full hover:bg-stone-ink hover:text-white transition whitespace-nowrap">
            <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali
        </a>
    </div>

    <!-- Alert Notifikasi -->
    <?php if (!empty($pesan)): ?>
        <div class="mb-6 rounded-2xl p-4 flex items-center gap-3 shadow-editorial border <?= $tipe_pesan == 'success' ? 'bg-sage/10 border-sage/30 text-sage' : 'bg-red-50 border-red-200 text-red-700'; ?>">
            <i class="fa-solid <?= $tipe_pesan == 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?> text-lg"></i>
            <p class="text-sm flex-1"><?= htmlspecialchars($pesan); ?></p>
            <button onclick="this.parentElement.remove()" class="opacity-60 hover:opacity-100 transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Card Tabel Data -->
    <div class="bg-white rounded-3xl shadow-editorial border border-sand/30 overflow-hidden">
        <!-- Card Header -->
        <div class="px-6 py-5 border-b border-sand/30 flex items-center justify-between">
            <h2 class="font-serif text-lg font-semibold text-stone-ink flex items-center gap-2">
                <i class="fa-solid fa-users text-clay text-sm"></i>
                Daftar Pelanggan Terdaftar
            </h2>
            <span class="text-[10px] font-bold tracking-widest uppercase text-stone-ink/40 bg-warm px-3 py-1.5 rounded-full">
                <?php
                $jumlah_query = mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM tb_user WHERE role = 'pelanggan'");
                echo (int) mysqli_fetch_assoc($jumlah_query)['jumlah'];
                ?> Pelanggan
            </span>
        </div>

        <!-- Tabel -->
        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-warm/50 text-left">
                        <th class="px-6 py-4 text-[10px] font-bold tracking-widest uppercase text-stone-ink/50 w-12">#</th>
                        <th class="px-4 py-4 text-[10px] font-bold tracking-widest uppercase text-stone-ink/50">Pelanggan</th>
                        <th class="px-4 py-4 text-[10px] font-bold tracking-widest uppercase text-stone-ink/50">Username</th>
                        <th class="px-4 py-4 text-[10px] font-bold tracking-widest uppercase text-stone-ink/50">Email</th>
                        <th class="px-4 py-4 text-[10px] font-bold tracking-widest uppercase text-stone-ink/50">No. HP</th>
                        <th class="px-4 py-4 text-[10px] font-bold tracking-widest uppercase text-stone-ink/50">Alamat</th>
                        <th class="px-6 py-4 text-[10px] font-bold tracking-widest uppercase text-stone-ink/50 text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sand/20">
                    <?php
                    // Ambil data pelanggan dari tb_user
                    $result = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE role = 'pelanggan' ORDER BY id DESC");
                    $no = 1;

                    if (mysqli_num_rows($result) > 0) {
                        while ($user = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr class="hover:bg-warm/30 transition">
                                <td class="px-6 py-4 font-serif font-semibold text-stone-ink/40"><?= $no++; ?></td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-clay/10 text-clay flex items-center justify-center font-serif font-bold text-sm shrink-0">
                                            <?= strtoupper(substr($user['name'] ?? $user['username'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-stone-ink"><?= htmlspecialchars($user['name'] ?? '-'); ?></div>
                                            <span class="text-[9px] font-bold tracking-widest uppercase text-clay bg-clay/10 px-2 py-0.5 rounded-full">Pelanggan</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-stone-ink/60">@<?= htmlspecialchars($user['username']); ?></td>
                                <td class="px-4 py-4 text-stone-ink/80"><?= htmlspecialchars($user['email']); ?></td>
                                <td class="px-4 py-4">
                                    <?php if (!empty($user['hp'])): ?>
                                        <span class="text-stone-ink/80"><i class="fa-solid fa-phone text-stone-ink/30 mr-1.5 text-[10px]"></i><?= htmlspecialchars($user['hp']); ?></span>
                                    <?php else: ?>
                                        <span class="text-stone-ink/40 italic text-xs">Belum diisi</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4">
                                    <?php if (!empty($user['alamat'])): ?>
                                        <span class="text-stone-ink/80"><i class="fa-solid fa-location-dot text-stone-ink/30 mr-1.5 text-[10px]"></i><?= htmlspecialchars($user['alamat']); ?></span>
                                    <?php else: ?>
                                        <span class="text-stone-ink/40 italic text-xs">Belum diisi</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <form method="POST" onsubmit="return confirm('Reset password pelanggan ini menjadi user123?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">
                                        <input type="hidden" name="id" value="<?= (int) $user['id']; ?>">
                                        <input type="hidden" name="aksi" value="reset">
                                        <button type="submit" class="inline-flex items-center gap-1.5 text-[10px] font-bold text-red-700 border border-red-200 px-4 py-2 rounded-full hover:bg-red-700 hover:text-white transition shadow-sm">
                                            <i class="fa-solid fa-key text-[9px]"></i> Reset Pass
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="7" class="px-6 py-12 text-center text-stone-ink/50">
                            <i class="fa-solid fa-users text-3xl mb-3 block text-sand"></i>
                            Belum ada data pelanggan terdaftar.
                        </td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer Note -->
    <p class="text-center text-stone-ink/40 text-xs mt-8 font-handwriting text-lg">&copy; 2026 Kopi Singgah. All rights reserved.</p>
</div>

<?php if (file_exists("footer.php")) include "footer.php"; ?>

</body>
</html>