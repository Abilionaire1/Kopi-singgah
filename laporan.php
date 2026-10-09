<?php include "koneksi.php"; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan & Log Database</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #e2e2e2; }
    </style>
</head>
<body>

    <p><a href="index.php">&laquo; Kembali ke Form Transaksi</a></p>

    <h2>1. Data Transaksi (Tabel: pembelian)</h2>
    <table>
        <tr>
            <th>ID Pembelian (id_pem)</th>
            <th>Kode Barang (id_brg)</th>
            <th>Jumlah Beli + Bonus (jml_beli)</th>
        </tr>
        <?php
        $pem = mysqli_query($koneksi, "SELECT * FROM pembelian");
        while ($r = mysqli_fetch_assoc($pem)) {
            echo "<tr>
                    <td>{$r['id_pem']}</td>
                    <td>{$r['id_brg']}</td>
                    <td>{$r['jml_beli']}</td>
                  </tr>";
        }
        ?>
    </table>

    <h2>2. Rekap Tagihan (Tabel: pembayaran) — *Otomatis diisi Trigger*</h2>
    <table>
        <tr>
            <th>ID Pembelian (id_pem)</th>
            <th>Total Pembayaran (jumlah_pem)</th>
        </tr>
        <?php
        $bayar = mysqli_query($koneksi, "SELECT * FROM pembayaran");
        while ($r = mysqli_fetch_assoc($bayar)) {
            echo "<tr>
                    <td>{$r['id_pem']}</td>
                    <td>Rp " . number_format($r['jumlah_pem'], 0, ',', '.') . "</td>
                  </tr>";
        }
        ?>
    </table>
</body>
</html>