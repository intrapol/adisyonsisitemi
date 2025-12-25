-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 21 Kas 2025, 18:45:22
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `src_adisyon`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `anliksiparis`
--

CREATE TABLE `anliksiparis` (
  `id` int(11) NOT NULL,
  `masa_id` int(11) NOT NULL,
  `urun_id` int(11) NOT NULL,
  `adet` int(11) NOT NULL,
  `saat` time NOT NULL,
  `aciklama` text NOT NULL,
  `siparis_durumu` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `anliksiparis`
--

INSERT INTO `anliksiparis` (`id`, `masa_id`, `urun_id`, `adet`, `saat`, `aciklama`, `siparis_durumu`) VALUES
(84, 9, 17, 3, '12:08:41', ' ', 0),
(85, 9, 22, 71, '12:18:29', ' ', 0);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `gider`
--

CREATE TABLE `gider` (
  `id` int(11) NOT NULL,
  `aciklama` text NOT NULL,
  `tutar` float NOT NULL,
  `tarih` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `gider`
--

INSERT INTO `gider` (`id`, `aciklama`, `tutar`, `tarih`) VALUES
(2, 'Makarana', 14.99, '2025-09-29'),
(4, 'açıklamalı', 11, '2025-09-30'),
(5, 'geçen haftanın gideri', 123123, '2025-09-15');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `kategori_adi` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `kategori`
--

INSERT INTO `kategori` (`id`, `kategori_adi`) VALUES
(1, 'Yemekler'),
(2, 'Tatlılar'),
(3, 'İçecekler'),
(4, 'Dondurmalar');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `masalar`
--

CREATE TABLE `masalar` (
  `id` int(11) NOT NULL,
  `masa_adi` varchar(255) NOT NULL,
  `durum` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `masalar`
--

INSERT INTO `masalar` (`id`, `masa_adi`, `durum`) VALUES
(8, 'M-1', 0),
(9, 'M-2', 1),
(10, 'M-3', 0),
(11, 'M-4', 0),
(12, 'M-5', 0),
(13, 'M-6', 0),
(14, 'M-7', 0),
(15, 'M-9', 0),
(16, 'M-10', 0);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rapor`
--

CREATE TABLE `rapor` (
  `id` int(11) NOT NULL,
  `urun_adi` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci NOT NULL,
  `urun_fiyat` float NOT NULL,
  `adet` int(11) NOT NULL,
  `tarih` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Tablo döküm verisi `rapor`
--

INSERT INTO `rapor` (`id`, `urun_adi`, `urun_fiyat`, `adet`, `tarih`) VALUES
(1, 'Tereyağlı Balık', 300, 13, '2025-09-18'),
(2, 'Mantar', 280, 2, '2025-09-18'),
(3, 'Tereyağlı Balık', 300, 11, '2025-09-18'),
(4, 'Fanta', 50, 4, '2025-09-18'),
(5, 'Tereyağlı Balık', 300, 2, '2025-09-18'),
(6, 'Tahin Helva', 200, 3, '2025-09-18'),
(7, 'Mantar', 280, 5, '2025-09-18'),
(8, 'Şalgam', 50, 3, '2025-09-18'),
(9, 'Kola', 50, 1, '2025-09-19'),
(10, 'Şalgam', 50, 100, '2025-09-19'),
(11, 'Tereyağlı Balık', 300, 100, '2025-09-19'),
(12, 'serdarın alabalık', 1000000, 100, '2025-09-19'),
(13, 'Helva', 200, 3, '2025-09-20'),
(14, 'Mantar', 200, 1, '2025-09-20'),
(15, 'Helva', 200, 1, '2025-09-20'),
(16, 'Tereyağlı Balık', 300, 2, '2025-09-20'),
(17, 'Kola', 50, 2, '2025-09-20'),
(18, 'Kola', 50, 1, '2025-09-20'),
(19, 'Şalgam', 50, 1, '2025-09-20'),
(20, 'Baklavaki', 100, 3, '2025-09-21'),
(21, 'Tereyağlı Balık', 300, 3, '2025-09-25'),
(22, 'Helva', 200, 1, '2025-09-25'),
(23, 'Tereyağlı Balık', 300, 1, '2025-09-25'),
(24, 'Tereyağlı Balık', 300, 1, '2025-09-25'),
(25, 'Helva', 200, 1, '2025-09-25'),
(26, 'Şalgam', 50, 1, '2025-09-25'),
(27, 'Mantar', 200, 1, '2025-09-25'),
(28, 'Tereyağlı Balık', 300, 1, '2025-09-25'),
(29, 'Baklavaki', 100, 1, '2025-09-25'),
(30, 'Baklavaki', 100, 1, '2025-09-25'),
(31, 'Baklavaki', 100, 1, '2025-09-25'),
(32, 'Kola', 50, 1, '2025-09-25'),
(33, 'Golf Dondurma', 55.49, 2, '2025-09-25'),
(34, 'Fanta', 50, 1, '2025-09-25'),
(35, 'Mantar', 200, 1, '2025-09-25'),
(36, 'Helva', 200, 1, '2025-09-25'),
(37, 'Meyve Suyu', 50, 1, '2025-09-25'),
(38, 'Şalgam', 50, 1, '2025-09-25'),
(39, 'Fanta', 50, 1, '2025-09-25'),
(40, 'Baklavaki', 100, 1, '2025-09-25'),
(41, 'Tereyağlı Balık', 300, 1, '2025-09-25'),
(42, 'Helva', 200, 1, '2025-09-25'),
(43, 'Mantar', 200, 1, '2025-09-25'),
(44, 'Tereyağlı Balık', 300, 1, '2025-09-25'),
(45, 'Şalgam', 50, 3, '2025-09-25'),
(46, 'Şalgam', 50, 94, '2025-09-25'),
(47, 'Baklavaki', 100, 3, '2025-09-25'),
(48, 'Kola', 50, 99, '2025-09-25'),
(49, 'Mantar', 200, 2, '2025-09-25'),
(50, 'Baklavaki', 100, 3, '2025-09-25'),
(51, 'Tereyağlı Balık', 300, 2, '2025-09-25'),
(52, 'Helva', 200, 2, '2025-09-25'),
(53, 'Baklavaki', 100, 2, '2025-09-25'),
(54, 'Fanta', 50, 2, '2025-09-25'),
(55, 'Mantar', 200, 1, '2025-09-25'),
(56, 'Baklavaki', 100, 1, '2025-09-25'),
(57, 'Baklavaki', 100, 1, '2025-09-25'),
(58, 'Fanta', 50, 4, '2025-09-25'),
(59, 'Mantar', 200, 4, '2025-09-25'),
(60, 'Kola', 50, 6, '2025-09-25'),
(61, 'Tereyağlı Balık', 300, 3, '2025-09-25'),
(62, 'Baklavaki', 100, 1, '2025-09-25'),
(63, 'Kola', 50, 1, '2025-09-25'),
(64, 'Kola', 50, 2, '2025-09-25'),
(65, 'Baklavaki', 100, 4, '2025-09-25'),
(66, 'Baklavaki', 10.5, 1, '2025-09-25'),
(67, 'Tereyağlı Balık', 300, 1, '2025-09-25'),
(68, 'Meyve Suyu', 50, 7, '2025-10-26'),
(69, 'Helva', 200, 4, '2025-10-26'),
(70, 'Tereyağlı Balık', 300, 5, '2025-10-26'),
(71, 'Mantar', 200, 4, '2025-10-26'),
(72, 'Meyve Suyu', 50, 8, '2025-10-26'),
(73, 'Kola', 50, 3, '2025-10-26'),
(74, 'Baklavaki', 10.5, 4, '2025-10-26'),
(75, 'Helva', 200, 5, '2025-10-26'),
(76, 'Şalgam', 50, 4, '2025-10-26'),
(77, 'Şalgam', 50, 4, '2025-10-26'),
(78, 'Golf Dondurma', 55.49, 6, '2025-10-26'),
(79, 'Baklavaki', 10.5, 3, '2025-10-26'),
(80, 'Golf Dondurma', 55.49, 6, '2025-10-26'),
(81, 'Tereyağlı Balık', 300, 6, '2025-10-26'),
(82, 'Meyve Suyu', 50, 14, '2025-10-26');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `urunler`
--

CREATE TABLE `urunler` (
  `id` int(11) NOT NULL,
  `urun_adi` varchar(255) NOT NULL,
  `urun_fiyat` float NOT NULL,
  `kategori_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `urunler`
--

INSERT INTO `urunler` (`id`, `urun_adi`, `urun_fiyat`, `kategori_id`) VALUES
(12, 'Tereyağlı Balık', 300, 1),
(13, 'Mantar', 200, 1),
(14, 'Kola', 50, 3),
(15, 'Fanta', 50, 3),
(16, 'Şalgam', 50, 3),
(17, 'Baklavaki', 10.5, 2),
(18, 'Helva', 200, 2),
(21, 'Golf Dondurma', 55.49, 4),
(22, 'Meyve Suyu', 50, 3);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `anliksiparis`
--
ALTER TABLE `anliksiparis`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `gider`
--
ALTER TABLE `gider`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `masalar`
--
ALTER TABLE `masalar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `rapor`
--
ALTER TABLE `rapor`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `urunler`
--
ALTER TABLE `urunler`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `anliksiparis`
--
ALTER TABLE `anliksiparis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- Tablo için AUTO_INCREMENT değeri `gider`
--
ALTER TABLE `gider`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `masalar`
--
ALTER TABLE `masalar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Tablo için AUTO_INCREMENT değeri `rapor`
--
ALTER TABLE `rapor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- Tablo için AUTO_INCREMENT değeri `urunler`
--
ALTER TABLE `urunler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
