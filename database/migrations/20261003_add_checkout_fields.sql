ALTER TABLE tb_transaksi
    ADD COLUMN metode_pembayaran VARCHAR(50) NULL,
    ADD COLUMN alamat_pengiriman TEXT NULL,
    ADD COLUMN hp_pengiriman VARCHAR(30) NULL,
    ADD COLUMN catatan TEXT NULL;
