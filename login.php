<?php
session_start();
include 'koneksi.php';
/** @var mysqli $koneksi */

// Jika user sudah dalam keadaan login, cegah masuk ke halaman login lagi
if (isset($_SESSION['user_id']) || isset($_SESSION['id_user'])) {
    $role = strtolower($_SESSION['role'] ?? '');
    if ($role === 'admin') {
        header("Location: dashboard/dasbor.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$pesan = "";
$status = "";
$redirect_url = "";

// PROSES LOGIN
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Cek akun berdasarkan username atau email
    $stmt_login = mysqli_prepare($koneksi, 'SELECT * FROM tb_user WHERE username = ? OR email = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt_login, 'ss', $username, $username);
    mysqli_stmt_execute($stmt_login);
    $query = mysqli_stmt_get_result($stmt_login);

    $user = mysqli_fetch_assoc($query);
    mysqli_stmt_close($stmt_login);
    if ($user) {

        // Verifikasi password teks biasa.
        if ($password === $user['password']) {
            $role = strtolower($user['role']);

            // 1. Simpan Seluruh Kunci Session secara Lengkap
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['id_user']  = $user['id'];
            $_SESSION['id']       = $user['id'];
            $_SESSION['name']     = $user['name'] ?? $user['username'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $role;

            $pesan  = "Login berhasil! Selamat datang, " . ($user['name'] ?? $user['username']) . ".";
            $status = "success";

            // 2. Tentukan Tujuan Redirect Berdasarkan Role
            if ($role === 'admin') {
                $redirect_url = "dashboard/dasbor.php";
            } else {
                $redirect_url = "index.php";
            }
        } else {
            $pesan  = "Password yang Anda masukkan salah!";
            $status = "error";
        }
    } else {
        $pesan  = "Username atau Email tidak ditemukan!";
        $status = "error";
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Kopi Singgah</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@300;400;500;600;700&family=Caveat:wght@600&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 & Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        .swal2-popup { border-radius: 1.5rem !important; font-family: 'Inter', sans-serif !important; }
    </style>
</head>
<body class="bg-cream text-stone-ink font-sans antialiased min-h-screen flex flex-col justify-between">

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
            
        </div>
    </nav>

    <!-- MAIN LOGIN CARD -->
    <main class="flex-grow flex items-center justify-center p-4 my-8">
        <div class="w-full max-w-md bg-white rounded-[2.5rem] shadow-soft p-8 md:p-10 border border-sand/30">
            
            <!-- HEADER FORM -->
            <div class="text-center mb-8">
                <span class="text-clay text-[10px] font-bold tracking-widest uppercase mb-2 block">Selamat Datang Kembali</span>
                <h1 class="font-serif text-3xl font-semibold text-stone-ink">Masuk Akun</h1>
                <p class="text-xs text-stone-ink/60 mt-2">Nikmati secangkir kehangatan favoritmu di Kopi Singgah.</p>
            </div>

            <!-- FORM LOGIN -->
            <form method="POST" action="login.php" class="space-y-5">
                
                <!-- INPUT USERNAME / EMAIL -->
                <div>
                    <label class="block text-xs font-bold text-stone-ink/60 uppercase tracking-widest mb-2">Username / Email</label>
                    <div class="relative">
                        <input type="text" name="username" required placeholder="Masukkan username atau email"
                               class="w-full bg-warm/30 border border-sand/50 rounded-2xl px-4 py-3.5 pl-11 text-sm text-stone-ink focus:outline-none focus:border-clay focus:ring-1 focus:ring-clay transition">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-stone-ink/40">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- INPUT PASSWORD -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-stone-ink/60 uppercase tracking-widest">Password</label>
                    </div>
                    <div class="relative">
                        <input type="password" name="password" required placeholder="••••••••"
                               class="w-full bg-warm/30 border border-sand/50 rounded-2xl px-4 py-3.5 pl-11 text-sm text-stone-ink focus:outline-none focus:border-clay focus:ring-1 focus:ring-clay transition">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-stone-ink/40">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- TOMBOL SUBMIT -->
                <div class="pt-3">
                    <button type="submit" name="login" 
                            class="w-full bg-stone-ink text-white py-3.5 rounded-2xl font-semibold hover:bg-clay transition-colors duration-200 shadow-sm flex items-center justify-center gap-2">
                        Masuk Sekarang
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>

            <!-- FOOTER FORM -->
            <div class="text-center mt-6 pt-6 border-t border-sand/30">
                <p class="text-xs text-stone-ink/60">
                    Belum punya akun? <a href="register.php" class="text-clay font-semibold hover:underline">Daftar Akun Baru</a>
                </p>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bg-[#1A1412] text-white pt-8 pb-6">
        <div class="max-w-[1400px] mx-auto px-6 text-center text-[10px] text-sand/40">
            <p>&copy; <?= date('Y') ?> Kopi Singgah. All rights reserved.</p>
        </div>
    </footer>

    <!-- SCRIPT NOTIFIKASI SWEETALERT & AUTOMATIC REDIRECT -->
    <script>
        <?php if ($pesan != "") { ?>
            Swal.fire({
                title: '<?= $status == "success" ? "Berhasil!" : "Gagal!" ?>',
                text: '<?= $pesan ?>',
                icon: '<?= $status ?>',
                timer: <?= $status == "success" ? "1500" : "2500" ?>,
                showConfirmButton: false
            }).then(() => {
                <?php if ($status == "success" && !empty($redirect_url)) { ?>
                    window.location.href = '<?= $redirect_url ?>';
                <?php } ?>
            });
        <?php } ?>
    </script>
</body>
</html> 