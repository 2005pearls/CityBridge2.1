-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Apr 21, 2026 at 07:59 PM
-- Server version: 8.0.44
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `account_id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`account_id`, `username`, `email`, `password_hash`, `role`) VALUES
(1, 'dhayUser', 'dhay@gmail.com', '$2y$10$Goy4GU0DB8z4NXXlQYs.PeBYz0UeHycJZVnRaQ5oJEk0BcrgZpZ8K', 'user'),
(2, 'aliUser', 'ali@gmail.com', '$2y$10$Goy4GU0DB8z4NXXlQYs.PeBYz0UeHycJZVnRaQ5oJEk0BcrgZpZ8K', 'user'),
(3, 'admin1', 'admin@citybridge.com', '$2y$10$a34PHgX2.V/m5VjydwpvZO0ARn/K9bX3QAlu6s3LRUBCnK/XV65QS', 'admin'),
(4, 'banana', 'jana@gmail.com', '$2y$10$Goy4GU0DB8z4NXXlQYs.PeBYz0UeHycJZVnRaQ5oJEk0BcrgZpZ8K', 'user'),
(5, 'AdamSaif', 'Adam@gmail.com', '$2y$10$/yNe3oGzg/XDdaJtBjVZfOwEzgMhtd5iIOXPfPkHq0TjD3KKWMVNy', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `admin_account`
--

CREATE TABLE `admin_account` (
  `account_id` int NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `role_title` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin_account`
--

INSERT INTO `admin_account` (`account_id`, `admin_name`, `role_title`, `phone_number`) VALUES
(3, 'Wasan Alamri', 'System Administrator', '+966550000999');

-- --------------------------------------------------------

--
-- Table structure for table `attachment`
--

CREATE TABLE `attachment` (
  `attachment_id` int NOT NULL,
  `permit_id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `attachment`
--

INSERT INTO `attachment` (`attachment_id`, `permit_id`, `file_name`, `file_type`, `file_path`, `uploaded_at`) VALUES
(1, 1, 'labor_contract.pdf', 'pdf', 'uploads/labor_contract.pdf', '2026-03-05 10:15:00'),
(2, 2, 'equipment_license.pdf', 'pdf', 'uploads/equipment_license.pdf', '2026-02-28 09:00:00'),
(3, 3, 'medical_certificate.pdf', 'pdf', 'uploads/medical_certificate.pdf', '2026-02-20 11:30:00'),
(4, 4, 'tech_spec.pdf', 'pdf', 'uploads/tech_spec.pdf', '2026-04-18 19:41:58'),
(5, 5, 'medical_certificate.pdf', 'pdf', 'uploads/p5_1776799808.pdf', '2026-04-21 22:30:08');

-- --------------------------------------------------------

--
-- Table structure for table `authority`
--

CREATE TABLE `authority` (
  `authority_id` int NOT NULL,
  `authority_name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `category` enum('labor','equipment','medical','electronic') NOT NULL,
  `website` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `authority`
--

INSERT INTO `authority` (`authority_id`, `authority_name`, `description`, `category`, `website`) VALUES
(1, 'SMASCO (Saudi Manpower Solutions Company)', 'Saudi company specializing in workforce supply and staffing solutions across construction, engineering, and industrial sectors.', 'labor', 'https://www.smasco.com'),
(2, 'Nesma & Partners', 'Major Saudi contractor providing workforce services for infrastructure, construction, and energy projects.', 'labor', 'https://www.nesma.com'),
(3, 'Alfanar', 'Saudi engineering and construction company supplying skilled technical labor and industrial services.', 'labor', 'https://www.alfanar.com'),
(4, 'Saudi Aramco', 'World\'s largest oil company, providing highly skilled technical and engineering workforce across energy and infrastructure projects.', 'labor', 'https://www.aramco.com'),
(5, 'SABIC', 'Global leader in diversified chemicals, supplying industrial labor and technical expertise for petrochemical and manufacturing projects.', 'labor', 'https://www.sabic.com'),
(6, 'Al-Mabani General Contractors', 'Saudi construction company offering large-scale workforce deployment for civil and industrial contracting projects.', 'labor', 'https://www.almabani.com'),
(7, 'Saudi Binladin Group', 'One of the largest construction conglomerates in the Middle East, providing specialized labor for mega-infrastructure developments.', 'labor', 'https://www.sbg.com.sa'),
(8, 'Saudi Oger', 'Major Saudi contractor with a strong workforce base covering civil construction, electrical works, and facility management.', 'labor', 'https://www.saudioger.com'),
(9, 'TAQA Arabia', 'Energy services company supplying trained operational and maintenance labor for utilities and smart city infrastructure.', 'labor', 'https://www.taqa.com'),
(10, 'ManpowerGroup Saudi Arabia', 'Global staffing firm with a strong Saudi presence, connecting businesses with skilled workers in technology, engineering, and operations.', 'labor', 'https://www.manpowergroup.com'),
(11, 'Adecco Saudi Arabia', 'International workforce solutions provider offering recruitment, staffing, and outsourcing services across all major industries.', 'labor', 'https://www.adecco.com'),
(12, 'Al Majdouie Group', 'Diversified Saudi conglomerate providing logistics, industrial labor, and technical workforce solutions for major development initiatives.', 'labor', 'https://www.almajdouie.com'),
(13, 'Caterpillar', 'Global manufacturer of heavy construction machinery widely used in infrastructure and mining projects in Saudi Arabia.', 'equipment', 'https://www.caterpillar.com'),
(14, 'Komatsu', 'Japanese company producing advanced construction and mining equipment used in large-scale projects.', 'equipment', 'https://www.komatsu.com'),
(15, 'Liebherr', 'International company known for heavy cranes and construction machinery used in major development projects.', 'equipment', 'https://www.liebherr.com'),
(16, 'Volvo Construction Equipment', 'Swedish manufacturer of excavators, wheel loaders, and articulated haulers for road and infrastructure construction.', 'equipment', 'https://www.volvoce.com'),
(17, 'Hitachi Construction Machinery', 'Japanese producer of hydraulic excavators and construction machinery suited for large-scale earthmoving operations.', 'equipment', 'https://www.hitachicm.com'),
(18, 'JCB', 'British manufacturer of backhoe loaders, telescopic handlers, and compact construction equipment used across Saudi projects.', 'equipment', 'https://www.jcb.com'),
(19, 'Manitowoc Cranes', 'Leading crane manufacturer supplying tower cranes and crawler cranes for high-rise and industrial construction projects.', 'equipment', 'https://www.manitowoccranes.com'),
(20, 'Terex', 'Global equipment manufacturer providing aerial work platforms, cranes, and material processing machinery for construction sites.', 'equipment', 'https://www.terex.com'),
(21, 'Sandvik Construction', 'Swedish engineering company delivering rock drilling equipment, crushing machinery, and tunneling solutions for Saudi infrastructure.', 'equipment', 'https://www.sandvik.com'),
(22, 'Atlas Copco', 'Swedish industrial company producing compressors, drilling rigs, and power equipment essential for construction and mining.', 'equipment', 'https://www.atlascopco.com'),
(23, 'CASE Construction Equipment', 'American manufacturer of wheel loaders, motor graders, and excavators supporting road and utility infrastructure projects.', 'equipment', 'https://www.casece.com'),
(24, 'Doosan Bobcat', 'Korean-American company producing compact loaders, mini-excavators, and utility vehicles for urban construction environments.', 'equipment', 'https://www.doosanbobcat.com'),
(25, 'Philips Healthcare', 'Global provider of hospital monitoring systems, diagnostic imaging equipment, and healthcare technology.', 'medical', 'https://www.philips.com/healthcare'),
(26, 'Siemens Healthineers', 'Leading company delivering medical imaging equipment and advanced healthcare diagnostic technologies.', 'medical', 'https://www.siemens-healthineers.com'),
(27, 'GE Healthcare', 'Manufacturer of advanced medical equipment including MRI, ultrasound, and clinical diagnostic systems.', 'medical', 'https://www.gehealthcare.com'),
(28, 'Dräger', 'German medical technology company specializing in patient monitoring, anesthesia machines, and hospital safety systems.', 'medical', 'https://www.draeger.com'),
(29, 'Medtronic', 'World\'s largest medical device company, supplying cardiac devices, surgical tools, and remote patient monitoring systems.', 'medical', 'https://www.medtronic.com'),
(30, 'BD (Becton, Dickinson)', 'Global medical technology company manufacturing syringes, diagnostic instruments, and infection prevention products.', 'medical', 'https://www.bd.com'),
(31, 'Abbott Laboratories', 'Healthcare company providing point-of-care diagnostics, glucose monitors, and cardiovascular medical devices.', 'medical', 'https://www.abbott.com'),
(32, 'Stryker', 'Medical technology company specializing in orthopedic implants, surgical equipment, and hospital bed systems.', 'medical', 'https://www.stryker.com'),
(33, 'Roche Diagnostics', 'Swiss company delivering in-vitro diagnostics, laboratory analyzers, and molecular testing systems for clinical use.', 'medical', 'https://www.roche.com'),
(34, 'Zimmer Biomet', 'Musculoskeletal healthcare company producing joint replacement implants, spine products, and surgical enabling technologies.', 'medical', 'https://www.zimmerbiomet.com'),
(35, 'Baxter International', 'Global medical products company providing IV fluids, renal therapies, and hospital nutrition solutions.', 'medical', 'https://www.baxter.com'),
(36, 'Mindray', 'Chinese medical device company supplying patient monitors, ultrasound systems, and in-vitro diagnostic equipment.', 'medical', 'https://www.mindray.com'),
(37, 'Fresenius Medical Care', 'World\'s leading provider of dialysis equipment and renal care products for hospitals and outpatient clinics.', 'medical', 'https://www.freseniusmedicalcare.com'),
(38, 'Cisco', 'Technology company providing networking infrastructure, cybersecurity systems, and smart city connectivity solutions.', 'electronic', 'https://www.cisco.com'),
(39, 'Huawei', 'Global telecommunications company delivering smart city infrastructure and advanced communication technologies.', 'electronic', 'https://www.huawei.com'),
(40, 'Honeywell', 'Industrial technology company producing smart sensors, automation systems, and safety monitoring equipment.', 'electronic', 'https://www.honeywell.com'),
(41, 'Samsung Electronics', 'South Korean multinational providing smart displays, IoT devices, and electronic infrastructure for modern city deployments.', 'electronic', 'https://www.samsung.com'),
(42, 'Schneider Electric', 'French energy management company delivering smart grid technology, building automation, and industrial control systems.', 'electronic', 'https://www.se.com'),
(43, 'ABB', 'Swiss-Swedish multinational providing robotics, power grids, and electrification products for smart infrastructure.', 'electronic', 'https://www.abb.com'),
(44, 'Ericsson', 'Swedish telecommunications company supplying 5G network equipment and connected city communication infrastructure.', 'electronic', 'https://www.ericsson.com'),
(45, 'Nokia', 'Finnish technology company providing end-to-end network solutions, IoT platforms, and smart city digital infrastructure.', 'electronic', 'https://www.nokia.com'),
(46, 'Bosch', 'German engineering company delivering smart home devices, IoT sensors, security systems, and industrial automation solutions.', 'electronic', 'https://www.bosch.com'),
(47, 'Siemens Digital Industries', 'Provider of industrial automation, digital factory software, and smart building management systems for city-scale deployments.', 'electronic', 'https://www.siemens.com/digital-industries'),
(48, 'Intel', 'American semiconductor company supplying processors, edge computing hardware, and AI chips powering smart city platforms.', 'electronic', 'https://www.intel.com'),
(49, 'IBM', 'Technology company offering cloud computing, AI analytics, and IoT integration services for smart city management systems.', 'electronic', 'https://www.ibm.com'),
(50, 'Dell Technologies', 'American technology company providing servers, edge computing solutions, and data center infrastructure for government projects.', 'electronic', 'https://www.dell.com');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `company_id` int NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `sector` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`company_id`, `company_name`, `sector`) VALUES
(1, 'Alsumayt Construction Co.', 'Construction'),
(2, 'Smart Health Solutions', 'Healthcare'),
(3, 'Future Tech IoT', 'Technology'),
(4, 'Test Company', 'Healthcare'),
(5, 'Sabic', 'telecommunications');

-- --------------------------------------------------------

--
-- Table structure for table `electronic_permit`
--

CREATE TABLE `electronic_permit` (
  `permit_id` int NOT NULL,
  `device_type` varchar(100) NOT NULL,
  `device_manufacturer` varchar(150) NOT NULL,
  `device_model` varchar(100) NOT NULL,
  `device_quantity` int NOT NULL,
  `use_type` enum('personal','commercial') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `electronic_permit`
--

INSERT INTO `electronic_permit` (`permit_id`, `device_type`, `device_manufacturer`, `device_model`, `device_quantity`, `use_type`) VALUES
(4, 'IoT Sensor', 'FutureTech', 'FT-9000', 50, 'commercial');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_permit`
--

CREATE TABLE `equipment_permit` (
  `permit_id` int NOT NULL,
  `equipment_type` varchar(100) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `operator_name` varchar(100) NOT NULL,
  `operator_license_number` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `equipment_permit`
--

INSERT INTO `equipment_permit` (`permit_id`, `equipment_type`, `serial_number`, `operator_name`, `operator_license_number`) VALUES
(2, 'Excavator', 'EQ-78231', 'Mohammed Alharbi', 'LIC-20493');

-- --------------------------------------------------------

--
-- Table structure for table `labor_permit`
--

CREATE TABLE `labor_permit` (
  `permit_id` int NOT NULL,
  `number_of_workers` int NOT NULL,
  `job_title` varchar(100) NOT NULL,
  `supervisor_name` varchar(100) NOT NULL,
  `employer_name` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `labor_permit`
--

INSERT INTO `labor_permit` (`permit_id`, `number_of_workers`, `job_title`, `supervisor_name`, `employer_name`) VALUES
(1, 12, 'Site Technician', 'Ahmed Alqahtani', 'Alsumayt Construction Co.');

-- --------------------------------------------------------

--
-- Table structure for table `medical_permit`
--

CREATE TABLE `medical_permit` (
  `permit_id` int NOT NULL,
  `device_name` varchar(150) NOT NULL,
  `manufacturer` varchar(150) NOT NULL,
  `facility_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `medical_permit`
--

INSERT INTO `medical_permit` (`permit_id`, `device_name`, `manufacturer`, `facility_name`) VALUES
(3, 'Portable Monitor', 'MedTech Co.', 'Alsumayt Medical Center'),
(5, 'Portable Patient Monitor', 'MedTech Co.', 'Alsumayt Medical Center');

-- --------------------------------------------------------

--
-- Table structure for table `permit`
--

CREATE TABLE `permit` (
  `permit_id` int NOT NULL,
  `user_account_id` int NOT NULL,
  `permit_type` enum('labor','equipment','medical','electronic') NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `submitted_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_date` datetime DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `reviewed_by_admin_id` int DEFAULT NULL,
  `rejection_reason` text,
  `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `permit`
--

INSERT INTO `permit` (`permit_id`, `user_account_id`, `permit_type`, `status`, `submitted_date`, `reviewed_date`, `approved_date`, `expiry_date`, `reviewed_by_admin_id`, `rejection_reason`, `last_updated`) VALUES
(1, 1, 'labor', 'pending', '2026-03-05 10:15:00', NULL, NULL, NULL, NULL, NULL, '2026-04-21 18:09:43'),
(2, 1, 'equipment', 'approved', '2026-02-28 09:00:00', '2026-02-28 14:00:00', '2026-02-28 14:00:00', '2027-02-28', 3, NULL, '2026-04-21 18:09:43'),
(3, 2, 'medical', 'rejected', '2026-02-20 11:30:00', '2026-02-21 10:00:00', NULL, NULL, 3, 'Device certificate is expired.', '2026-04-21 18:09:43'),
(4, 2, 'electronic', 'approved', '2026-04-18 19:41:58', '2026-04-18 20:00:00', '2026-04-18 20:00:00', '2026-10-18', 3, NULL, '2026-04-21 18:09:43'),
(5, 4, 'medical', 'approved', '2026-04-21 22:30:08', '2026-04-21 22:46:38', '2026-04-21 22:46:38', '2027-04-21', 3, NULL, '2026-04-21 22:46:38');

-- --------------------------------------------------------

--
-- Table structure for table `safety_guideline`
--

CREATE TABLE `safety_guideline` (
  `guideline_id` int NOT NULL,
  `category` enum('general','labor','equipment','medical','electronic') NOT NULL,
  `level` enum('required','important','info') NOT NULL,
  `rule_label` varchar(50) DEFAULT NULL,
  `rule_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `safety_guideline`
--

INSERT INTO `safety_guideline` (`guideline_id`, `category`, `level`, `rule_label`, `rule_text`) VALUES
(1, 'general', 'required', 'Age', 'Applicant must be 21 years of age or older.'),
(2, 'general', 'required', 'Identity', 'A valid national ID or passport must be submitted and must not be expired.'),
(3, 'general', 'required', 'Documents', 'All submitted documents must be in English or accompanied by an official certified translation.'),
(4, 'general', 'required', 'Legal', 'Applicant must have no pending legal violations or outstanding fines within the Smart City jurisdiction.'),
(5, 'general', 'important', 'Legal', 'Falsifying any information in the application will result in a permanent ban and referral for legal action.'),
(6, 'general', 'important', 'Validity', 'Renewal must be initiated before the permit expiry date. Operating with an expired permit is a violation.'),
(7, 'general', 'info', 'Processing', 'Processing times vary by permit type. Incomplete applications will not be processed.'),
(8, 'labor', 'required', 'Age', 'Workers under 18 years of age are strictly prohibited from being listed on this permit.'),
(9, 'labor', 'required', 'Documents', 'Proof of employment or a labor contract signed by both parties.'),
(10, 'labor', 'required', 'Legal', 'Employer must be a registered legal entity within the Smart City jurisdiction.'),
(11, 'labor', 'info', 'Validity', 'Permit is valid for 12 months. Renewal must be initiated at least 30 days before expiry.'),
(12, 'labor', 'info', 'Processing', 'Standard processing time is 5–7 business days after a complete submission.'),
(13, 'equipment', 'required', 'Age', 'Primary operator must be at least 21 years old and hold a valid certified operator license.'),
(14, 'equipment', 'required', 'Documents', 'Equipment registration certificate and valid insurance documents.'),
(15, 'equipment', 'required', 'Legal', 'Third-party liability insurance must be active before permit issuance.'),
(16, 'equipment', 'important', 'Legal', 'Unauthorized equipment operation on-site will result in immediate confiscation and permit revocation.'),
(17, 'equipment', 'info', 'Validity', 'Permit is valid for the project duration, maximum 24 months. Extensions require full re-application.'),
(18, 'equipment', 'info', 'Processing', 'Standard processing time is 7–10 business days after submission.'),
(19, 'medical', 'required', 'Documents', 'Facility license for the premises where the device will be stored and operated.'),
(20, 'medical', 'important', 'Documents', 'Import clearance certificate is required if the device is sourced internationally.'),
(21, 'medical', 'required', 'Legal', 'Device must carry a Certification and approval from the National Medical Devices Authority prior to any use.'),
(22, 'medical', 'info', 'Validity', 'Permit is valid for 24 months. An annual compliance audit is required to maintain active standing.'),
(23, 'medical', 'info', 'Processing', 'Processing time is 10–14 business days due to mandatory regulatory review.'),
(24, 'electronic', 'required', 'Documents', 'Device technical specification sheet including frequency band and power output documentation.'),
(25, 'electronic', 'required', 'Legal', 'Device must operate within approved frequency ranges and must not cause interference to other systems.'),
(26, 'electronic', 'required', 'Legal', 'Any device collecting personal or location data must comply with the Smart City Data Privacy Act.'),
(27, 'electronic', 'important', 'Legal', 'Devices used for surveillance require a separate Security Permit before deployment.'),
(28, 'electronic', 'info', 'Validity', 'Permit is valid for 12 months. Any hardware or firmware modification immediately voids the permit.'),
(29, 'electronic', 'info', 'Processing', 'Standard processing time is 3–5 business days after a complete submission.');

-- --------------------------------------------------------

--
-- Table structure for table `user_account`
--

CREATE TABLE `user_account` (
  `account_id` int NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `company_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_account`
--

INSERT INTO `user_account` (`account_id`, `first_name`, `last_name`, `phone_number`, `job_title`, `company_id`) VALUES
(1, 'Dhay', 'Alsumayt', '+966550000001', 'Site Manager', 1),
(2, 'Ali', 'Alharbi', '+966550000002', 'Engineer', 3),
(4, 'Jana', 'Test', '0653678230', 'Developer', 4),
(5, 'Adam', 'Al-saif', '+96635524335', 'Marketing Manager', 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_account`
--
ALTER TABLE `admin_account`
  ADD PRIMARY KEY (`account_id`);

--
-- Indexes for table `attachment`
--
ALTER TABLE `attachment`
  ADD PRIMARY KEY (`attachment_id`),
  ADD KEY `permit_id` (`permit_id`);

--
-- Indexes for table `authority`
--
ALTER TABLE `authority`
  ADD PRIMARY KEY (`authority_id`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`company_id`);

--
-- Indexes for table `electronic_permit`
--
ALTER TABLE `electronic_permit`
  ADD PRIMARY KEY (`permit_id`);

--
-- Indexes for table `equipment_permit`
--
ALTER TABLE `equipment_permit`
  ADD PRIMARY KEY (`permit_id`);

--
-- Indexes for table `labor_permit`
--
ALTER TABLE `labor_permit`
  ADD PRIMARY KEY (`permit_id`);

--
-- Indexes for table `medical_permit`
--
ALTER TABLE `medical_permit`
  ADD PRIMARY KEY (`permit_id`);

--
-- Indexes for table `permit`
--
ALTER TABLE `permit`
  ADD PRIMARY KEY (`permit_id`),
  ADD KEY `user_account_id` (`user_account_id`),
  ADD KEY `reviewed_by_admin_id` (`reviewed_by_admin_id`);

--
-- Indexes for table `safety_guideline`
--
ALTER TABLE `safety_guideline`
  ADD PRIMARY KEY (`guideline_id`);

--
-- Indexes for table `user_account`
--
ALTER TABLE `user_account`
  ADD PRIMARY KEY (`account_id`),
  ADD KEY `company_id` (`company_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `account_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `attachment`
--
ALTER TABLE `attachment`
  MODIFY `attachment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `authority`
--
ALTER TABLE `authority`
  MODIFY `authority_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `company_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `permit`
--
ALTER TABLE `permit`
  MODIFY `permit_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `safety_guideline`
--
ALTER TABLE `safety_guideline`
  MODIFY `guideline_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_account`
--
ALTER TABLE `admin_account`
  ADD CONSTRAINT `admin_account_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attachment`
--
ALTER TABLE `attachment`
  ADD CONSTRAINT `attachment_ibfk_1` FOREIGN KEY (`permit_id`) REFERENCES `permit` (`permit_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `electronic_permit`
--
ALTER TABLE `electronic_permit`
  ADD CONSTRAINT `electronic_permit_ibfk_1` FOREIGN KEY (`permit_id`) REFERENCES `permit` (`permit_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `equipment_permit`
--
ALTER TABLE `equipment_permit`
  ADD CONSTRAINT `equipment_permit_ibfk_1` FOREIGN KEY (`permit_id`) REFERENCES `permit` (`permit_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `labor_permit`
--
ALTER TABLE `labor_permit`
  ADD CONSTRAINT `labor_permit_ibfk_1` FOREIGN KEY (`permit_id`) REFERENCES `permit` (`permit_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `medical_permit`
--
ALTER TABLE `medical_permit`
  ADD CONSTRAINT `medical_permit_ibfk_1` FOREIGN KEY (`permit_id`) REFERENCES `permit` (`permit_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `permit`
--
ALTER TABLE `permit`
  ADD CONSTRAINT `permit_ibfk_1` FOREIGN KEY (`user_account_id`) REFERENCES `user_account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permit_ibfk_2` FOREIGN KEY (`reviewed_by_admin_id`) REFERENCES `admin_account` (`account_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `user_account`
--
ALTER TABLE `user_account`
  ADD CONSTRAINT `user_account_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_account_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
