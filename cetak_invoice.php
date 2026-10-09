<?php
// cetak_invoice.php
declare(strict_types=1);
session_start();
include 'koneksi.php';

function rupiah(float $n): string
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}

function e(mixed $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

$id_transaksi = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_transaksi || $id_transaksi < 1) {
    echo "ID Transaksi tidak ditemukan!";
    exit;
}

$query = mysqli_query($koneksi, "SELECT t.*, u.name, u.email FROM tb_transaksi t LEFT JOIN tb_user u ON t.id_pelanggan = u.id WHERE t.id_transaksi = $id_transaksi");
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
    SELECT tb_detail.jumlah AS qty, tb_produk.nama_produk AS desc, tb_produk.harga AS price
    FROM tb_detail
    JOIN tb_produk ON tb_detail.id_produk = tb_produk.id
    WHERE tb_detail.id_transaksi = $id_transaksi
");
if (!$detail_query) {
    http_response_code(500);
    echo "Gagal mengambil rincian transaksi.";
    exit;
}

$items = [];
while ($row = mysqli_fetch_assoc($detail_query)) {
    $items[] = [
        'desc' => $row['desc'],
        'qty' => (int)$row['qty'],
        'price' => (float)$row['price']
    ];
}

/* ================= DATA DINAMIS ================= */
$invoice_no = 'INV-' . str_pad((string)$trx['id_transaksi'], 5, '0', STR_PAD_LEFT);
$status_trx = strtoupper($trx['status'] ?? 'PENDING');
if (in_array($status_trx, ['SELESAI', 'LUNAS', 'SUCCESS', 'PAID'])) {
    $status_display = 'LUNAS';
} else {
    $status_display = 'BELUM LUNAS';
}

$invoice = [
    'no'        => $invoice_no,
    'date'      => date('Y-m-d', strtotime($trx['tanggal'])),
    'due_date'  => date('Y-m-d', strtotime($trx['tanggal'] . ' + 1 days')),
    'status'    => $status_display,
    'company'   => [
        'name'    => 'Kopi Singgah',
        'address' => "Jl. Kopi Bersama No. 12, Jakarta",
        'phone'   => '+62 811 2233 4455',
        'email'   => 'hello@kopisinggah.com',
        'npwp'    => '00.000.000.0-000.000',
    ],
    'client'    => [
        'name'    => $trx['name'] ?? 'Pelanggan',
        'company' => 'Pembeli Reguler',
        'address' => $trx['alamat_pengiriman'] ?? 'Ambil di kedai',
        'phone'   => $trx['hp_pengiriman'] ?? '-',
    ],
    'items'     => $items,
    'discount'  => 0,
    'tax_rate'  => 0, // Tidak pakai PPN
    'bank'      => [
        'name'    => $trx['metode_pembayaran'] ?? 'QRIS / Cash',
        'account' => '-',
        'holder'  => 'Kopi Singgah',
    ],
    'notes'     => $trx['catatan'] ? 'Catatan Pembeli: ' . $trx['catatan'] : 'Terima kasih atas pesanan Anda di Kopi Singgah.',
];

/* ================= KALKULASI ================= */
$subtotal = 0.0;
foreach ($invoice['items'] as &$item) {
    $item['total'] = $item['qty'] * $item['price'];
    $subtotal += $item['total'];
}
unset($item);

$discount = (float) ($invoice['discount'] ?? 0);
$taxable  = max(0, $subtotal - $discount);
$tax      = round($taxable * (float) $invoice['tax_rate']);
$grand    = $taxable + $tax;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Invoice <?= e($invoice['no']) ?></title>
<link rel="icon" type="image/svg+xml" href="favicon.svg">
<style>
    /* Margin kertas diatur di sini. Browser akan menerapkan ini saat print. */
    @page {
        size: A4;
        margin: 12mm 14mm;
    }

    * { box-sizing: border-box; }

    html, body {
        margin: 0;
        padding: 0;
        font-family: "Helvetica Neue", Arial, "Segoe UI", sans-serif;
        font-size: 10pt;
        line-height: 1.4;
        color: #1f2937;
        background: #e5e7eb;
    }

    /* Tampilan layar: kertas A4 simulasi */
    .sheet {
        width: 210mm;
        min-height: 297mm;
        margin: 10mm auto;
        padding: 12mm 14mm;
        background: #fff;
        box-shadow: 0 4px 18px rgba(0,0,0,.12);
        display: flex;
        flex-direction: column;
    }

    .accent { color: #A96B51; }
    .muted  { color: #6b7280; }

    /* Header */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding-bottom: 8mm;
        border-bottom: 2px solid #A96B51;
    }
    .brand h1 {
        margin: 0 0 2mm;
        font-size: 15pt;
        color: #1E1B18;
    }
    .brand p { margin: 0; font-size: 9pt; color: #4b5563; }

    .doc-title { text-align: right; }
    .doc-title h2 {
        margin: 0;
        font-size: 22pt;
        letter-spacing: 2px;
        color: #111827;
    }
    .meta { margin-top: 2mm; font-size: 9pt; }
    .meta td { padding: 0.5mm 0; }
    .meta td:first-child { color: #6b7280; padding-right: 4mm; }

    .badge {
        display: inline-block;
        margin-top: 2mm;
        padding: 1mm 4mm;
        font-size: 9pt;
        font-weight: bold;
        border-radius: 3px;
        color: #fff;
        background: #16a34a;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .badge.unpaid { background: #dc2626; }

    /* Info bill-to */
    .parties {
        width: 100%;
        border-collapse: collapse;
        margin: 7mm 0;
    }
    .parties td { vertical-align: top; width: 50%; }
    .label {
        font-size: 8pt;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #6b7280;
        margin-bottom: 1mm;
    }
    .parties strong { font-size: 10.5pt; color: #1E1B18; }

    /* Tabel item */
    .items {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .items thead { display: table-header-group; }
    .items th {
        background: #1E1B18;
        color: #fff;
        font-size: 9pt;
        font-weight: 600;
        padding: 2.5mm 2mm;
        text-align: left;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .items td {
        padding: 2.5mm 2mm;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: top;
    }
    .items tbody tr { page-break-inside: avoid; break-inside: avoid; }
    .items tbody tr:nth-child(even) td {
        background: #F9F6F0;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .num   { text-align: right; white-space: nowrap; }
    .center{ text-align: center; }

    /* Total */
    .totals-wrap {
        display: flex;
        justify-content: flex-end;
        margin-top: 4mm;
    }
    .totals {
        width: 75mm;
        border-collapse: collapse;
    }
    .totals td { padding: 1.5mm 2mm; }
    .totals td:last-child { text-align: right; white-space: nowrap; }
    .totals .grand td {
        font-size: 12pt;
        font-weight: bold;
        color: #A96B51;
        border-top: 2px solid #A96B51;
        padding-top: 2.5mm;
    }

    /* Footer area: bawah halaman */
    .bottom {
        display: flex;
        justify-content: space-between;
        gap: 8mm;
        margin-top: 8mm;
        page-break-inside: avoid;
        break-inside: avoid;
    }
    .box {
        flex: 1;
        font-size: 9pt;
        padding: 3mm;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
    }
    .box p { margin: 0 0 1mm; }
    .sign {
        width: 55mm;
        text-align: center;
        font-size: 9pt;
    }
    .sign .line {
        margin-top: 16mm;
        border-top: 1px solid #111827;
        padding-top: 1mm;
    }

    .footer {
        margin-top: auto;
        padding-top: 4mm;
        border-top: 1px solid #e5e7eb;
        font-size: 8pt;
        color: #6b7280;
        text-align: center;
    }

    /* Tombol cetak (disembunyikan saat print) */
    .toolbar {
        width: 210mm;
        margin: 6mm auto 0;
        text-align: right;
    }
    .btn {
        padding: 2mm 5mm;
        background: #1E1B18;
        color: #fff;
        border: 0;
        border-radius: 4px;
        font-size: 10pt;
        cursor: pointer;
    }
    .btn:hover { background: #A96B51; }

    /* ===== Aturan khusus cetak ===== */
    @media print {
        html, body { background: #fff; }
        .toolbar { display: none !important; }
        .sheet {
            width: auto;
            min-height: 0;
            margin: 0;
            padding: 0;
            box-shadow: none;
            display: block;
        }
        .footer { position: static; }
        a { color: inherit; text-decoration: none; }
    }
</style>
</head>
<body>

<div class="toolbar">
    <button class="btn" onclick="window.print()">Cetak Invoice</button>
</div>

<div class="sheet">

    <!-- HEADER -->
    <div class="header">
        <div class="brand">
            <h1><?= e($invoice['company']['name']) ?></h1>
            <p><?= nl2br(e($invoice['company']['address'])) ?></p>
            <p>Telp: <?= e($invoice['company']['phone']) ?> &middot; <?= e($invoice['company']['email']) ?></p>
        </div>

        <div class="doc-title">
            <h2>INVOICE</h2>
            <table class="meta" style="margin-left:auto;">
                <tr><td>No. Invoice</td><td><strong><?= e($invoice['no']) ?></strong></td></tr>
                <tr><td>Tanggal</td><td><?= date('d M Y', strtotime($invoice['date'])) ?></td></tr>
            </table>
            <span class="badge <?= $invoice['status'] === 'LUNAS' ? '' : 'unpaid' ?>">
                <?= e($invoice['status']) ?>
            </span>
        </div>
    </div>

    <!-- BILL TO -->
    <table class="parties">
        <tr>
            <td>
                <div class="label">Ditagihkan Kepada</div>
                <strong><?= e($invoice['client']['name']) ?></strong><br>
                <?= nl2br(e($invoice['client']['address'])) ?><br>
                <?= e($invoice['client']['phone']) ?>
            </td>
        </tr>
    </table>

    <!-- ITEMS -->
    <table class="items">
        <colgroup>
            <col style="width:8%">
            <col style="width:44%">
            <col style="width:10%">
            <col style="width:19%">
            <col style="width:19%">
        </colgroup>
        <thead>
            <tr>
                <th class="center">No</th>
                <th>Deskripsi</th>
                <th class="center">Qty</th>
                <th class="num">Harga Satuan</th>
                <th class="num">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoice['items'] as $i => $item): ?>
            <tr>
                <td class="center"><?= $i + 1 ?></td>
                <td><?= e($item['desc']) ?></td>
                <td class="center"><?= (int) $item['qty'] ?></td>
                <td class="num"><?= rupiah((float) $item['price']) ?></td>
                <td class="num"><?= rupiah((float) $item['total']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- TOTALS -->
    <div class="totals-wrap">
        <table class="totals">
            <tr>
                <td>Subtotal</td>
                <td><?= rupiah($subtotal) ?></td>
            </tr>
            <tr class="grand">
                <td>TOTAL</td>
                <td><?= rupiah($grand) ?></td>
            </tr>
        </table>
    </div>

    <!-- BANK, CATATAN, TANDA TANGAN -->
    <div class="bottom">
        <div class="box">
            <p class="label">Pembayaran</p>
            <p>Metode: <strong><?= e($invoice['bank']['name']) ?></strong></p>
            <?php if (!empty($invoice['notes'])): ?>
                <p class="label" style="margin-top:3mm;">Catatan</p>
                <p><?= e($invoice['notes']) ?></p>
            <?php endif; ?>
        </div>

        <div class="sign">
            <p>Hormat kami,</p>
            <div class="line"><?= e($invoice['company']['name']) ?></div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        Cetak bukti ini untuk ditukarkan dengan pesanan Anda di Kopi Singgah.
    </div>

</div>

</body>
</html>
