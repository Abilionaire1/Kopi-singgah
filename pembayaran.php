<?php
session_start();
include 'koneksi.php';
/** @var mysqli $koneksi */

// 1. AMBIL ID TRANSAKSI DARI URL
$id_transaksi = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. QUERY DATA TRANSAKSI
$trx_query = mysqli_query($koneksi, "SELECT * FROM tb_transaksi WHERE id_transaksi = '$id_transaksi'");
$trx = ($trx_query && mysqli_num_rows($trx_query) > 0) ? mysqli_fetch_assoc($trx_query) : null;

// Ambil Status Transaksi (Default: 'pending')
$status_trx = strtolower($trx['status'] ?? $trx['status_pembayaran'] ?? 'pending');
$metode_pembayaran = $trx['metode_pembayaran'] ?? 'QRIS';
$alamat_pengiriman = $trx['alamat_pengiriman'] ?? '';

// 3. QUERY DETAIL PRODUK & GAMBAR (ATM DARI menu.php)
$detail_query = mysqli_query($koneksi, "
    SELECT tb_detail.*, tb_detail.jumlah AS jml_beli, tb_produk.nama_produk, tb_produk.harga, tb_produk.poto 
    FROM tb_detail 
    JOIN tb_produk ON tb_detail.id_produk = tb_produk.id 
    WHERE tb_detail.id_transaksi = '$id_transaksi'
");

$items_array = [];
$subtotal = 0;

if ($detail_query && mysqli_num_rows($detail_query) > 0) {
    while ($row = mysqli_fetch_assoc($detail_query)) {
        $items_array[] = $row;
        $subtotal += ($row['harga'] * $row['jml_beli']);
    }
} else {
    // Fallback data jika testing tanpa ID
    $items_array = [
        ['nama_produk' => 'Black Lemonade', 'jml_beli' => 1, 'harga' => 22000, 'poto' => 'black_lemonade.jpg'],
        ['nama_produk' => 'Caramel Coffee', 'jml_beli' => 1, 'harga' => 25000, 'poto' => 'caramel_coffee.jpg']
    ];
    $subtotal = 47000;
}

if (!$trx) {
    $trx = [
        'total_harga' => $subtotal,
        'tanggal' => date('Y-m-d H:i:s')
    ];
}

$invoice_code = 'INV-' . str_pad($id_transaksi > 0 ? $id_transaksi : 32, 5, '0', STR_PAD_LEFT);
$kode_pemesanan = 'KSG-' . strtoupper(substr(md5($invoice_code), 0, 6));

// Cek status lunas
$is_paid = in_array($status_trx, ['selesai', 'lunas', 'success', 'paid']);
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran - Kopi Singgah</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        sage: '#7C8A6C'
                    },
                    fontFamily: {
                        serif: ['Fraunces', 'serif'],
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .print-receipt {
            display: none;
        }

        @media print {
            body > *:not(.print-receipt) {
                display: none !important;
            }
            
            .print-receipt {
                display: block !important;
                position: static !important;
                width: 100% !important;
                max-width: 300px !important;
                margin: 0 auto !important;
                padding: 10px !important;
                font-size: 12px !important;
                line-height: 1.4 !important;
            }

            .print-receipt * {
                font-size: inherit !important;
            }

            .no-print {
                display: none !important;
            }
            
            @page {
                margin: 0;
                size: auto;
            }
        }
    </style>
</head>

<body class="bg-cream text-stone-ink min-h-screen flex flex-col">

    <!-- NAVBAR -->
    <nav class="bg-cream/90 backdrop-blur sticky top-0 z-40 border-b border-sand/30 no-print">
        <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
            <a href="menu.php" class="font-serif text-xl font-semibold flex items-center gap-2 text-clay">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                Kopi Singgah.
            </a>
            <a href="profile.php" class="text-xs font-semibold border border-stone-ink px-4 py-2 rounded-full hover:bg-stone-ink hover:text-white transition">Riwayat Pesanan</a>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="flex-grow py-8 no-print">
        <div class="max-w-5xl mx-auto px-6">

            <!-- TOMBOL BACK / KEMBALI -->
            <div class="mb-6">
                <a href="menu.php" class="inline-flex items-center gap-2 text-xs font-bold text-stone-ink/70 hover:text-clay transition bg-white px-4 py-2 rounded-full border border-sand/40 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Menu
                </a>
            </div>

            <!-- HEADER TRANSAKSI -->
            <div class="flex flex-col md:flex-row justify-between mb-8 gap-4 items-start md:items-center">
                <div>
                    <span class="text-clay text-[10px] font-bold tracking-widest uppercase">Tagihan Pembayaran</span>
                    <h1 class="font-serif text-3xl md:text-4xl font-bold"><?= $invoice_code ?></h1>
                    <p class="text-stone-ink/60 text-sm mt-1"><?= date('d M Y, H:i', strtotime($trx['tanggal'])) ?></p>
                </div>

                <!-- BADGE STATUS DINAMIS -->
                <div id="badge-status">
                    <?php if ($is_paid): ?>
                        <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase border bg-emerald-100 text-emerald-800 border-emerald-300 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Selesai
                        </span>
                    <?php else: ?>
                        <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase border bg-amber-100 text-amber-800 border-amber-300 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Menunggu Pembayaran
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- KIRI: RINCIAN PESANAN -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-sand/30">
                    <h2 class="font-serif text-2xl font-bold mb-6 border-b border-sand/30 pb-4">Rincian Pesanan</h2>

                    <div class="space-y-4">
                        <?php foreach ($items_array as $item): $sub = $item['harga'] * $item['jml_beli']; ?>
                            <div class="flex items-center gap-4">
                                <div class="group w-14 h-14 rounded-xl bg-warm flex-shrink-0 overflow-hidden flex items-center justify-center">
                                    <img src="dashboard/img/<?= htmlspecialchars($item['poto']) ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" onerror="this.src='https://via.placeholder.com/400x300?text=No+Image'">
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-medium text-base text-stone-ink"><?= htmlspecialchars($item['nama_produk']) ?></h3>
                                    <p class="text-xs text-stone-ink/50 mt-1"><?= $item['jml_beli'] ?> x Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                                </div>
                                <p class="font-semibold text-base text-stone-ink">Rp <?= number_format($sub, 0, ',', '.') ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-8 pt-6 border-t border-sand/30 space-y-3 text-sm">
                        <div class="flex justify-between text-stone-ink/70">
                            <span>Subtotal</span>
                            <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                        </div>
                        <div class="flex justify-between text-stone-ink/70">
                            <span>Biaya Layanan</span>
                            <span class="text-clay font-medium">Gratis</span>
                        </div>
                        <div class="flex justify-between font-bold text-xl text-stone-ink pt-4 mt-2 border-t border-sand/30 border-dashed">
                            <span>Total Pembayaran</span>
                            <span class="text-clay font-serif">Rp <?= number_format($trx['total_harga'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>

                <!-- KANAN: SCAN & BAYAR -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-sand/30 flex flex-col">
                    <h2 class="font-serif text-2xl font-bold mb-6 border-b border-sand/30 pb-4">Metode Pembayaran</h2>

                    <div class="flex flex-col items-center flex-grow">
                        <p class="text-stone-ink/60 text-sm mb-2">Total Tagihan</p>
                        <p class="font-serif text-4xl font-bold text-clay mb-6">Rp <?= number_format($trx['total_harga'], 0, ',', '.') ?></p>

                        <div class="w-full bg-warm/50 rounded-2xl p-5 mb-6 border border-sand/30 text-center">
                            <p class="text-xs uppercase tracking-widest font-bold text-stone-ink/50 mb-2">Metode yang dipilih</p>
                            <p class="font-semibold text-lg"><?= htmlspecialchars($metode_pembayaran) ?></p>
                            <?php if (strtolower($metode_pembayaran) === 'qris'): ?>
                                <div class="inline-block p-3 bg-white rounded-2xl mt-4 border border-sand/30">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&amp;data=KopiSinggah-<?= urlencode($invoice_code) ?>" alt="QR simulasi pembayaran <?= htmlspecialchars($invoice_code) ?>" class="w-48 h-48">
                                </div>
                                <p class="text-xs text-stone-ink/60 mt-3">QR ini hanya simulasi lokal, belum terhubung ke pembayaran QRIS sungguhan.</p>
                            <?php elseif (strtolower($metode_pembayaran) === 'transfer bank'): ?>
                                <p class="text-sm text-stone-ink/70 mt-3">Transfer bank dipilih. Halaman demo ini tidak meminta data bank atau menampilkan rekening.</p>
                            <?php else: ?>
                                <p class="text-sm text-stone-ink/70 mt-3">Pembayaran dilakukan tunai saat pesanan diterima atau diambil.</p>
                            <?php endif; ?>
                        </div>

                        <div class="w-full text-sm text-stone-ink/70 mb-8">
                            <p><span class="font-semibold text-stone-ink">Pengiriman:</span> <?= htmlspecialchars($alamat_pengiriman !== '' ? $alamat_pengiriman : 'Belum ditentukan') ?></p>
                        </div>
                    </div>

                    <div class="space-y-3 mt-auto">
                        <!-- TOMBOL BAYAR / SUDAH DIBAYAR -->
                        <button id="btn-bayar" 
                            onclick="prosesPembayaran()" 
                            data-metode="<?= htmlspecialchars($metode_pembayaran, ENT_QUOTES) ?>"
                            <?= $is_paid ? 'disabled' : '' ?>
                            class="w-full text-white py-4 rounded-xl font-bold text-base shadow-sm transition 
                            <?= $is_paid 
                                ? 'bg-gray-400 cursor-not-allowed opacity-75' 
                                : 'bg-clay hover:bg-stone-ink cursor-pointer' ?>">
                            <?= $is_paid ? '✓ Sudah Dibayar' : (strtolower($metode_pembayaran) === 'cash / tunai' ? 'Konfirmasi Pesanan' : 'Saya Sudah Membayar') ?>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- MODAL SUCCESS -->
    <div id="successModal" class="hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-4 no-print">
        <div class="bg-white rounded-3xl max-w-sm w-full p-8 text-center shadow-2xl">
            <div class="w-16 h-16 bg-sage/20 rounded-full mx-auto flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-sage" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="font-serif text-2xl text-stone-ink font-bold mb-1">Pembayaran Berhasil</h3>
            <p class="text-3xl font-bold text-sage mb-6">Rp <?= number_format($trx['total_harga'], 0, ',', '.') ?></p>

            <div class="bg-cream rounded-2xl p-4 text-left text-sm space-y-2 mb-6 border border-sand/30">
                <div class="flex justify-between">
                    <span class="text-stone-ink/60">Nomor Tagihan</span>
                    <span class="font-semibold text-stone-ink"><?= $invoice_code ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-stone-ink/60">Kode Pemesanan</span>
                    <span class="font-semibold text-stone-ink"><?= $kode_pemesanan ?></span>
                </div>
                <div class="flex justify-between border-t border-sand/30 pt-2 mt-2">
                    <span class="text-stone-ink/60">Status</span>
                    <span class="font-bold text-emerald-600">SELESAI</span>
                </div>
            </div>

            <div class="space-y-3">
                <a href="profile.php" class="w-full bg-stone-ink text-white py-3 rounded-xl font-semibold text-sm hover:bg-clay transition block text-center">
                    Lihat Riwayat Pesanan
                </a>
                <button onclick="window.print()" class="w-full border border-stone-ink text-stone-ink py-3 rounded-xl font-semibold text-sm hover:bg-stone-ink hover:text-white transition">
                    Cetak Struk
                </button>
            </div>
        </div>
    </div>

    <!-- STRUK THERMAL PRINT -->
    <div class="print-receipt" style="font-family: 'Courier New', monospace; color: #000; background: #fff;">
        <div style="max-width: 280px; margin: 0 auto; padding: 15px 10px; font-size: 11px; line-height: 1.3;">
            <!-- Header -->
            <div style="text-align: center; margin-bottom: 10px;">
                <h2 style="font-size: 16px; font-weight: bold; margin: 0; letter-spacing: 1px;">KOPI SINGGAH</h2>
                <p style="font-size: 10px; margin: 2px 0;">Kedai Kopi & Resto</p>
                <p style="font-size: 10px; margin: 0;"><?= $invoice_code ?></p>
            </div>

            <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>

            <!-- Info -->
            <div style="font-size: 10px; margin-bottom: 8px;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Tgl:</span>
                    <span><?= date('d/m/Y H:i', strtotime($trx['tanggal'])) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Kode:</span>
                    <span><?= $kode_pemesanan ?></span>
                </div>
            </div>

            <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>

            <!-- Items -->
            <div style="font-size: 10px;">
                <?php foreach ($items_array as $item): $sub = $item['harga'] * $item['jml_beli']; ?>
                    <div style="margin-bottom: 6px;">
                        <div style="font-weight: bold;"><?= htmlspecialchars($item['nama_produk']) ?></div>
                        <div style="display: flex; justify-content: space-between; font-size: 10px;">
                            <span><?= $item['jml_beli'] ?> x <?= number_format($item['harga'], 0, ',', '.') ?></span>
                            <span><?= number_format($sub, 0, ',', '.') ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>

            <!-- Total -->
            <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: bold; margin: 8px 0;">
                <span>TOTAL</span>
                <span>Rp <?= number_format($trx['total_harga'], 0, ',', '.') ?></span>
            </div>

            <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>

            <!-- Footer -->
            <div style="text-align: center; margin-top: 12px; font-size: 10px;">
                <p style="font-weight: bold; margin: 0;">*** TERIMA KASIH ***</p>
                <p style="margin: 4px 0 0 0;">Silakan datang kembali</p>
            </div>
        </div>
    </div>

    <script>
        // Set flag paid dari PHP
        let isPaid = <?= $is_paid ? 'true' : 'false' ?>;

        // Tutup modal jika klik di luar
        document.getElementById('successModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
                this.classList.remove('flex');
            }
        });

        function prosesPembayaran() {
    if (isPaid) return;

    const btn = document.getElementById('btn-bayar');
            if (btn.dataset.metode.toLowerCase() === 'cash / tunai') {
                Swal.fire({
                    icon: 'info',
                    title: 'Pesanan Terkonfirmasi',
                    text: 'Pembayaran tunai dilakukan saat pesanan diterima atau diambil.',
                    confirmButtonColor: '#A96B51'
                });
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Pembayaran',
                text: 'Lanjutkan untuk menandai pembayaran sebagai selesai?',
                icon: 'question',
                allowOutsideClick: false,
                showCancelButton: true,
                confirmButtonText: 'Ya, sudah membayar',
                cancelButtonText: 'Belum',
                confirmButtonColor: '#A96B51'
            }).then((result) => {
                if (!result.isConfirmed) return;
                // SINYAL KE PROFILE.PHP
                localStorage.setItem('trx_paid_<?= $id_transaksi ?>', '1');
        localStorage.setItem('trx_update_profile', '1'); // Flag umum
        
        isPaid = true;
      
        document.getElementById('badge-status').innerHTML = `
            <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase border bg-emerald-100 text-emerald-800 border-emerald-300 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Selesai
            </span>`;
        
        btn.disabled = true;
        btn.innerHTML = '✓ Sudah Dibayar';
        btn.classList.remove('bg-clay', 'hover:bg-stone-ink', 'cursor-pointer');
        btn.classList.add('bg-gray-400', 'cursor-not-allowed', 'opacity-75');
        
        const modal = document.getElementById('successModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    });
}
    </script>
</body>

</html>




