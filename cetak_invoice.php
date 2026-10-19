<?php
session_start();
include 'koneksi.php';
/** @var mysqli $koneksi */

$user_id = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
$transaction_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$user_id || $user_id < 1 || !$transaction_id || $transaction_id < 1) {
    $_SESSION['error'] = 'Silakan login dan pilih pesanan yang valid.';
    header('Location: login.php');
    exit;
}

$stmt_user = mysqli_prepare($koneksi, 'SELECT role FROM tb_user WHERE id = ?');
mysqli_stmt_bind_param($stmt_user, 'i', $user_id);
mysqli_stmt_execute($stmt_user);
$current_user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_user));
mysqli_stmt_close($stmt_user);

if (!$current_user || !in_array(strtolower($current_user['role']), ['admin', 'pelanggan', 'user'], true)) {
    unset($_SESSION['user_id'], $_SESSION['id_user'], $_SESSION['id'], $_SESSION['username'], $_SESSION['role']);
    $_SESSION['error'] = 'Sesi akun tidak valid. Silakan login kembali.';
    header('Location: login.php');
    exit;
}

$role = strtolower($current_user['role']);
$stmt_order = mysqli_prepare(
    $koneksi,
    "SELECT t.*, u.name AS nama_pelanggan, u.email 
     FROM tb_transaksi t 
     JOIN tb_user u ON u.id = t.id_pelanggan 
     WHERE t.id_transaksi = ? AND (? = 'admin' OR t.id_pelanggan = ?) 
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt_order, 'isi', $transaction_id, $role, $user_id);
mysqli_stmt_execute($stmt_order);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_order));
mysqli_stmt_close($stmt_order);

if (!$order) {
    http_response_code(404);
    exit('Pesanan tidak ditemukan atau Anda tidak memiliki akses.');
}

$stmt_items = mysqli_prepare(
    $koneksi,
    'SELECT d.jumlah, p.nama_produk, p.harga 
     FROM tb_detail d 
     LEFT JOIN tb_produk p ON p.id = d.id_produk 
     WHERE d.id_transaksi = ?'
);
mysqli_stmt_bind_param($stmt_items, 'i', $transaction_id);
mysqli_stmt_execute($stmt_items);
$items = mysqli_stmt_get_result($stmt_items);
$return_url = $role === 'admin' ? 'admin_transaksi.php' : 'profile.php';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #<?= (int)$order['id_transaksi'] ?> - Kopi Singgah</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --bg: #F9F6F0;
            --text: #1E1B18;
            --clay: #A96B51;
            --sand: #D8CAB3;
        }
        body { 
            background: var(--bg); 
            color: var(--text); 
            font-family: 'Inter', sans-serif; 
        }
        h1, h2 { 
            font-family: 'Fraunces', serif; 
        }
        .invoice { 
            max-width: 850px; 
            margin: 48px auto; 
            padding: 48px; 
            border: 1px solid var(--sand); 
            border-radius: 24px; 
            background: #FFFFFF; 
            box-shadow: 0 4px 24px rgba(42,36,33,.05); 
        }
        .table { 
            --bs-table-bg: transparent; 
            --bs-table-color: var(--text); 
        }
        .table>:not(caption)>*>* { 
            border-bottom-color: var(--sand); 
        }
        .btn-clay {
            background-color: var(--text);
            color: #fff;
            border: none;
        }
        .btn-clay:hover {
            background-color: var(--clay);
            color: #fff;
        }
        @media print { 
            body { background: #FFFFFF; } 
            .invoice { margin: 0 auto; padding: 18px; border: 0; box-shadow: none; } 
            .no-print { display: none!important; } 
        }
        @media (max-width: 575px) { 
            .invoice { margin: 12px; padding: 22px; } 
        }
    </style>
</head>
<body>
<main class="invoice">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 border-bottom pb-4">
        <div>
            <div class="small text-uppercase fw-bold text-secondary" style="letter-spacing:.18em">KOPI SINGGAH</div>
            <h1 class="h3 fw-bold mt-1">Setiap Singgah<br>Terdapat Rasa</h1>
            <p class="mb-0 text-secondary">Invoice / Bukti Pesanan</p>
        </div>
        <div class="text-sm-end">
            <div class="fw-bold fs-5">#<?= str_pad((int)$order['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></div>
            <div class="text-secondary"><?= htmlspecialchars(date('d F Y H:i', strtotime($order['tanggal'] ?? 'now')), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
    
    <div class="row g-4 py-4">
        <div class="col-sm-6">
            <div class="small text-secondary mb-1">Pelanggan</div>
            <div class="fw-semibold"><?= htmlspecialchars($order['nama_pelanggan'], ENT_QUOTES, 'UTF-8') ?></div>
            <div><?= htmlspecialchars($order['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="col-sm-6">
            <div class="small text-secondary mb-1">Status pesanan</div>
            <div class="fw-semibold text-uppercase"><?= htmlspecialchars($order['status'] ?? 'Pending', ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="col-sm-6">
            <div class="small text-secondary mb-1">Pembayaran</div>
            <div><?= htmlspecialchars($order['metode_pembayaran'] ?? 'Transfer Bank', ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="col-sm-6">
            <div class="small text-secondary mb-1">Pengiriman</div>
            <div><?= htmlspecialchars($order['metode_pengiriman'] ?? 'Kurir', ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="col-12">
            <div class="small text-secondary mb-1">Alamat / pengambilan</div>
            <div><?= nl2br(htmlspecialchars($order['alamat_pengiriman'] ?? 'Ambil di tempat', ENT_QUOTES, 'UTF-8')) ?></div>
            <?php if (!empty($order['hp_pengiriman'])): ?>
                <div class="text-secondary"><?= htmlspecialchars($order['hp_pengiriman'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="text-end">Harga</th>
                    <th class="text-end">Jumlah</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = mysqli_fetch_assoc($items)): ?>
                    <?php 
                        $unit_price = (float)($item['harga'] ?? 0); 
                        $quantity = (int)$item['jumlah']; 
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nama_produk'] ?? 'Produk tidak tersedia', ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end">Rp <?= number_format($unit_price, 0, ',', '.') ?></td>
                        <td class="text-end"><?= $quantity ?></td>
                        <td class="text-end">Rp <?= number_format($unit_price * $quantity, 0, ',', '.') ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end pt-3">Total pesanan</th>
                    <th class="text-end pt-3 fs-5" style="color: var(--clay);">Rp <?= number_format((float)$order['total_harga'], 0, ',', '.') ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <?php if (!empty($order['catatan'])): ?>
    <div class="mt-3">
        <div class="small text-secondary mb-1">Catatan</div>
        <div><?= nl2br(htmlspecialchars($order['catatan'], ENT_QUOTES, 'UTF-8')) ?></div>
    </div>
    <?php endif; ?>
    
    <div class="text-center mt-5 mb-2 print-only" style="display: none;">
        <p class="small text-secondary">*** TERIMA KASIH ***<br>Silakan datang kembali</p>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4 no-print">
        <button type="button" class="btn btn-clay rounded-pill px-4" onclick="window.print()">Cetak Invoice</button>
        <a href="<?= htmlspecialchars($return_url, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary rounded-pill px-4">Kembali</a>
    </div>
</main>
<style>
    @media print {
        .print-only { display: block !important; }
    }
</style>
</body>
</html>