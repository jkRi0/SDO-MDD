-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 09, 2026 at 03:07 AM
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

--
-- Dumping data for table `dental_assessments`
--

INSERT INTO `dental_assessments` (`id`, `patient_id`, `assessed_by_name`, `license_no`, `mh_allergy`, `mh_asthma`, `mh_bleeding_problem`, `mh_heart_ailment`, `mh_diabetes`, `mh_epilepsy`, `mh_kidney_disease`, `mh_convulsion`, `mh_fainting`, `mh_others`, `exam_date`, `age_last_birthday`, `debris`, `gingiva_inflammation`, `calculus`, `orthodontic_treatment`, `occlusion`, `tmj_exam`, `tooth_chart_json`, `teeth_present_count`, `d_count`, `m_count`, `f_count`, `dmft_total`, `soft_tissue_exam`, `perio_gingival_inflammation`, `perio_soft_plaque`, `perio_hard_calc`, `perio_stains`, `home_care_effectiveness`, `periodontal_condition`, `periodontal_diagnosis`, `periodontitis`, `recommendations_json`, `recommendation_others`, `created_at`) VALUES
(1, 3, 'den1', 'asdlkml123', 0, 0, 0, 0, 0, 0, 0, 0, 0, 'sample', NULL, NULL, 0, 0, 0, 0, NULL, NULL, '{\"12\":\"✓\",\"11\":\"D\",\"21\":\"M\",\"22\":\"F\",\"23\":\"X\",\"48\":\"IMP\",\"47\":\"S\",\"46\":\"XO\",\"45\":\"MO\",\"44\":\"AM\"}', 10, 1, 2, 2, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', NULL, '2026-03-07 19:09:46'),
(2, 1, 'den1', 'qwe4123', 1, 0, 0, 0, 1, 0, 0, 0, 1, 'qwerty', NULL, NULL, 1, 1, 0, 0, 'Class 1', 'Popping', '{\"13\":\"✓\",\"12\":\"D\",\"11\":\"M\",\"21\":\"F\",\"22\":\"X\",\"43\":\"✓\",\"42\":\"✓\",\"41\":\"AM\"}', 8, 1, 2, 2, 5, 'Tongue', 'Moderate', 'Slight', 'Heavy', 'Moderate', 'Good', 'Fair', 'Gingivitis', 'Moderate', '[\"Caries Free\",\"Gingival Inflammation\",\"No Dental Treatment Needed at Present\",\"For Endodontic Treatment\",\"Indicated for Extraction\"]', 'okay', '2026-03-08 09:23:50'),
(3, 1, 'den1', 'qwe4123', 1, 0, 0, 0, 1, 0, 0, 0, 1, 'qwerty', NULL, NULL, 1, 1, 0, 0, 'Class 1', 'Popping', '{\"13\":\"✓\",\"12\":\"D\",\"11\":\"M\",\"21\":\"F\",\"22\":\"X\",\"43\":\"✓\",\"42\":\"✓\",\"41\":\"AM\"}', 8, 1, 2, 2, 5, 'Tongue', 'Moderate', 'Slight', 'Heavy', 'Moderate', 'Good', 'Fair', 'Gingivitis', 'Moderate', '[\"Caries Free\",\"Gingival Inflammation\",\"No Dental Treatment Needed at Present\",\"For Endodontic Treatment\",\"Indicated for Extraction\"]', 'okay', '2026-03-08 09:23:50'),
(4, 1, 'den1', 'qwe4123', 1, 0, 0, 0, 1, 0, 0, 0, 1, 'qwerty', '2026-03-06', 20, 1, 1, 0, 0, 'Class 1', 'Popping', '{\"13\":\"✓\",\"12\":\"D\",\"11\":\"M\",\"21\":\"F\",\"22\":\"X\",\"43\":\"✓\",\"42\":\"✓\",\"41\":\"AM\"}', 8, 1, 2, 2, 5, 'Tongue', 'Moderate', 'Slight', 'Heavy', 'Moderate', 'Good', 'Fair', 'Gingivitis', 'Moderate', '[\"Caries Free\",\"Gingival Inflammation\",\"No Dental Treatment Needed at Present\",\"For Endodontic Treatment\",\"Indicated for Extraction\"]', 'okay', '2026-03-08 10:00:29');

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

--
-- Dumping data for table `medical_assessments`
--

INSERT INTO `medical_assessments` (`id`, `patient_id`, `assessed_by_name`, `license_no`, `height_cm`, `weight_kg`, `bmi_value`, `bmi_category`, `bmi_percentile`, `temperature_c`, `pulse_rate`, `rr`, `o2_sat`, `bp_systolic`, `bp_diastolic`, `past_medical_history`, `ob_lmp`, `ob_gtpal`, `ob_chest_xray`, `ob_ecg`, `physical_findings`, `stress_level`, `coping_level`, `created_at`) VALUES
(1, 1, 'sample1', 'io09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[],\"cancer_type\":\"\",\"operation\":\"\",\"confinement\":\"\",\"others\":\"\"}', NULL, NULL, NULL, NULL, NULL, 1, 1, '2026-03-07 00:54:19'),
(2, 2, 'sample1', 'asd12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[],\"cancer_type\":\"\",\"operation\":\"\",\"confinement\":\"\",\"others\":\"\"}', NULL, NULL, NULL, NULL, NULL, 1, 1, '2026-03-07 02:30:47'),
(3, 6, 'sample1', 'as', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[],\"cancer_type\":\"\",\"operation\":\"\",\"confinement\":\"\",\"others\":\"\"}', NULL, NULL, NULL, NULL, NULL, 3, 1, '2026-03-07 02:31:34'),
(4, 3, 'sample1', 'asd123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[],\"cancer_type\":\"\",\"operation\":\"\",\"confinement\":\"\",\"others\":\"\"}', NULL, NULL, NULL, NULL, NULL, 2, 3, '2026-03-07 02:34:40'),
(5, 7, 'sample1', 'asd2134', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[],\"cancer_type\":\"\",\"operation\":\"\",\"confinement\":\"\",\"others\":\"\"}', NULL, NULL, NULL, NULL, NULL, 4, 4, '2026-03-07 02:38:27'),
(6, 8, 'sample1', 'sd12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[],\"cancer_type\":\"\",\"operation\":\"\",\"confinement\":\"\",\"others\":\"\"}', NULL, NULL, NULL, NULL, NULL, 4, 4, '2026-03-07 02:39:41'),
(7, 8, 'sample1', 'sd12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[],\"cancer_type\":\"\",\"operation\":\"\",\"confinement\":\"\",\"others\":\"\"}', NULL, NULL, NULL, NULL, 'sample', 4, 4, '2026-03-07 04:46:41'),
(8, 8, 'sample1', 'sd12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[],\"cancer_type\":\"\",\"operation\":\"\",\"confinement\":\"\",\"others\":\"okay\"}', NULL, NULL, NULL, NULL, 'sample', 4, 4, '2026-03-07 04:46:54'),
(9, 8, 'sample1', 'sd12', 89.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[],\"cancer_type\":\"\",\"operation\":\"\",\"confinement\":\"\",\"others\":\"okay\"}', NULL, NULL, NULL, NULL, 'sample', 4, 4, '2026-03-07 04:47:19'),
(10, 3, 'sample1', 'asd123', 182.00, 65.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[],\"cancer_type\":\"\",\"operation\":\"\",\"confinement\":\"\",\"others\":\"\"}', NULL, NULL, NULL, NULL, NULL, 2, 3, '2026-03-07 19:38:36'),
(11, 3, 'sample1', 'asd123', 182.00, 65.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[],\"cancer_type\":\"\",\"operation\":\"\",\"confinement\":\"\",\"others\":\"\"}', NULL, NULL, NULL, NULL, NULL, 2, 3, '2026-03-08 01:40:47'),
(12, 3, 'sample1', 'asd123', 182.00, 65.00, 19.62, 'Healthy weight', 27.5, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[],\"cancer_type\":\"\",\"operation\":\"\",\"confinement\":\"\",\"others\":\"\"}', NULL, NULL, NULL, NULL, NULL, 2, 3, '2026-03-08 01:58:07'),
(13, 7, 'sample1', 'asd2134', 182.00, 65.00, 19.62, 'Normal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[],\"cancer_type\":\"\",\"operation\":\"\",\"confinement\":\"\",\"others\":\"\"}', NULL, NULL, NULL, NULL, NULL, 4, 4, '2026-03-08 01:58:42'),
(14, 1, 'sample1', 'io09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[],\"cancer_type\":\"\",\"operation\":\"\",\"confinement\":\"\",\"others\":\"\"}', NULL, NULL, NULL, NULL, NULL, 1, 3, '2026-03-08 08:07:10'),
(15, 3, 'sample1', 'asd123', 182.00, 65.00, 19.62, 'Healthy weight', 27.5, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[\"HPN\",\"Allergies\",\"Lung Dse.\",\"Operation\"],\"cancer_type\":\"\",\"operation\":\"asdqwe\",\"confinement\":\"\",\"others\":\"qwert\"}', NULL, NULL, NULL, NULL, NULL, 2, 3, '2026-03-08 09:36:00'),
(16, 1, 'sample1', 'io09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[\"DM\",\"Lung Dse.\",\"Operation\"],\"cancer_type\":\"\",\"operation\":\"sample\",\"confinement\":\"\",\"others\":\"okay\"}', NULL, NULL, NULL, NULL, NULL, 1, 3, '2026-03-08 11:27:47'),
(17, 1, 'sample1', 'io09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"checked\":[\"DM\",\"Lung Dse.\",\"Operation\",\"Allergies\"],\"allergies\":\"uio\",\"cancer_type\":\"\",\"operation\":\"sample\",\"confinement\":\"\",\"others\":\"okay\"}', NULL, NULL, NULL, NULL, NULL, 1, 3, '2026-03-08 15:07:29'),
(18, 1, 'sample1', 'io09', 180.00, 60.00, 18.52, 'Normal', NULL, 42.0, 56, 100, 34, 100, 200, '{\"checked\":[\"DM\",\"Heart Dse.\",\"Lung Dse.\",\"Cancer\",\"Operation\",\"Allergies\",\"Confinement\"],\"allergies\":\"uio\",\"cancer_type\":\"bbhjk jkhkjh\",\"operation\":\"sample\",\"confinement\":\"bjhhjj ggh\",\"others\":\"okay\"}', 'bbhjhk', 'n,nm', ',mni', 'nnjm', 'okay', 1, 3, '2026-03-09 01:31:46'),
(19, 1, 'sample1', 'io09', 180.00, 60.00, 18.52, 'Normal', NULL, 42.0, 56, 100, 34, 100, 200, '{\"checked\":[\"DM\",\"Heart Dse.\",\"Lung Dse.\",\"Cancer\",\"Operation\",\"Allergies\",\"Confinement\"],\"allergies\":\"uio\",\"cancer_type\":\"bbhjk jkhkjh\",\"operation\":\"sample\",\"confinement\":\"bjhhjj ggh\",\"others\":\"okay\"}', 'bbhjhk', 'n,nm', ',mni', 'nnjm', 'okay', 1, 3, '2026-03-09 02:04:21');

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

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `school`, `level`, `entry_date`, `fullname`, `age`, `sex`, `address`, `contact_number`, `date_of_birth`, `civil_status`, `designation`, `region`, `division`, `district`, `hmo_provider`, `medical_checked`, `medical_checked_at`, `dental_checked`, `dental_checked_at`, `created_at`) VALUES
(1, 'Mamatid National High School', 'Secondary', '2026-03-07', 'asdqwe, qwe asd', 21, 'Male', '123asd', '098892', '2026-03-13', 'Single', 'Teacher I (Elementary)', 'IV-A CALABARZON', 'Cabuyao City', 'District 2', 'kljk', 1, '2026-03-09 10:04:21', 1, '2026-03-08 18:00:29', '2026-03-06 23:33:31'),
(2, 'Banay-Banay Elementary School', 'Elementary', '2026-03-07', '123', 12, 'Male', 'as a d', NULL, '2026-03-12', 'Married', NULL, 'IV-A CALABARZON', 'Cabuyao City', NULL, NULL, 1, '2026-03-07 10:30:47', 1, '2026-03-07 10:30:08', '2026-03-06 23:41:40'),
(3, 'Butong Elementary School', 'Elementary', '2026-03-07', 'asdqweq1233', 18, 'Female', 'asdq123', NULL, '2026-03-20', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', NULL, NULL, 1, '2026-03-08 17:36:00', 1, '2026-03-08 03:09:46', '2026-03-06 23:46:08'),
(7, 'Pittland Integrated School', 'Elementary', '2026-03-07', 'QWE', 21, 'Others', 'adsaw12', NULL, '2026-03-19', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', NULL, NULL, 1, '2026-03-08 09:58:42', 0, NULL, '2026-03-07 02:28:32'),
(8, 'Butong Elementary School', 'Elementary', '2026-03-07', 'ASD', 123, 'Others', 'qwe', NULL, '2026-03-26', 'Single', NULL, 'IV-A CALABARZON', 'Cabuyao City', NULL, NULL, 1, '2026-03-07 12:47:19', 0, NULL, '2026-03-07 02:38:05'),
(9, 'Pulo Elementary School', 'Elementary', '2026-03-07', 'ASDFGG', 8, 'Female', 'sdfgh', '989889', '2026-03-21', 'Widowed', 'School Principal IV', 'IV-A CALABARZON', 'Cabuyao City', NULL, NULL, 0, NULL, 0, NULL, '2026-03-07 04:41:02'),
(10, 'Banlic Elementary School', 'Elementary', '2026-03-07', 'ASD', 123, 'Others', 'asdqe', '234422222222222', '2026-03-26', 'Single', 'Teacher II (Elementary)', 'IV-A CALABARZON', 'Cabuyao City', NULL, NULL, 0, NULL, 0, NULL, '2026-03-07 04:42:55');

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `medical_assessments`
--
ALTER TABLE `medical_assessments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
