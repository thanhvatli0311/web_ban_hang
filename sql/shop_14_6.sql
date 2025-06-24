-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th6 22, 2025 lúc 02:12 PM
-- Phiên bản máy phục vụ: 8.0.30
-- Phiên bản PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `shop_14_6`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banners`
--

CREATE TABLE `banners` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `position` varchar(50) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `banners`
--

INSERT INTO `banners` (`id`, `title`, `image`, `link`, `position`, `is_active`, `start_date`, `end_date`) VALUES
(5, 'Mừng đại lễ 30/4 - 1/5', 'banner_6852e55129e7a6.44838089.png', 'http://localhost:3000/index.php', 'homepage-top', 1, '2025-06-18', '2025-07-18'),
(6, 'Mừng đại lễ 30/4 - 1/5', 'banner_6852e56ad5f509.98859768.png', 'http://localhost:3000/index.php', 'homepage-middle', 1, '2025-06-18', '2025-07-18'),
(7, 'Mừng đại lễ 30/4 - 1/5', 'banner_6852e577aff405.63464971.png', 'http://localhost:3000/index.php', 'homepage-bottom', 1, '2025-06-18', '2025-07-18'),
(8, 'Mừng đại lễ 30/4 - 1/5', 'banner_6852e58618f751.85367722.png', 'http://localhost:3000/index.php', 'product-top', 1, '2025-06-18', '2025-07-18');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `name_slug` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`, `name_slug`) VALUES
(3, 'Nước ngọt', '2025-06-15 22:44:24', NULL),
(4, 'Bim bim', '2025-06-15 22:44:34', NULL),
(5, 'Đồ gia dụng', '2025-06-15 22:44:54', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('đang chờ','đang xử lý','đã hoàn thành','đã hủy') CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci DEFAULT 'đang chờ',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `shipping_address` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `shipping_phone` varchar(20) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `description` text COLLATE utf8mb4_vietnamese_ci,
  `price` decimal(10,2) NOT NULL,
  `stock` int DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `is_deleted` tinyint DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `views` int DEFAULT '0',
  `status` enum('in_stock','out_of_stock') COLLATE utf8mb4_vietnamese_ci DEFAULT 'in_stock',
  `name_slug` varchar(255) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `image`, `category_id`, `is_deleted`, `created_at`, `views`, `status`, `name_slug`) VALUES
(6, 'Thùng nước ngọt Coca Cola 1.5l (12 chai)', 'Hương vị nguyên bản, giảm đường.', 180000.00, 20, 'uploads/684eebce4dd08_618793-28935049502177.webp', 3, 0, '2025-06-15 22:50:38', 0, 'in_stock', NULL),
(7, 'Lốc nước ngọt vị chanh zero calo Pepsi 320ml (6 Lon)', 'Không đường, vị chanh', 62500.00, 20, 'uploads/684eec4feec9e_56200-8934588670114.webp', 3, 0, '2025-06-15 22:52:47', 0, 'in_stock', NULL),
(8, 'Lốc nước ngọt Sprite hương chanh 320ml (6 lon)', 'Hương chanh tự nhiên', 59000.00, 10, 'uploads/684eecdf1a5ac_8935049590262.webp', 3, 0, '2025-06-15 22:55:11', 0, 'in_stock', NULL),
(9, 'Snack', 'Vị tùy chọn', 10000.00, 50, 'uploads/684eed6f68afd_images (1).jpg', 4, 0, '2025-06-15 22:57:35', 0, 'in_stock', NULL),
(10, 'Snack tôm oishi', 'Snack tôm oishi, cay chuẩn vị', 10000.00, 19, 'uploads/684eee0ea029a_images (3).jpg', 4, 0, '2025-06-15 23:00:14', 0, 'in_stock', NULL),
(11, 'Snack bí đỏ vị bò nướng', 'Thơm ngon, giòn rụm.', 10000.00, 20, 'uploads/684eeef057364_images (4).jpg', 4, 0, '2025-06-15 23:04:00', 0, 'in_stock', NULL),
(12, 'Lon bánh khoai tây Lay\'s nhiều vị ngon khó cưỡng', 'Nhiều vị ngon khó cưỡng', 26950.00, 9, 'uploads/684eef6783127_vn-11134207-7ra0g-madb38cyj9w82b@resize_w900_nl.webp', 4, 0, '2025-06-15 23:05:59', 0, 'in_stock', NULL),
(13, 'Robot hút bụi lau nhà Ecovacs Deebot N30 Pro Omni', 'Chổi chống rối tóc công nghệ mới ZeroTangle 2.0\r\nCông nghệ xoè giẻ TruEdge Adaptive Edge Mopping\r\nKhả năng nâng thảm lau 9mm\r\nCông nghệ lau xoay OZMO Turbo 2.0\r\nTương thích Iphone Widget và Apple Watch', 9990000.00, 4, 'uploads/684ef03e00908_ecovacs-deebot-n30-pro-omni-2.webp', 5, 0, '2025-06-15 23:09:34', 0, 'in_stock', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`) VALUES
(14, 6, 'uploads/684eebce50697_18151-370560.webp'),
(15, 8, 'uploads/684eecdf1b8ab_618864-8935049590262.webp'),
(16, 9, 'uploads/684eed6f69e42_images.jpg'),
(17, 9, 'uploads/684eed6f6aed2_4d059aa400fc0e3d89abf78689e04f82.jpg'),
(18, 10, 'uploads/684eee0ea193c_images (2).jpg'),
(19, 10, 'uploads/684eee0ea2968_cc-2.jpg'),
(20, 11, 'uploads/684eeef0591f0_images (5).jpg'),
(21, 12, 'uploads/684eef678451f_vn-11134207-7ra0g-madb38cyhvbs70.webp'),
(22, 12, 'uploads/684eef67856b9_vn-11134207-7ra0g-madb38cyggrc3c.webp'),
(23, 13, 'uploads/684ef03e01a9a_ecovacs-deebot-n30-pro-omni-8.jpg'),
(24, 13, 'uploads/684ef03e02b10_ecovacs-deebot-n30-pro-omni-1.webp'),
(25, 13, 'uploads/684ef03e03b2f_ecovacs-deebot-n30-pro-omni.webp');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotions`
--

CREATE TABLE `promotions` (
  `id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `discount_percent` tinyint NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `apply_all` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `promotions`
--

INSERT INTO `promotions` (`id`, `product_id`, `discount_percent`, `start_date`, `end_date`, `category_id`, `apply_all`) VALUES
(3, NULL, 20, '2025-06-16', '2025-06-30', NULL, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_vietnamese_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_vietnamese_ci,
  `role` enum('customer','admin') COLLATE utf8mb4_vietnamese_ci DEFAULT 'customer',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `phone`, `address`, `role`, `created_at`) VALUES
(5, 'Nguyễn Văn Tâm', 'admin@gmail.com', '$2y$10$z0Wo/H9oR4/rI/Iz2cZeP.hMSnq2iwF..VoxDykqo.3CqVCN3Mcv.', NULL, NULL, 'admin', '2025-06-16 17:53:29'),
(6, 'Tiến', 'tien@gmail.com', '$2y$10$UuEGBN4SzxPAZcjqtzc1DeHIQ/lolnsVgROrgH3mRSk0qlQiecQ3u', NULL, NULL, 'customer', '2025-06-16 17:55:25');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `categories` ADD FULLTEXT KEY `name` (`name`,`name_slug`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);
ALTER TABLE `products` ADD FULLTEXT KEY `name` (`name`,`description`,`name_slug`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ràng buộc đối với các bảng kết xuất
--

--
-- Ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `promotions`
--
ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
