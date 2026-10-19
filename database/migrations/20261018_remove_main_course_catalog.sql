-- Migration: Menghapus katalog Main Course dan produk terkait
-- Kafe kopi berfokus pada kopi, dessert, dan pastry

DELETE FROM tb_produk WHERE id_kategori = (SELECT id_kategori FROM tb_kategori WHERE LOWER(nama_kategori) = 'main course');
DELETE FROM tb_kategori WHERE LOWER(nama_kategori) = 'main course';
