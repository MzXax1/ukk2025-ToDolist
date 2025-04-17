-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 15 Apr 2025 pada 07.13
-- Versi server: 8.4.3
-- Versi PHP: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `to_do_list`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `projects`
--

CREATE TABLE `projects` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `slug` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `projects`
--

INSERT INTO `projects` (`id`, `user_id`, `name`, `created_at`, `slug`) VALUES
(81, 1, 'Project Beta 1', '2025-04-13 22:30:00', 'project-beta'),
(86, 1, 'Project  SpaceX', '2025-04-15 05:49:25', NULL),
(87, 4, 'cek', '2025-04-15 06:44:36', NULL),
(88, 1, 'mengerjakan matematika', '2025-04-15 06:56:59', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tasks`
--

CREATE TABLE `tasks` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_completed` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `priority` enum('Low','Medium','High') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `user_id`, `name`, `is_completed`, `created_at`, `priority`, `due_date`, `completed_at`) VALUES
(4, 81, 1, 'Write API documentation', 0, '2025-04-04 06:45:00', 'Low', '2025-04-25', NULL),
(5, 81, 1, 'Test payment gateway', 1, '2025-04-05 01:20:00', 'Medium', '2025-04-18', NULL),
(6, 86, 1, 'Update project roadmap', 0, '2025-04-10 02:00:00', 'Medium', '2025-04-20', NULL),
(7, 86, 1, 'Finalize UI mockups', 1, '2025-04-11 03:30:00', 'High', '2025-04-15', '2025-04-14 17:00:00'),
(8, 86, 1, 'Write unit tests', 0, '2025-04-12 07:00:00', 'Low', '2025-04-22', NULL),
(9, 86, 1, 'Integrate OAuth login', 0, '2025-04-13 04:45:00', 'High', '2025-04-25', NULL),
(10, 86, 1, 'Conduct team meeting', 1, '2025-04-14 01:15:00', 'Medium', '2025-04-18', '2025-04-17 13:30:00'),
(11, 86, 1, 'Refactor task module', 0, '2025-04-15 09:45:00', 'Medium', '2025-04-28', NULL),
(89, 88, 1, 'testing', 0, '2025-04-15 07:13:34', 'High', '2026-02-21', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'users1', 'user@gmail.com', '7c6a180b36896a0a8c02787eeafb0e4c', '2025-01-22 01:41:49'),
(2, 'test', 'test@user.com', 'a722c63db8ec8625af6cf71cb8c2d939', '2025-02-26 00:16:42'),
(4, 'John Doe', 'john.doe@example.com', '7c6a180b36896a0a8c02787eeafb0e4c', '2025-04-13 22:00:00'),
(5, 'Jane Smith', 'jane.smith@example.com', '5f4dcc3b5aa765d61d8327deb882cf99', '2025-04-13 22:05:00'),
(6, 'Michael Brown', 'michael.brown@example.com', '5f4dcc3b5aa765d61d8327deb882cf99', '2025-04-13 22:10:00');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT untuk tabel `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_tasks_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
