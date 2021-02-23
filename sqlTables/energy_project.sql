-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2021 at 08:52 AM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `energy_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `ideas`
--

CREATE TABLE `ideas` (
  `idea_id` int(11) NOT NULL,
  `ownerId` int(11) NOT NULL,
  `idea_name` varchar(60) CHARACTER SET utf8 NOT NULL,
  `expertId` int(11) DEFAULT NULL,
  `ideaStatus` enum('START','firstLevel','secondLevel','thirdLevel') NOT NULL DEFAULT 'START',
  `description` mediumtext CHARACTER SET utf8 NOT NULL,
  `extraResources` varchar(255) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=armscii8;

-- --------------------------------------------------------

--
-- Table structure for table `patents`
--

CREATE TABLE `patents` (
  `patent_id` int(11) NOT NULL,
  `patent_name` varchar(100) COLLATE utf8mb4_persian_ci NOT NULL,
  `ownerId` int(11) NOT NULL,
  `expertId` int(11) DEFAULT NULL,
  `patentStatus` enum('START','firstLevel','secondLevel','thirdLevel') COLLATE utf8mb4_persian_ci NOT NULL DEFAULT 'START',
  `description` mediumtext COLLATE utf8mb4_persian_ci NOT NULL,
  `extraResources` varchar(255) COLLATE utf8mb4_persian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `accountId` int(11) NOT NULL,
  `phoneNum` varchar(11) CHARACTER SET armscii8 NOT NULL,
  `email` varchar(60) CHARACTER SET armscii8 DEFAULT NULL,
  `password` varchar(255) CHARACTER SET armscii8 NOT NULL,
  `nationalCode` varchar(20) CHARACTER SET armscii8 DEFAULT NULL,
  `residence` varchar(40) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `schoolName` varchar(60) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `address` mediumtext COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `fullname` varchar(60) COLLATE utf8mb4_persian_ci NOT NULL,
  `type` set('Admin','Assistant','Student','') COLLATE utf8mb4_persian_ci NOT NULL DEFAULT 'Student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ideas`
--
ALTER TABLE `ideas`
  ADD PRIMARY KEY (`idea_id`),
  ADD KEY `ownerId` (`ownerId`);

--
-- Indexes for table `patents`
--
ALTER TABLE `patents`
  ADD PRIMARY KEY (`patent_id`),
  ADD KEY `ownerId` (`ownerId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`accountId`),
  ADD UNIQUE KEY `phoneNum` (`phoneNum`),
  ADD UNIQUE KEY `nationalCode` (`nationalCode`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ideas`
--
ALTER TABLE `ideas`
  MODIFY `idea_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `patents`
--
ALTER TABLE `patents`
  MODIFY `patent_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `accountId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ideas`
--
ALTER TABLE `ideas`
  ADD CONSTRAINT `ideas_ibfk_1` FOREIGN KEY (`ownerId`) REFERENCES `users` (`accountId`);

--
-- Constraints for table `patents`
--
ALTER TABLE `patents`
  ADD CONSTRAINT `patents_ibfk_1` FOREIGN KEY (`ownerId`) REFERENCES `users` (`accountId`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
