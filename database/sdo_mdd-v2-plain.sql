-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 09, 2026 at 03:08 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sdo_mdd`
--

-- --------------------------------------------------------

--
-- Table structure for table `dental_assessments`
--

CREATE TABLE `dental_assessments` (
  `id` int(10) UNSIGNED NOT NULL,
  `patient_id` int(10) UNSIGNED NOT NULL,
  `assessed_by_name` varchar(255) NOT NULL,
  `license_no` varchar(100) NOT NULL,
  `mh_allergy` tinyint(1) NOT NULL DEFAULT 0,
  `mh_asthma` tinyint(1) NOT NULL DEFAULT 0,
  `mh_bleeding_problem` tinyint(1) NOT NULL DEFAULT 0,
  `mh_heart_ailment` tinyint(1) NOT NULL DEFAULT 0,
  `mh_diabetes` tinyint(1) NOT NULL DEFAULT 0,
  `mh_epilepsy` tinyint(1) NOT NULL DEFAULT 0,
  `mh_kidney_disease` tinyint(1) NOT NULL DEFAULT 0,
  `mh_convulsion` tinyint(1) NOT NULL DEFAULT 0,
  `mh_fainting` tinyint(1) NOT NULL DEFAULT 0,
  `mh_others` text DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `age_last_birthday` int(10) UNSIGNED DEFAULT NULL,
  `debris` tinyint(1) NOT NULL DEFAULT 0,
  `gingiva_inflammation` tinyint(1) NOT NULL DEFAULT 0,
  `calculus` tinyint(1) NOT NULL DEFAULT 0,
  `orthodontic_treatment` tinyint(1) NOT NULL DEFAULT 0,
  `occlusion` enum('Class 1','Class 2','Class 3') DEFAULT NULL,
  `tmj_exam` enum('Pain','Popping','Deviation','Tooth wear') DEFAULT NULL,
  `tooth_chart_json` mediumtext DEFAULT NULL,
  `teeth_present_count` int(10) UNSIGNED DEFAULT NULL,
  `d_count` int(10) UNSIGNED DEFAULT NULL,
  `m_count` int(10) UNSIGNED DEFAULT NULL,
  `f_count` int(10) UNSIGNED DEFAULT NULL,
  `dmft_total` int(10) UNSIGNED DEFAULT NULL,
  `soft_tissue_exam` enum('Lips','Floor of mouth','Palate','Tongue','Neck & nodes') DEFAULT NULL,
  `perio_gingival_inflammation` enum('Slight','Moderate','Severe') DEFAULT NULL,
  `perio_soft_plaque` enum('Slight','Moderate','Heavy') DEFAULT NULL,
  `perio_hard_calc` enum('Light','Moderate','Heavy') DEFAULT NULL,
  `perio_stains` enum('Light','Moderate','Heavy') DEFAULT NULL,
  `home_care_effectiveness` enum('Good','Fair','Poor') DEFAULT NULL,
  `periodontal_condition` enum('Good','Fair','Poor') DEFAULT NULL,
  `periodontal_diagnosis` enum('Normal','Gingivitis') DEFAULT NULL,
  `periodontitis` enum('Early','Moderate','Advanced') DEFAULT NULL,
  `recommendations_json` text DEFAULT NULL,
  `recommendation_others` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_assessments`
--

CREATE TABLE `medical_assessments` (
  `id` int(10) UNSIGNED NOT NULL,
  `patient_id` int(10) UNSIGNED NOT NULL,
  `assessed_by_name` varchar(255) NOT NULL,
  `license_no` varchar(100) NOT NULL,
  `height_cm` decimal(6,2) DEFAULT NULL,
  `weight_kg` decimal(6,2) DEFAULT NULL,
  `bmi_value` decimal(6,2) DEFAULT NULL,
  `bmi_category` varchar(60) DEFAULT NULL,
  `bmi_percentile` decimal(5,1) DEFAULT NULL,
  `temperature_c` decimal(4,1) DEFAULT NULL,
  `pulse_rate` int(10) UNSIGNED DEFAULT NULL,
  `rr` int(10) UNSIGNED DEFAULT NULL,
  `o2_sat` int(10) UNSIGNED DEFAULT NULL,
  `bp_systolic` int(10) UNSIGNED DEFAULT NULL,
  `bp_diastolic` int(10) UNSIGNED DEFAULT NULL,
  `past_medical_history` text DEFAULT NULL,
  `ob_lmp` varchar(50) DEFAULT NULL,
  `ob_gtpal` varchar(50) DEFAULT NULL,
  `ob_chest_xray` varchar(100) DEFAULT NULL,
  `ob_ecg` varchar(100) DEFAULT NULL,
  `physical_findings` text DEFAULT NULL,
  `stress_level` tinyint(3) UNSIGNED DEFAULT NULL,
  `coping_level` tinyint(3) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(10) UNSIGNED NOT NULL,
  `school` varchar(255) NOT NULL,
  `level` enum('Elementary','Secondary','DepEd City Schools Division of Cabuyao') NOT NULL,
  `entry_date` date NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `age` int(10) UNSIGNED DEFAULT NULL,
  `sex` enum('Male','Female','Others') DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `region` varchar(100) NOT NULL,
  `division` varchar(100) NOT NULL,
  `district` varchar(100) DEFAULT NULL,
  `hmo_provider` varchar(100) DEFAULT NULL,
  `medical_checked` tinyint(1) NOT NULL DEFAULT 0,
  `medical_checked_at` datetime DEFAULT NULL,
  `dental_checked` tinyint(1) NOT NULL DEFAULT 0,
  `dental_checked_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `role` enum('admin','medical','dental') NOT NULL DEFAULT 'medical',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `fullname`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$fyN3NjOLtCjZ.1OwzaBskOPbKFVmWR6buqkBXH.0kjVyynEWNLIlu', 'System Administrator', 'admin', '2026-03-06 06:13:10'),
(2, 'medsample1', '$2y$10$UA.KPlsoLabcDGsM9ll7r.h/5JYua95oKaAJi014vlwsRzRM9p3E6', 'med1', 'medical', '2026-03-06 07:10:46'),
(3, 'densample1', '$2y$10$ulc07JtO4sO6.92XjzTUM.xWHffVxxUUQkozg0LnFQhCTcYt4U2oG', 'den1', 'dental', '2026-03-06 07:11:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dental_assessments`
--
ALTER TABLE `dental_assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient_id` (`patient_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `medical_assessments`
--
ALTER TABLE `medical_assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient_id` (`patient_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_entry_date` (`entry_date`),
  ADD KEY `idx_school` (`school`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dental_assessments`
--
ALTER TABLE `dental_assessments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_assessments`
--
ALTER TABLE `medical_assessments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
