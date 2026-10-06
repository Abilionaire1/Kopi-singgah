<?php
session_start();
include 'koneksi.php';
/** @var mysqli $koneksi */

if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    header($_SESSION['role'] === 'admin' ? 'Location: dashboard/dasbor.php' : 'Location: profile.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Lengkapi nama, username, dan alamat email yang valid.';
    } elseif (strlen($name) > 255 || strlen($username) > 255 || strlen($email) > 255) {
        $error = 'Nama, username, atau email terlalu panjang.';
    } elseif (strlen($password) < 8) {
        $error = 'Password harus terdiri dari minimal 8 karakter.';
    } else {
        try {
            $stmt_exists = mysqli_prepare($koneksi, 'SELECT id FROM tb_user WHERE username = ? OR email = ? LIMIT 1');
            mysqli_stmt_bind_param($stmt_exists, 'ss', $username, $email);
            mysqli_stmt_execute($stmt_exists);
            $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_exists));
            mysqli_stmt_close($stmt_exists);

            if ($exists) {
                $error = 'Username atau email sudah digunakan.';
            } else {
                $stmt_register = mysqli_prepare($koneksi, 'INSERT INTO tb_user (name, username, password, email, role) VALUES (?, ?, ?, ?, ?)');
                $role = 'pelanggan';
                mysqli_stmt_bind_param($stmt_register, 'sssss', $name, $username, $password, $email, $role);
                mysqli_stmt_execute($stmt_register);
                mysqli_stmt_close($stmt_register);
                $_SESSION['success'] = 'Pendaftaran berhasil. Silakan login.';
                header('Location: login.php');
                exit();
            }
        } catch (mysqli_sql_exception $exception) {
            error_log('Customer registration failed: ' . $exception->getMessage());
            $error = 'Pendaftaran belum berhasil disimpan. Periksa kembali username dan email, lalu coba lagi.';
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar HARU はるカフェ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#17324D',
                        primaryHover: '#10263B',
                        primarySoft: '#E8F0F6',
                        secondary: '#526273',
                        bg: '#F6F5F2',
                        surface: '#FFFFFF',
                        text: '#18212B',
                        textSecondary: '#64717D',
                        border: '#DDE1E5',
                        success: '#21865B',
                        warning: '#B7791F',
                        danger: '#C74747'
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    },
                    boxShadow: {
                        soft: '0 10px 30px rgba(23, 50, 77, 0.06)'
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }
        .brand-mark {
            letter-spacing: 0.22em;
        }
    </style>
</head>
<body class="bg-bg text-text antialiased selection:bg-primarySoft">
    <div class="min-h-screen flex items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-5xl grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-[28px] border border-border bg-surface p-8 sm:p-10 shadow-soft">
                <div class="inline-flex items-center gap-2 rounded-full bg-primarySoft px-3 py-1.5 text-[10px] font-semibold tracking-[0.12em] text-primary uppercase">
                    Café Management
                </div>

                <h1 class="mt-6 text-4xl font-bold leading-tight text-text sm:text-5xl">
                    Daftar akun<br>
                    <span class="text-primary">HARU</span>
                </h1>

                <p class="mt-4 max-w-md text-sm leading-relaxed text-textSecondary">
                    Buat akun pelanggan untuk memesan, melacak pesanan, dan mengelola profil Anda dengan lebih cepat dan mudah.
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-border bg-bg p-4">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primarySoft text-primary">
                            <i class="fa-solid fa-bag-shopping text-sm"></i>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-text">Pesanan</p>
                        <p class="mt-1 text-[11px] text-textSecondary">Lacak dan checkout lebih cepat.</p>
                    </div>

                    <div class="rounded-2xl border border-border bg-bg p-4">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primarySoft text-primary">
                            <i class="fa-solid fa-user text-sm"></i>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-text">Profil</p>
                        <p class="mt-1 text-[11px] text-textSecondary">Kelola data diri dan preferensi.</p>
                    </div>

                    <div class="rounded-2xl border border-border bg-bg p-4">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primarySoft text-primary">
                            <i class="fa-solid fa-chart-line text-sm"></i>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-text">Dashboard</p>
                        <p class="mt-1 text-[11px] text-textSecondary">Akses halaman akun sesuai role.</p>
                    </div>
                </div>
            </div>

            <div id="register" class="rounded-[24px] border border-border bg-surface p-6 shadow-soft sm:p-8">
                <div class="mb-6 flex items-center justify-between gap-3">
                    <a href="index.php" class="flex items-center gap-2 text-text">
                        <span class="font-semibold text-xl tracking-[0.18em] brand-mark">HARU</span>
                    </a>
                    <span class="rounded-full bg-primarySoft px-2.5 py-1 text-[10px] font-semibold tracking-[0.08em] text-primary uppercase">
                        Pelanggan
                    </span>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="mb-4 rounded-xl border border-[#F2C7C7] bg-[#FFF5F5] px-4 py-3 text-xs font-medium text-[#B42318]" role="alert">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php" class="space-y-4">
                    <div>
                        <label for="name" class="mb-1.5 block text-[12px] font-medium text-textSecondary">Nama Lengkap</label>
                        <div class="relative">
                            <i class="fa-solid fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-textSecondary text-sm"></i>
                            <input id="name" type="text" name="name" maxlength="255" value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="h-[44px] w-full rounded-xl border border-border bg-bg pl-11 pr-4 text-sm text-text placeholder:text-textSecondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" placeholder="Nama lengkap" required>
                        </div>
                    </div>

                    <div>
                        <label for="username" class="mb-1.5 block text-[12px] font-medium text-textSecondary">Username</label>
                        <div class="relative">
                            <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-textSecondary text-sm"></i>
                            <input id="username" type="text" name="username" maxlength="255" value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="h-[44px] w-full rounded-xl border border-border bg-bg pl-11 pr-4 text-sm text-text placeholder:text-textSecondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" placeholder="Username" required>
                        </div>
                    </div>

                    <div>
                        <label for="email" class="mb-1.5 block text-[12px] font-medium text-textSecondary">Email</label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-textSecondary text-sm"></i>
                            <input id="email" type="email" name="email" maxlength="255" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="h-[44px] w-full rounded-xl border border-border bg-bg pl-11 pr-4 text-sm text-text placeholder:text-textSecondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" placeholder="Email" required>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-[12px] font-medium text-textSecondary">Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-textSecondary text-sm"></i>
                            <input id="password" type="password" name="password" minlength="8" class="h-[44px] w-full rounded-xl border border-border bg-bg pl-11 pr-4 text-sm text-text placeholder:text-textSecondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" placeholder="Minimal 8 karakter" required>
                        </div>
                    </div>

                    <button class="flex h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-primary text-sm font-semibold text-white transition hover:bg-primaryHover" type="submit" name="register">
                        Daftar Sekarang
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </form>

                <p class="mt-6 text-center text-xs text-textSecondary">
                    Sudah punya akun?
                    <a href="login.php" class="font-semibold text-primary hover:text-primaryHover">Login di sini</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
