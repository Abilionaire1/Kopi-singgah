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
    <title>Daftar Akun - Kopi Singgah</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@300;400;500;600;700&family=Caveat:wght@600&display=swap" rel="stylesheet">
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
                        'soft': '0 10px 40px rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-cream font-sans text-stone-ink antialiased selection:bg-warm">
    <div class="min-h-screen flex items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-5xl grid gap-10 lg:grid-cols-[1fr_430px] lg:gap-20">
            <div class="rounded-[2.5rem] border border-sand/30 bg-white p-8 shadow-soft sm:p-10">
                <div class="inline-flex items-center gap-2 rounded-full bg-warm px-3 py-1.5 text-[10px] font-semibold tracking-[0.12em] text-clay uppercase">
                    Tempat untuk singgah
                </div>

                <h1 class="mt-6 font-serif text-4xl font-semibold leading-tight text-stone-ink sm:text-5xl">
                    Daftar akun<br>
                    <span class="text-clay">Kopi Singgah</span>
                </h1>

                <p class="mt-4 max-w-md text-sm leading-relaxed text-stone-ink/60">
                    Buat akun untuk memesan kopi favorit, melacak pesanan, dan menikmati pengalaman singgah yang lebih mudah.
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-sand/50 bg-warm/30 p-4">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-warm text-clay">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-stone-ink">Pesanan</p>
                        <p class="mt-1 text-[11px] text-stone-ink/60">Lacak dan checkout lebih cepat.</p>
                    </div>

                    <div class="rounded-2xl border border-sand/50 bg-warm/30 p-4">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-warm text-clay">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-stone-ink">Profil</p>
                        <p class="mt-1 text-[11px] text-stone-ink/60">Kelola data diri dan preferensi.</p>
                    </div>

                    <div class="rounded-2xl border border-sand/50 bg-warm/30 p-4">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-warm text-clay">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-stone-ink">Kopi Favorit</p>
                        <p class="mt-1 text-[11px] text-stone-ink/60">Temukan rasa favoritmu.</p>
                    </div>
                </div>
            </div>

            <div id="register" class="rounded-[2.5rem] border border-sand/30 bg-white p-7 shadow-soft sm:p-10">
                <div class="mb-6 flex items-center justify-between gap-3">
                    <a href="index.php" class="flex items-center gap-2 text-stone-ink">
                    <span class="font-serif text-xl font-semibold tracking-tight">Kopi Singgah.</span>
                    </a>
                    <span class="rounded-full bg-warm px-2.5 py-1 text-[10px] font-semibold tracking-[0.08em] text-clay uppercase">
                        Daftar Pelanggan
                    </span>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="mb-4 rounded-xl border border-[#F2C7C7] bg-[#FFF5F5] px-4 py-3 text-xs font-medium text-[#B42318]" role="alert">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php" class="space-y-4">
                    <div>
                        <label for="name" class="mb-1.5 block text-[12px] font-bold tracking-widest text-stone-ink/60 uppercase">Nama Lengkap</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-stone-ink/40">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                            </div>
                            <input id="name" type="text" name="name" maxlength="255" value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-full bg-warm/30 border border-sand/50 rounded-2xl px-4 py-3.5 pl-11 text-sm text-stone-ink focus:outline-none focus:border-clay focus:ring-1 focus:ring-clay transition placeholder:text-stone-ink/40" placeholder="Nama lengkap" required>
                        </div>
                    </div>

                    <div>
                        <label for="username" class="mb-1.5 block text-[12px] font-bold tracking-widest text-stone-ink/60 uppercase">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-stone-ink/40">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <input id="username" type="text" name="username" maxlength="255" value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-full bg-warm/30 border border-sand/50 rounded-2xl px-4 py-3.5 pl-11 text-sm text-stone-ink focus:outline-none focus:border-clay focus:ring-1 focus:ring-clay transition placeholder:text-stone-ink/40" placeholder="Username" required>
                        </div>
                    </div>

                    <div>
                        <label for="email" class="mb-1.5 block text-[12px] font-bold tracking-widest text-stone-ink/60 uppercase">Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-stone-ink/40">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <input id="email" type="email" name="email" maxlength="255" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-full bg-warm/30 border border-sand/50 rounded-2xl px-4 py-3.5 pl-11 text-sm text-stone-ink focus:outline-none focus:border-clay focus:ring-1 focus:ring-clay transition placeholder:text-stone-ink/40" placeholder="Email" required>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-[12px] font-bold tracking-widest text-stone-ink/60 uppercase">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-stone-ink/40">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <input id="password" type="password" name="password" minlength="8" class="w-full bg-warm/30 border border-sand/50 rounded-2xl px-4 py-3.5 pl-11 text-sm text-stone-ink focus:outline-none focus:border-clay focus:ring-1 focus:ring-clay transition placeholder:text-stone-ink/40" placeholder="Minimal 8 karakter" required>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button class="w-full bg-stone-ink text-white py-3.5 rounded-2xl font-semibold hover:bg-clay transition-colors duration-200 shadow-sm flex items-center justify-center gap-2" type="submit" name="register">
                            Daftar Sekarang
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </form>

                <div class="text-center mt-6 pt-6 border-t border-sand/30">
                    <p class="text-xs text-stone-ink/60">
                        Sudah punya akun?
                        <a href="login.php" class="text-clay font-semibold hover:underline">Login di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
