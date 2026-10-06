<?php
session_start();
include 'koneksi.php';
/** @var mysqli $koneksi */

// Pengecekan Session yang fleksibel agar tidak salah redirect
$id_user = $_SESSION['user_id'] ?? $_SESSION['id_user'] ?? $_SESSION['id'] ?? null;

if (!$id_user) {
    $_SESSION['error'] = "Silakan login terlebih dahulu.";
    header("Location: login.php");
    exit;
}

$pesan = "";
$status = "";

// 1. Proses Update Profil (Sesuai tb_user)
if (isset($_POST['update_profil'])) {
    $name   = mysqli_real_escape_string($koneksi, $_POST['name'] ?? '');
    $email  = mysqli_real_escape_string($koneksi, $_POST['email'] ?? '');
    $hp     = mysqli_real_escape_string($koneksi, $_POST['hp'] ?? '');
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat'] ?? '');

    $update = mysqli_query($koneksi, "UPDATE tb_user SET name = '$name', email = '$email', hp = '$hp', alamat = '$alamat' WHERE id = '$id_user'");
    
    if ($update) {
        $pesan  = "Profil berhasil diperbarui!";
        $status = "success";
    } else {
        $pesan  = "Gagal memperbarui profil: " . mysqli_error($koneksi);
        $status = "error";
    }
}

// 2. Proses Ganti Password (Sesuai tb_user)
if (isset($_POST['update_password'])) {
    $password_baru = mysqli_real_escape_string($koneksi, $_POST['password_baru'] ?? '');
    
    $update_pw = mysqli_query($koneksi, "UPDATE tb_user SET password = '$password_baru' WHERE id = '$id_user'");
    
    if ($update_pw) {
        $pesan  = "Password berhasil diubah!";
        $status = "success";
    } else {
        $pesan  = "Gagal mengubah password.";
        $status = "error";
    }
}

// 3. Ambil Data User Saat Ini
$query_user = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE id = '$id_user'");
$user       = mysqli_fetch_array($query_user);

// CEK ROLE USER UNTUK PERLAKUAN SPESIAL
$is_admin = isset($user['role']) && $user['role'] == 'admin';

// 4. Ambil Data Riwayat Transaksi User (Urut dari yang terbaru)
$query_transaksi = mysqli_query($koneksi, "SELECT * FROM tb_transaksi WHERE id_pelanggan = '$id_user' ORDER BY id_transaksi DESC");

// PENCEGAHAN ERROR NULL DI PHP 8
$user_id_safe       = $user['id'] ?? '';
$user_name_safe     = $user['name'] ?? ($user['username'] ?? 'Pengguna');
$user_username_safe = $user['username'] ?? 'user';
$user_email_safe    = $user['email'] ?? '';
$user_hp_safe       = $user['hp'] ?? '';
$user_alamat_safe   = $user['alamat'] ?? '';
$user_role_safe     = $user['role'] ?? 'pelanggan';
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Saya - Kopi Singgah</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@300;400;500;600;700&family=Caveat:wght@600&display=swap" rel="stylesheet">
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
                        'royal-coffee': '#3D2B20', 
                        'gold-sand': '#E6D2B5',    
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
        .swal2-popup { border-radius: 1rem !important; font-family: 'Inter', sans-serif !important; }
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
            <div class="hidden lg:flex items-center gap-10 text-sm font-medium">
                <a href="index.php" class="text-stone-ink/60 hover:text-stone-ink transition">Beranda</a>
                <a href="menu.php" class="text-stone-ink/60 hover:text-stone-ink transition">Menu</a>
                <a href="keranjang.php" class="text-stone-ink/60 hover:text-stone-ink transition">Keranjang</a>
            </div>
            <div class="flex items-center gap-5">
                <a href="profile.php" class="border-b border-stone-ink pb-1 font-semibold">Profil</a>
                <a href="logout.php" class="hidden md:inline-block border border-stone-ink text-stone-ink text-xs font-semibold px-5 py-2.5 rounded-full hover:bg-stone-ink hover:text-white transition">Keluar</a>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="flex-grow py-12">
        <div class="max-w-[1000px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- HEADER PROFIL -->
            <div class="mb-10 text-center md:text-left flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2 justify-center md:justify-start">
                        <span class="text-clay text-[10px] font-bold tracking-widest uppercase">Pengaturan Akun</span>
                        <?php if($is_admin): ?>
                            <span class="bg-clay text-white text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full flex items-center gap-1 shadow-sm">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.462a1 1 0 00.951-.69l1.07-3.292z" clip-rule="evenodd" /></svg>
                                Admin
                            </span>
                        <?php endif; ?>
                    </div>
                    <h1 class="font-serif text-3xl md:text-4xl text-stone-ink">
                        Halo, <?= htmlspecialchars($user_name_safe) ?>!
                    </h1>
                </div>
                
                <?php if($is_admin): ?>
                    <a href="dashboard/dasbor.php" class="inline-flex items-center justify-center gap-2 bg-clay text-white px-6 py-3 rounded-full font-semibold text-sm hover:bg-stone-ink transition shadow-soft">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        Dashboard Admin
                    </a>
                <?php endif; ?>
            </div>

            <div class="flex flex-col md:flex-row gap-8">
                
                <!-- SIDEBAR NAV -->
                <div class="w-full md:w-1/3 lg:w-1/4">
                    <div class="rounded-3xl shadow-soft p-4 flex flex-col gap-2 sticky top-28 border <?php echo $is_admin ? 'bg-royal-coffee border-clay/20 text-gold-sand' : 'bg-white border-sand/30 text-stone-ink'; ?>">
                        
                        <div class="flex flex-col items-center text-center gap-2 mb-4 pb-4 border-b <?php echo $is_admin ? 'border-gold-sand/10' : 'border-sand/30'; ?>">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold mb-2 transition
                                <?php echo $is_admin ? 'bg-clay text-white' : 'bg-clay/10 text-clay'; ?>">
                                <?= strtoupper(substr($user_name_safe, 0, 1)); ?>
                            </div>
                            <div class="font-serif text-lg font-semibold <?php echo $is_admin ? 'text-white' : 'text-stone-ink'; ?>"><?= htmlspecialchars($user_name_safe); ?></div>
                            <div class="text-xs <?php echo $is_admin ? 'text-gold-sand/70' : 'text-stone-ink/60'; ?>"><?= htmlspecialchars($user_email_safe); ?></div>
                        </div>

                        <button onclick="openTab('profil')" id="btn-profil" class="tab-btn w-full text-left px-5 py-3.5 rounded-2xl text-sm font-semibold transition 
                            <?php echo $is_admin ? 'text-gold-sand hover:bg-white/5' : 'text-stone-ink/70 hover:bg-warm'; ?> bg-clay text-white">
                            Informasi Akun
                        </button>
                        <button onclick="openTab('pesanan')" id="btn-pesanan" class="tab-btn w-full text-left px-5 py-3.5 rounded-2xl text-sm font-semibold transition
                            <?php echo $is_admin ? 'text-gold-sand hover:bg-white/5' : 'text-stone-ink/70 hover:bg-warm'; ?>">
                            Riwayat Pesanan
                        </button>
                        <button onclick="openTab('keamanan')" id="btn-keamanan" class="tab-btn w-full text-left px-5 py-3.5 rounded-2xl text-sm font-semibold transition
                            <?php echo $is_admin ? 'text-gold-sand hover:bg-white/5' : 'text-stone-ink/70 hover:bg-warm'; ?>">
                            Keamanan
                        </button>
                        
                        <div class="h-px my-2 mx-4 <?php echo $is_admin ? 'bg-gold-sand/10' : 'bg-sand/30'; ?>"></div>
                        
                        <a href="logout.php" class="w-full text-left px-5 py-3.5 rounded-2xl text-sm font-semibold text-red-400 hover:bg-red-500/10 transition flex items-center justify-between">
                            Keluar Akun
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </a>
                    </div>
                </div>

                <!-- CONTENT AREA -->
                <div class="w-full md:w-2/3 lg:w-3/4">
                    
                    <!-- TAB 1: INFORMASI AKUN -->
                    <div id="tab-profil" class="tab-content block bg-white rounded-[2rem] shadow-soft p-8 md:p-10">
                        <h2 class="font-serif text-2xl mb-6 border-b border-sand/30 pb-4">Informasi Akun</h2>
                        <form method="POST" action="profile.php" class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold text-stone-ink/60 uppercase tracking-widest mb-2">Nama Lengkap</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($user_name_safe) ?>" required class="w-full bg-warm/30 border border-sand/50 rounded-xl px-4 py-3 text-sm text-stone-ink focus:outline-none focus:border-clay focus:ring-1 focus:ring-clay transition">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-stone-ink/60 uppercase tracking-widest mb-2">Email</label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($user_email_safe) ?>" required class="w-full bg-warm/30 border border-sand/50 rounded-xl px-4 py-3 text-sm text-stone-ink focus:outline-none focus:border-clay focus:ring-1 focus:ring-clay transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-stone-ink/60 uppercase tracking-widest mb-2">No. Telepon (HP)</label>
                                    <input type="text" name="hp" value="<?= htmlspecialchars($user_hp_safe) ?>" placeholder="08xxxxxxxxxx" class="w-full bg-warm/30 border border-sand/50 rounded-xl px-4 py-3 text-sm text-stone-ink focus:outline-none focus:border-clay focus:ring-1 focus:ring-clay transition">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-stone-ink/60 uppercase tracking-widest mb-2">Alamat Pengiriman</label>
                                <textarea name="alamat" rows="3" placeholder="Tuliskan alamat pengiriman lengkap..." class="w-full bg-warm/30 border border-sand/50 rounded-xl px-4 py-3 text-sm text-stone-ink focus:outline-none focus:border-clay focus:ring-1 focus:ring-clay transition"><?= htmlspecialchars($user_alamat_safe) ?></textarea>
                            </div>
                            
                            <div class="pt-4 flex justify-end">
                                <button type="submit" name="update_profil" class="bg-stone-ink text-white px-8 py-3 rounded-xl font-semibold hover:bg-clay transition-colors">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 2: RIWAYAT PESANAN (DINAMIS SAMA PEMBAYARAN.PHP) -->
                    <div id="tab-pesanan" class="tab-content hidden bg-white rounded-[2rem] shadow-soft p-8 md:p-10">
                        <h2 class="font-serif text-2xl mb-6 border-b border-sand/30 pb-4">Riwayat Pesanan</h2>
                        
                        <?php if(mysqli_num_rows($query_transaksi) > 0) { ?>
                            <div class="space-y-4">
                                <?php while($trx = mysqli_fetch_array($query_transaksi)) { 
                                    $trx_id_safe      = $trx['id_transaksi'] ?? 0;
                                    $trx_tanggal_safe = $trx['tanggal'] ?? date('Y-m-d');
                                    $trx_harga_safe   = $trx['total_harga'] ?? 0;
                                    
                                    // Ambil status transaksi dari DB (Default: 'pending')
                                    $trx_status_safe  = strtolower($trx['status'] ?? $trx['status_pembayaran'] ?? 'pending');
                                    $trx_is_paid      = in_array($trx_status_safe, ['selesai', 'lunas', 'success', 'paid'], true);
                                ?>
                                    <div class="border border-sand/40 rounded-2xl p-5 hover:border-clay transition">
                                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                                            <div>
                                                <div class="text-xs text-stone-ink/50 font-semibold mb-1">
                                                    <?= date('d M Y', strtotime($trx_tanggal_safe)) ?> &bull; INV-<?= str_pad($trx_id_safe, 5, '0', STR_PAD_LEFT) ?>
                                                </div>
                                                <div class="font-bold text-stone-ink text-lg">
                                                    Rp <?= number_format($trx_harga_safe, 0, ',', '.') ?>
                                                </div>
                                            </div>
                                            
                                            <!-- BADGE STATUS & ACTION BUTTON DINAMIS -->
                                            <div class="flex items-center gap-3">
                                                <div id="badge-<?= $trx_id_safe ?>">
                                                    <?php if($trx_is_paid): ?>
                                                        <span class="bg-sage/10 text-sage border border-sage/20 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                                            Selesai
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="bg-amber-100 text-amber-800 border border-amber-300 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                                            Pending
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div id="btn-<?= $trx_id_safe ?>">
                                                    <?php if($trx_is_paid): ?>
                                                        <a href="cetak_invoice.php?id=<?= $trx_id_safe ?>" class="border border-stone-ink/30 text-stone-ink hover:bg-stone-ink hover:text-white px-3.5 py-1.5 rounded-xl text-xs font-medium transition flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                            Cetak
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="pembayaran.php?id=<?= $trx_id_safe ?>" class="bg-clay text-white hover:bg-stone-ink px-4 py-1.5 rounded-xl text-xs font-semibold transition flex items-center gap-1 shadow-sm">
                                                            Bayar Sekarang &rarr;
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } else { ?>
                            <div class="text-center py-10">
                                <div class="w-16 h-16 bg-warm rounded-full flex items-center justify-center mx-auto mb-4 text-stone-ink/30">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                </div>
                                <p class="text-stone-ink/60 text-sm mb-4">Kamu belum pernah melakukan pesanan.</p>
                                <a href="menu.php" class="text-clay font-semibold hover:underline text-sm">Mulai Belanja &rarr;</a>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- TAB 3: KEAMANAN -->
                    <div id="tab-keamanan" class="tab-content hidden bg-white rounded-[2rem] shadow-soft p-8 md:p-10">
                        <h2 class="font-serif text-2xl mb-6 border-b border-sand/30 pb-4">Ubah Password</h2>
                        <form method="POST" action="profile.php" class="space-y-6 max-w-md">
                            <div>
                                <label class="block text-xs font-bold text-stone-ink/60 uppercase tracking-widest mb-2">Password Baru</label>
                                <input type="password" name="password_baru" required class="w-full bg-warm/30 border border-sand/50 rounded-xl px-4 py-3 text-sm text-stone-ink focus:outline-none focus:border-clay focus:ring-1 focus:ring-clay transition" placeholder="Masukkan password baru">
                            </div>
                            <div class="pt-2">
                                <button type="submit" name="update_password" class="bg-stone-ink text-white px-8 py-3 rounded-xl font-semibold hover:bg-clay transition-colors w-full sm:w-auto">
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bg-[#1A1412] text-white pt-10 pb-8 mt-auto">
        <div class="max-w-[1400px] mx-auto px-6 text-center text-[10px] text-sand/40">
            <p>&copy; <?= date('Y') ?> Kopi Singgah. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Fungsi Tab
        function openTab(tabName) {
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('block');
            });

            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => {
                btn.classList.remove('bg-clay', 'text-white');
                btn.classList.add('<?php echo $is_admin ? 'text-gold-sand' : 'text-stone-ink/70'; ?>');
            });

            document.getElementById('tab-' + tabName).classList.remove('hidden');
            document.getElementById('tab-' + tabName).classList.add('block');
            
            const activeBtn = document.getElementById('btn-' + tabName);
            activeBtn.classList.add('bg-clay', 'text-white');
            activeBtn.classList.remove('<?php echo $is_admin ? 'text-gold-sand' : 'text-stone-ink/70'; ?>');
        }

        // Cek sinyal pembayaran dari localStorage saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Cek apakah ada update dari pembayaran.php
            if (localStorage.getItem('trx_update_profile')) {
                // Ambil semua transaksi yang dibayar
                const paidKeys = Object.keys(localStorage).filter(key => key.startsWith('trx_paid_'));
                
                paidKeys.forEach(key => {
                    const trxId = key.replace('trx_paid_', '');
                    const badge = document.getElementById('badge-' + trxId);
                    const btn = document.getElementById('btn-' + trxId);
                    
                    if (badge) {
                        badge.innerHTML = `
                            <span class="bg-sage/10 text-sage border border-sage/20 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                Selesai
                            </span>`;
                    }
                    
                    if (btn) {
                        btn.innerHTML = `
                            <a href="cetak_invoice.php?id=${encodeURIComponent(trxId)}" class="border border-stone-ink/30 text-stone-ink hover:bg-stone-ink hover:text-white px-3.5 py-1.5 rounded-xl text-xs font-medium transition flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Cetak
                            </a>`;
                    }
                });
                
                // Bersihkan flag setelah dipakai (opsional)
                localStorage.removeItem('trx_update_profile');
            }
        });

        // SweetAlert untuk pesan sukses/error
        <?php if($pesan != "") { ?>
            Swal.fire({
                title: '<?= $status == "success" ? "Berhasil!" : "Gagal!" ?>',
                text: '<?= $pesan ?>',
                icon: '<?= $status ?>',
                timer: 2500,
                showConfirmButton: false
            });
        <?php } ?>
    </script>
</body>
</html>