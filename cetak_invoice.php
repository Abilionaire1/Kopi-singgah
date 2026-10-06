<?php
session_start();
include 'koneksi.php';

$id_transaksi = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_transaksi || $id_transaksi < 1) {
    echo "ID Transaksi tidak ditemukan!";
    exit;
}

$query = mysqli_query($koneksi, "SELECT * FROM tb_transaksi WHERE id_transaksi = $id_transaksi");
if (!$query) {
    http_response_code(500);
    echo "Gagal mengambil data transaksi.";
    exit;
}

$trx = mysqli_fetch_assoc($query);
if (!$trx) {
    http_response_code(404);
    echo "Data transaksi tidak ditemukan!";
    exit;
}

$detail_query = mysqli_query($koneksi, "
    SELECT tb_detail.jumlah AS jumlah, tb_produk.nama_produk, tb_produk.harga
    FROM tb_detail
    JOIN tb_produk ON tb_detail.id_produk = tb_produk.id
    WHERE tb_detail.id_transaksi = $id_transaksi
");
if (!$detail_query) {
    http_response_code(500);
    echo "Gagal mengambil rincian transaksi.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #<?= str_pad($trx['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <style>
        body {
            max-width: 320px;
            margin: 0 auto;
            padding: 16px;
            color: #111;
            font: 12px/1.4 "Courier New", monospace;
        }
        .center { text-align: center; }
        .row { display: flex; justify-content: space-between; gap: 12px; }
        .separator { border-top: 1px dashed #111; margin: 10px 0; }
        .item { margin: 8px 0; }
        .total { font-weight: bold; font-size: 14px; }
        @media print {
            body { max-width: none; margin: 0; padding: 10px; }
            @page { margin: 0; size: auto; }
        }
    </style>
</head>
<body onload="window.print()">
    <header class="center">
        <h1 style="margin: 0">KOPI SINGGAH</h1>
        <div>Struk Pembayaran</div>
        <div>INV-<?= str_pad($trx['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></div>
        <div><?= htmlspecialchars(date('d/m/Y H:i', strtotime($trx['tanggal'] ?? 'now')), ENT_QUOTES, 'UTF-8') ?></div>
    </header>

    <div class="separator"></div>

    <section>
        <?php while ($item = mysqli_fetch_assoc($detail_query)): ?>
            <?php $subtotal = (float) $item['harga'] * (int) $item['jumlah']; ?>
            <div class="item">
                <div><?= htmlspecialchars($item['nama_produk'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="row">
                    <span><?= (int) $item['jumlah'] ?> x <?= number_format((float) $item['harga'], 0, ',', '.') ?></span>
                    <span><?= number_format($subtotal, 0, ',', '.') ?></span>
                </div>
            </div>
        <?php endwhile; ?>
    </section>

    <div class="separator"></div>

    <div class="row total">
        <span>TOTAL</span>
        <span>Rp <?= number_format((float) ($trx['total_harga'] ?? 0), 0, ',', '.') ?></span>
    </div>

    <div class="separator"></div>

    <div class="center">
        <div>Status: <?= htmlspecialchars($trx['status'] ?? 'Selesai', ENT_QUOTES, 'UTF-8') ?></div>
        <p>*** TERIMA KASIH ***<br>Silakan datang kembali</p>
    </div>
</body>
</html>