-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 20, 2026 at 09:52 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `room_reservation_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `room_number` varchar(20) NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `building` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `status` enum('Available','Under Maintenance','Inactive') NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_name`, `room_number`, `room_type`, `building`, `capacity`, `status`) VALUES
(1, 'Computer Lab 1', 'CL-101', 'Computer Laboratory', 'IT Building', 40, 'Available'),
(2, 'Science Lab 2', 'SL-202', 'Science Laboratory', 'Science Building', 30, 'Available'),
(3, 'Room 301', 'R-301', 'Classroom', 'Main Building', 50, 'Available'),
(4, 'Conference Room A', 'CR-A', 'Conference Room', 'Admin Building', 20, 'Under Maintenance'),
(5, 'CD Lab', '123', 'Science Laboratory', '2B Building', 40, 'Available'),
(6, 'CD Lab 2', '203', 'Classroom', 'MPH Building', 25, 'Available');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `uq_room` (`room_number`,`building`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
