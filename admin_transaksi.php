<?php
session_start();
include 'koneksi.php';
/** @var mysqli $koneksi */

$id_admin = $_SESSION['user_id'] ?? $_SESSION['id_user'] ?? $_SESSION['id'] ?? null;
if (!$id_admin) {
    header("Location: login.php");
    exit;
}

$stmt_role = mysqli_prepare($koneksi, "SELECT role FROM tb_user WHERE id = ?");
mysqli_stmt_bind_param($stmt_role, "i", $id_admin);
mysqli_stmt_execute($stmt_role);
$role = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_role))['role'] ?? '';
if ($role !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak.");
}

$csrf_token = $_SESSION['csrf_admin'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_admin'] = $csrf_token;
$notice = $_SESSION['admin_transaksi_notice'] ?? '';
unset($_SESSION['admin_transaksi_notice']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    $id_transaksi_hapus = filter_input(INPUT_POST, 'id_transaksi', FILTER_VALIDATE_INT);
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($csrf_token, $token) || !$id_transaksi_hapus) {
        http_response_code(400);
        $notice = "Permintaan hapus transaksi tidak valid.";
    } else {
        mysqli_begin_transaction($koneksi);
        $stmt_exists = mysqli_prepare($koneksi, "SELECT id_transaksi FROM tb_transaksi WHERE id_transaksi = ?");
        mysqli_stmt_bind_param($stmt_exists, "i", $id_transaksi_hapus);
        mysqli_stmt_execute($stmt_exists);
        $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_exists));

        if (!$exists) {
            mysqli_rollback($koneksi);
            $notice = "Transaksi tidak ditemukan.";
        } else {
            $stmt_details = mysqli_prepare($koneksi, "DELETE FROM tb_detail WHERE id_transaksi = ?");
            mysqli_stmt_bind_param($stmt_details, "i", $id_transaksi_hapus);
            $details_deleted = mysqli_stmt_execute($stmt_details);

            $stmt_transaction = mysqli_prepare($koneksi, "DELETE FROM tb_transaksi WHERE id_transaksi = ?");
            mysqli_stmt_bind_param($stmt_transaction, "i", $id_transaksi_hapus);
            $transaction_deleted = $details_deleted && mysqli_stmt_execute($stmt_transaction);

            if ($transaction_deleted && mysqli_stmt_affected_rows($stmt_transaction) === 1 && mysqli_commit($koneksi)) {
                $_SESSION['admin_transaksi_notice'] = "Transaksi berhasil dihapus.";
                header("Location: admin_transaksi.php");
                exit;
            }

            $error = !$details_deleted
                ? mysqli_stmt_error($stmt_details)
                : mysqli_stmt_error($stmt_transaction);
            mysqli_rollback($koneksi);
            $notice = "Transaksi gagal dihapus." . ($error ? " " . $error : "");
        }
    }
}

$id_detail = filter_input(INPUT_GET, 'detail', FILTER_VALIDATE_INT) ?: 0;
$detail_transaksi = null;
$detail_items = [];
if ($id_detail > 0) {
    $stmt_detail_trx = mysqli_prepare(
        $koneksi,
        "SELECT t.*, u.name AS nama_pelanggan, u.username, u.email
         FROM tb_transaksi t
         LEFT JOIN tb_user u ON u.id = t.id_pelanggan
         WHERE t.id_transaksi = ?"
    );
    mysqli_stmt_bind_param($stmt_detail_trx, "i", $id_detail);
    mysqli_stmt_execute($stmt_detail_trx);
    $detail_transaksi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_detail_trx));

    if ($detail_transaksi) {
        $stmt_items = mysqli_prepare(
            $koneksi,
            "SELECT d.jumlah, p.nama_produk, p.harga
             FROM tb_detail d
             LEFT JOIN tb_produk p ON p.id = d.id_produk
             WHERE d.id_transaksi = ?"
        );
        mysqli_stmt_bind_param($stmt_items, "i", $id_detail);
        mysqli_stmt_execute($stmt_items);
        $result_items = mysqli_stmt_get_result($stmt_items);
        while ($item = mysqli_fetch_assoc($result_items)) {
            $detail_items[] = $item;
        }
    } else {
        $notice = "Transaksi yang diminta tidak ditemukan.";
    }
}

$result_transactions = mysqli_query(
    $koneksi,
    "SELECT t.id_transaksi, t.tanggal, t.total_harga, t.status, t.metode_pembayaran,
            u.name AS nama_pelanggan, u.username
     FROM tb_transaksi t
     LEFT JOIN tb_user u ON u.id = t.id_pelanggan
     ORDER BY t.id_transaksi DESC"
);
if (!$result_transactions) {
    http_response_code(500);
    exit("Gagal memuat transaksi: " . htmlspecialchars(mysqli_error($koneksi)));
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Transaksi - Kopi Singgah</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: {
            colors: { cream: '#F9F6F0', warm: '#F3EFE6', 'stone-ink': '#1E1B18', clay: '#A96B51', sand: '#D8CAB3' },
            fontFamily: { serif: ['Fraunces', 'serif'], sans: ['Inter', 'sans-serif'] }
        } } };
    </script>
</head>
<body class="min-h-screen bg-cream text-stone-ink font-sans">
    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
        <header class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
            <div>
                <p class="text-clay text-xs font-bold tracking-widest uppercase">Admin Panel</p>
                <h1 class="font-serif text-3xl sm:text-4xl font-bold mt-2">Data Transaksi</h1>
                <p class="text-sm text-stone-ink/60 mt-2">Lihat rincian pesanan atau hapus transaksi.</p>
            </div>
            <a href="dashboard/dasbor.php" class="text-sm font-semibold border border-sand rounded-full px-5 py-2.5 hover:bg-stone-ink hover:text-white transition">Kembali ke Dashboard</a>
        </header>

        <?php if ($notice !== ''): ?>
            <div class="mb-6 rounded-xl border border-sand bg-white px-5 py-4 text-sm"><?= htmlspecialchars($notice) ?></div>
        <?php endif; ?>

        <?php if ($detail_transaksi): ?>
            <section class="bg-white rounded-2xl border border-sand/50 p-5 sm:p-7 mb-8">
                <div class="flex justify-between gap-4 items-start border-b border-sand/40 pb-4 mb-5">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-clay font-bold">Rincian transaksi</p>
                        <h2 class="font-serif text-2xl font-bold mt-1">INV-<?= str_pad((string) $detail_transaksi['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></h2>
                    </div>
                    <a href="admin_transaksi.php" class="text-sm underline text-stone-ink/70">Tutup detail</a>
                </div>
                <dl class="grid sm:grid-cols-2 gap-4 text-sm mb-6">
                    <div><dt class="text-stone-ink/50">Pelanggan</dt><dd class="font-semibold"><?= htmlspecialchars($detail_transaksi['nama_pelanggan'] ?: $detail_transaksi['username'] ?: 'Akun tidak ditemukan') ?></dd></div>
                    <div><dt class="text-stone-ink/50">Email</dt><dd><?= htmlspecialchars($detail_transaksi['email'] ?? '-') ?></dd></div>
                    <div><dt class="text-stone-ink/50">Tanggal</dt><dd><?= htmlspecialchars($detail_transaksi['tanggal'] ?? '-') ?></dd></div>
                    <div><dt class="text-stone-ink/50">Status</dt><dd><?= htmlspecialchars($detail_transaksi['status'] ?? 'Pending') ?></dd></div>
                    <div><dt class="text-stone-ink/50">Metode pembayaran</dt><dd><?= htmlspecialchars($detail_transaksi['metode_pembayaran'] ?? 'Belum dipilih') ?></dd></div>
                    <div><dt class="text-stone-ink/50">Alamat / pengambilan</dt><dd><?= nl2br(htmlspecialchars($detail_transaksi['alamat_pengiriman'] ?? 'Belum tersedia')) ?></dd></div>
                </dl>
                <h3 class="font-semibold mb-3">Item pesanan</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="text-left border-b border-sand/50"><th class="py-2 pr-4">Produk</th><th class="py-2 px-4">Jumlah</th><th class="py-2 px-4">Harga satuan</th><th class="py-2 pl-4 text-right">Subtotal</th></tr></thead>
                        <tbody>
                            <?php foreach ($detail_items as $item): ?>
                                <tr class="border-b border-sand/20">
                                    <td class="py-3 pr-4"><?= htmlspecialchars($item['nama_produk'] ?? 'Produk tidak tersedia') ?></td>
                                    <td class="py-3 px-4"><?= (int) $item['jumlah'] ?></td>
                                    <td class="py-3 px-4">Rp <?= number_format((int) ($item['harga'] ?? 0), 0, ',', '.') ?></td>
                                    <td class="py-3 pl-4 text-right">Rp <?= number_format((int) ($item['jumlah'] ?? 0) * (int) ($item['harga'] ?? 0), 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$detail_items): ?>
                                <tr><td colspan="4" class="py-4 text-center text-stone-ink/50">Tidak ada rincian produk untuk transaksi ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-right font-bold mt-5">Total: Rp <?= number_format((int) $detail_transaksi['total_harga'], 0, ',', '.') ?></p>
            </section>
        <?php endif; ?>

        <section class="bg-white rounded-2xl border border-sand/50 overflow-hidden">
            <div class="px-5 py-4 border-b border-sand/40">
                <h2 class="font-serif text-xl font-semibold">Daftar Transaksi</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-warm/70 text-left">
                        <th class="px-5 py-3">Invoice</th><th class="px-5 py-3">Pelanggan</th><th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Pembayaran</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Total</th><th class="px-5 py-3">Aksi</th>
                    </tr></thead>
                    <tbody>
                    <?php if (mysqli_num_rows($result_transactions) > 0): ?>
                        <?php while ($trx = mysqli_fetch_assoc($result_transactions)): ?>
                            <tr class="border-t border-sand/30">
                                <td class="px-5 py-4 font-semibold">INV-<?= str_pad((string) $trx['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td class="px-5 py-4"><?= htmlspecialchars($trx['nama_pelanggan'] ?: $trx['username'] ?: 'Akun tidak ditemukan') ?></td>
                                <td class="px-5 py-4"><?= htmlspecialchars($trx['tanggal'] ?? '-') ?></td>
                                <td class="px-5 py-4"><?= htmlspecialchars($trx['metode_pembayaran'] ?? 'Belum dipilih') ?></td>
                                <td class="px-5 py-4"><?= htmlspecialchars($trx['status'] ?? 'Pending') ?></td>
                                <td class="px-5 py-4 whitespace-nowrap">Rp <?= number_format((int) $trx['total_harga'], 0, ',', '.') ?></td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <a class="font-semibold text-clay hover:underline" href="admin_transaksi.php?detail=<?= (int) $trx['id_transaksi'] ?>">Detail</a>
                                        <form method="POST" onsubmit="return confirm('Hapus transaksi ini beserta rincian itemnya?');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                            <input type="hidden" name="id_transaksi" value="<?= (int) $trx['id_transaksi'] ?>">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <button type="submit" class="font-semibold text-red-700 hover:underline">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="px-5 py-10 text-center text-stone-ink/50">Belum ada transaksi.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
