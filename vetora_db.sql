-- phpMyAdmin SQL Dump
-- Passwords now use bcrypt hashes (password_hash with PASSWORD_DEFAULT)
-- Default password for both accounts: admin123

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- Database: `vetora_db`

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `apt_date` date DEFAULT NULL,
  `apt_time` time DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_patient` (`patient_id`),
  KEY `fk_doctor` (`doctor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `apt_date`, `apt_time`, `status`) VALUES
(1,  1,  1, '2026-04-14', '09:00:00', 'Recovering'),
(2,  2,  2, '2026-04-14', '10:30:00', 'Scheduled'),
(3,  3,  1, '2026-04-14', '13:00:00', 'Confirmed'),
(4,  4,  2, '2026-04-15', '09:00:00', 'Needs Attention'),
(5,  5,  3, '2026-04-15', '11:00:00', 'Recovering'),
(6,  6,  3, '2026-04-15', '14:30:00', 'Scheduled'),
(7,  7,  1, '2026-04-16', '08:30:00', 'Confirmed'),
(8,  8,  4, '2026-04-16', '10:00:00', 'Scheduled'),
(9,  9,  4, '2026-04-16', '13:00:00', 'Recovering'),
(10,10,  5, '2026-04-17', '09:00:00', 'Confirmed'),
(11,11,  2, '2026-04-17', '11:30:00', 'Needs Attention'),
(12,12,  1, '2026-04-17', '15:00:00', 'Scheduled'),
(13,13,  3, '2026-04-18', '10:00:00', 'Confirmed'),
(14,14,  4, '2026-04-18', '14:00:00', 'Recovering'),
(15,15,  5, '2026-04-19', '09:30:00', 'Scheduled'),
(16,16,  2, '2026-04-19', '11:00:00', 'Confirmed'),
(17,17,  1, '2026-04-20', '08:00:00', 'Needs Attention'),
(18,18,  5, '2026-04-20', '13:30:00', 'Scheduled'),
(19,19,  3, '2026-04-21', '10:00:00', 'Recovering'),
(20,20,  4, '2026-04-21', '15:00:00', 'Confirmed');

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `practice_hours` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- FIX: practice_hours filled in for all doctors (previously NULL)
INSERT INTO `doctors` (`id`, `name`, `specialization`, `practice_hours`, `status`, `image_url`) VALUES
(1, 'Dr. James Wilson',    'Feline Specialist', 'Mon-Fri 08:00-16:00', 'Active', NULL),
(2, 'Dr. Sarah Miller',    'Canine Specialist', 'Mon-Fri 09:00-17:00', 'Active', NULL),
(3, 'Dr. Elena Rodriguez', 'Exotic Pets',       'Tue-Sat 10:00-18:00', 'Active', NULL),
(4, 'Dr. Henry Choi',      'Surgeon',           'Mon-Thu 07:00-15:00', 'Active', NULL),
(5, 'Dr. Maria Garcia',    'Dermatologist',     'Mon-Fri 09:00-17:00', 'Active', NULL),
(7, 'Dr. Radella Hafiza',  'Rabies',            'Mon-Fri 09:00-17:00', 'Active', NULL),
(8, 'Terry',               'Rabies',            'Mon-Fri 09:00-17:00', 'Active', 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=100&h=100&fit=crop');

CREATE TABLE `drugs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `price` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `drugs` (`id`, `name`, `stock`, `price`) VALUES
(1, 'Amoxiclav 250mg',    45,  85000.00),
(2, 'NexGard Spectra',    20, 180000.00),
(3, 'Bravecto Chewable',  15, 210000.00),
(4, 'Ear Drops (Otic)',   30,  55000.00),
(5, 'Cough Syrup Pet',    25,  45000.00),
(6, 'Vitamin Booster Gold',100,125000.00),
(7, 'Flea & Tick Spray',  40,  95000.00);

CREATE TABLE `medical_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `record_date` date DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `therapy` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `medical_records` (`id`, `patient_id`, `doctor_id`, `record_date`, `diagnosis`, `therapy`) VALUES
(1, 1, 1, '2026-04-08', 'Mild joint pain',    'Rest for 3 days, joint supplements'),
(2, 2, 2, '2026-04-10', 'Annual Vet Checkup', 'All clear, vaccines updated'),
(3, 3, 1, '2026-04-11', 'Skin Allergy',       'Antihistamine and special shampoo'),
(4, 4, 2, '2026-04-12', 'Digestive issues',   'Probiotics and wet food diet'),
(5, 5, 3, '2026-04-13', 'Ear Infection',      'Clean ears twice daily, antibiotic drops'),
(6, 6, 3, '2026-04-14', 'Flea Infestation',   'Flea treatment and prevention spray'),
(7, 7, 1, '2026-04-15', 'Weight Management',  'Reduce kibble portion, increase activity');

CREATE TABLE `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `addr` text DEFAULT NULL,
  `allergy` text DEFAULT NULL,
  `blood_type` varchar(10) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `patients` (`id`, `name`, `phone`, `addr`, `allergy`, `blood_type`, `dob`, `gender`) VALUES
(1,  'Luna',     '0812001', 'Jakarta',   'Fish',     'A',  '2021-03-22', 'Female'),
(2,  'Bruno',    '0812002', 'Bandung',   'None',     'O',  '2020-06-15', 'Male'),
(3,  'Mochi',    '0812003', 'Surabaya',  'Chicken',  'B',  '2022-01-10', 'Female'),
(4,  'Kopi',     '0812004', 'Bekasi',    'None',     'O',  '2019-11-05', 'Male'),
(5,  'Snowball', '0812005', 'Tangerang', 'Dust',     'A',  '2022-11-08', 'Female'),
(6,  'Chiki',    '0812006', 'Depok',     'None',     'AB', '2023-05-12', 'Male'),
(7,  'Oyen',     '0812007', 'Jakarta',   'Seafood',  'B',  '2021-08-20', 'Male'),
(8,  'Cookie',   '0812008', 'Bogor',     'None',     'O',  '2020-12-01', 'Female'),
(9,  'Bento',    '0812009', 'Semarang',  'Beef',     'A',  '2021-05-14', 'Male'),
(10, 'Milo',     '0812010', 'Yogyakarta','None',     'B',  '2022-03-30', 'Male'),
(11, 'Bella',    '0812011', 'Malang',    'Pollens',  'O',  '2019-07-25', 'Female'),
(12, 'Simba',    '0812012', 'Medan',     'None',     'A',  '2020-02-11', 'Male'),
(13, 'Coco',     '0812013', 'Palembang', 'Eggs',     'AB', '2021-09-09', 'Female'),
(14, 'Rex',      '0812014', 'Makassar',  'None',     'B',  '2018-04-18', 'Male'),
(15, 'Lulu',     '0812015', 'Bali',      'None',     'O',  '2023-01-05', 'Female'),
(16, 'Teddy',    '0812016', 'Solo',      'Dairy',    'A',  '2022-06-20', 'Male'),
(17, 'Zoe',      '0812017', 'Jakarta',   'None',     'B',  '2021-11-30', 'Female'),
(18, 'Oliver',   '0812018', 'Bandung',   'None',     'O',  '2020-08-14', 'Male'),
(19, 'Cleo',     '0812019', 'Surabaya',  'None',     'A',  '2022-02-28', 'Female'),
(20, 'Max',      '0812020', 'Bekasi',    'Dust',     'AB', '2019-12-25', 'Male');

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `service_name` text DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `transaction_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `transactions` (`id`, `patient_id`, `service_name`, `amount`, `transaction_date`) VALUES
(1, 1, 'General Consultation', 150000.00, '2026-04-08'),
(2, 2, 'Vaccination',          250000.00, '2026-04-10'),
(3, 3, 'Skin Treatment',       300000.00, '2026-04-11'),
(4, 4, 'Lab Test',             450000.00, '2026-04-12'),
(5, 5, 'Ear Cleaning',         100000.00, '2026-04-13'),
(6, 6, 'Flea Removal',         200000.00, '2026-04-14'),
(7, 7, 'Nutrition Consulting', 125000.00, '2026-04-15');

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- FIX: Passwords hashed with bcrypt (password_hash('admin123', PASSWORD_DEFAULT))
-- Original plain-text password: admin123
INSERT INTO `users` (`id`, `email`, `password`, `role`, `name`, `image_url`) VALUES
(1, 'admin@kiyowo.id',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',  'Kiyowo Pet Admin', NULL),
(2, 'doctor@kiyowo.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', 'Dr. Paws',         NULL);

-- Foreign Key Constraints
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_doctor`  FOREIGN KEY (`doctor_id`)  REFERENCES `doctors`  (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`doctor_id`)  REFERENCES `doctors`  (`id`) ON DELETE CASCADE;

ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

COMMIT;
