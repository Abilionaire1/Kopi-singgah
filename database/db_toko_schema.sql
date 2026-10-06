CREATE DATABASE IF NOT EXISTS `db_toko`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;
USE `db_toko`;

CREATE TABLE `barang` (
  `id_brg` varchar(150) NOT NULL,
  `nama_barang` varchar(150) NOT NULL,
  `stok` int NOT NULL,
  `harga` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_brg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `log_pembelian` (
  `operasi` varchar(25) NOT NULL,
  `waktu` date DEFAULT NULL,
  PRIMARY KEY (`operasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `pembayaran` (
  `id_pem` int NOT NULL,
  `jumlah_pem` int NOT NULL,
  PRIMARY KEY (`id_pem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `pembelian` (
  `id_pem` int NOT NULL,
  `id_brg` varchar(150) NOT NULL,
  `jml_beli` int NOT NULL,
  PRIMARY KEY (`id_pem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `tb_kategori` (
  `id_kategori` int NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(255) NOT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `tb_produk` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_produk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL,
  `poto` text DEFAULT NULL,
  `id_kategori` int DEFAULT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_kategori` (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `tb_user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `hp` varchar(255) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `role` enum('admin','pelanggan') NOT NULL DEFAULT 'pelanggan',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `tb_transaksi` (
  `id_transaksi` int NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int NOT NULL,
  `tanggal` date NOT NULL,
  `total_harga` int NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `alamat_pengiriman` text DEFAULT NULL,
  `hp_pengiriman` varchar(30) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  PRIMARY KEY (`id_transaksi`),
  KEY `id_pelanggan` (`id_pelanggan`),
  CONSTRAINT `fk_transaksi_user`
    FOREIGN KEY (`id_pelanggan`) REFERENCES `tb_user` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `tb_detail` (
  `id_detail` int NOT NULL AUTO_INCREMENT,
  `id_transaksi` int NOT NULL,
  `id_produk` int NOT NULL,
  `jumlah` int NOT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `id_transaksi` (`id_transaksi`),
  KEY `id_produk` (`id_produk`),
  CONSTRAINT `fk_detail_produk`
    FOREIGN KEY (`id_produk`) REFERENCES `tb_produk` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detail_transaksi`
    FOREIGN KEY (`id_transaksi`) REFERENCES `tb_transaksi` (`id_transaksi`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DELIMITER $$

CREATE TRIGGER `trg_pembelian_delete_log`
AFTER DELETE ON `pembelian` FOR EACH ROW
BEGIN
  INSERT INTO `log_pembelian` (`operasi`, `waktu`)
  VALUES ('Delete', CURDATE())
  ON DUPLICATE KEY UPDATE `waktu` = CURDATE();
END$$

CREATE TRIGGER `trg_pembelian_insert`
BEFORE INSERT ON `pembelian` FOR EACH ROW
BEGIN
  DECLARE bonus INT DEFAULT 0;
  DECLARE harga_satuan INT DEFAULT 0;

  SELECT `harga` INTO harga_satuan
  FROM `barang`
  WHERE `id_brg` = NEW.`id_brg`;

  INSERT INTO `pembayaran` (`id_pem`, `jumlah_pem`)
  VALUES (NEW.`id_pem`, NEW.`jml_beli` * harga_satuan)
  ON DUPLICATE KEY UPDATE
    `jumlah_pem` = `jumlah_pem` + (NEW.`jml_beli` * harga_satuan);

  IF NEW.`jml_beli` > 50 AND NEW.`jml_beli` < 100 THEN
    SET bonus = 5;
  ELSEIF NEW.`jml_beli` >= 100 AND NEW.`jml_beli` <= 150 THEN
    SET bonus = 10;
  ELSEIF NEW.`jml_beli` > 150 THEN
    SET bonus = 20;
  END IF;

  SET NEW.`jml_beli` = NEW.`jml_beli` + bonus;

  UPDATE `barang`
  SET `stok` = `stok` - NEW.`jml_beli`
  WHERE `id_brg` = NEW.`id_brg`;
END$$

CREATE TRIGGER `trg_pembelian_insert_log`
AFTER INSERT ON `pembelian` FOR EACH ROW
BEGIN
  INSERT INTO `log_pembelian` (`operasi`, `waktu`)
  VALUES ('Insert', CURDATE())
  ON DUPLICATE KEY UPDATE `waktu` = CURDATE();
END$$

CREATE TRIGGER `trg_pembelian_update_log`
AFTER UPDATE ON `pembelian` FOR EACH ROW
BEGIN
  INSERT INTO `log_pembelian` (`operasi`, `waktu`)
  VALUES ('Update', CURDATE())
  ON DUPLICATE KEY UPDATE `waktu` = CURDATE();
END$$

CREATE TRIGGER `trg_tb_detail_delete_log`
AFTER DELETE ON `tb_detail` FOR EACH ROW
BEGIN
  INSERT INTO `log_pembelian` (`operasi`, `waktu`)
  VALUES ('Delete', CURDATE())
  ON DUPLICATE KEY UPDATE `waktu` = CURDATE();
END$$

CREATE TRIGGER `trg_tb_detail_insert`
BEFORE INSERT ON `tb_detail` FOR EACH ROW
BEGIN
  DECLARE harga_satuan INT DEFAULT 0;

  SELECT `harga` INTO harga_satuan
  FROM `tb_produk`
  WHERE `id` = NEW.`id_produk`;

  INSERT INTO `pembayaran` (`id_pem`, `jumlah_pem`)
  VALUES (NEW.`id_transaksi`, NEW.`jumlah` * harga_satuan)
  ON DUPLICATE KEY UPDATE
    `jumlah_pem` = `jumlah_pem` + (NEW.`jumlah` * harga_satuan);

  UPDATE `tb_produk`
  SET `stok` = `stok` - NEW.`jumlah`
  WHERE `id` = NEW.`id_produk`;
END$$

CREATE TRIGGER `trg_tb_detail_insert_log`
AFTER INSERT ON `tb_detail` FOR EACH ROW
BEGIN
  INSERT INTO `log_pembelian` (`operasi`, `waktu`)
  VALUES ('Insert', CURDATE())
  ON DUPLICATE KEY UPDATE `waktu` = CURDATE();
END$$

CREATE TRIGGER `trg_tb_detail_update_log`
AFTER UPDATE ON `tb_detail` FOR EACH ROW
BEGIN
  INSERT INTO `log_pembelian` (`operasi`, `waktu`)
  VALUES ('Update', CURDATE())
  ON DUPLICATE KEY UPDATE `waktu` = CURDATE();
END$$

DELIMITER ;
