-- =====================================================================
-- MatchIn v2 — Schema
-- Sistem Pendukung Keputusan Partner Tugas Mahasiswa Teknik Informatika
-- & Ilmu Komputer (Metode SAW - Simple Additive Weighting)
-- =====================================================================
-- Rewritten from a messy production export: IDs re-sequenced, orphaned
-- data removed, `skills` normalized against a `skill_options` catalog,
-- unused `course_semester` table dropped (app already has a fallback
-- for its absence, see EnrollmentService::courseSemesterTableExists),
-- and proper FOREIGN KEY constraints added (the old dump had none).
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- Table: users
-- ---------------------------------------------------------------------
CREATE TABLE `users` (
  `id_user` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `student_id` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `major` ENUM('Teknik Informatika','Ilmu Komputer','Teknik Komputer') NOT NULL,
  `semester` TINYINT(4) NOT NULL DEFAULT 1,
  `photo` LONGBLOB DEFAULT NULL,
  `photo_mime` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `student_id` (`student_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: skill_options  (katalog master skill — sumber untuk dropdown
-- "Tambah Skill" & referensi `skills.id_skill_option`)
-- ---------------------------------------------------------------------
CREATE TABLE `skill_options` (
  `id_skill_option` INT(11) NOT NULL AUTO_INCREMENT,
  `skill_name` VARCHAR(150) NOT NULL,
  `category` VARCHAR(80) DEFAULT NULL,
  PRIMARY KEY (`id_skill_option`),
  UNIQUE KEY `unique_skill_name` (`skill_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: skills  (skill milik mahasiswa — kini merujuk ke skill_options,
-- bukan menyimpan teks bebas lagi)
-- ---------------------------------------------------------------------
CREATE TABLE `skills` (
  `id_skill` INT(11) NOT NULL AUTO_INCREMENT,
  `id_user` INT(11) NOT NULL,
  `id_skill_option` INT(11) NOT NULL,
  `level` ENUM('basic','intermediate','expert') NOT NULL DEFAULT 'basic',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_skill`),
  UNIQUE KEY `unique_user_skill` (`id_user`,`id_skill_option`),
  KEY `id_skill_option` (`id_skill_option`),
  CONSTRAINT `fk_skills_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `fk_skills_option` FOREIGN KEY (`id_skill_option`) REFERENCES `skill_options` (`id_skill_option`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: courses  (semester langsung melekat di sini; tabel
-- `course_semester` lama dihapus karena tidak dibutuhkan lagi)
-- ---------------------------------------------------------------------
CREATE TABLE `courses` (
  `id_course` INT(11) NOT NULL AUTO_INCREMENT,
  `course_name` VARCHAR(150) NOT NULL,
  `lecturer` VARCHAR(100) NOT NULL,
  `semester` TINYINT(4) NOT NULL,
  PRIMARY KEY (`id_course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: classes
-- ---------------------------------------------------------------------
CREATE TABLE `classes` (
  `id_class` INT(11) NOT NULL AUTO_INCREMENT,
  `id_course` INT(11) NOT NULL,
  `class_name` VARCHAR(50) NOT NULL,
  `semester` TINYINT(4) NOT NULL,
  PRIMARY KEY (`id_class`),
  UNIQUE KEY `unique_course_class` (`id_course`,`class_name`),
  KEY `id_course` (`id_course`),
  CONSTRAINT `fk_classes_course` FOREIGN KEY (`id_course`) REFERENCES `courses` (`id_course`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: enrollments
-- ---------------------------------------------------------------------
CREATE TABLE `enrollments` (
  `id_enrollment` INT(11) NOT NULL AUTO_INCREMENT,
  `id_user` INT(11) NOT NULL,
  `id_class` INT(11) NOT NULL,
  `enrolled_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_enrollment`),
  UNIQUE KEY `unique_enrollment` (`id_user`,`id_class`),
  KEY `id_class` (`id_class`),
  CONSTRAINT `fk_enrollments_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `fk_enrollments_class` FOREIGN KEY (`id_class`) REFERENCES `classes` (`id_class`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: preferences
-- ---------------------------------------------------------------------
CREATE TABLE `preferences` (
  `id_preference` INT(11) NOT NULL AUTO_INCREMENT,
  `id_user` INT(11) NOT NULL,
  `id_class` INT(11) NOT NULL,
  `preferred_skill` VARCHAR(100) NOT NULL,
  `work_style` ENUM('tim','individu','fleksibel') NOT NULL DEFAULT 'fleksibel',
  PRIMARY KEY (`id_preference`),
  UNIQUE KEY `unique_preference` (`id_user`,`id_class`),
  KEY `id_class` (`id_class`),
  CONSTRAINT `fk_preferences_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `fk_preferences_class` FOREIGN KEY (`id_class`) REFERENCES `classes` (`id_class`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: criteria  (bobot SAW — C1..C4)
-- ---------------------------------------------------------------------
CREATE TABLE `criteria` (
  `id_criteria` INT(11) NOT NULL AUTO_INCREMENT,
  `criteria_name` VARCHAR(100) NOT NULL,
  `criteria_code` VARCHAR(10) NOT NULL,
  `weight` DECIMAL(3,2) NOT NULL,
  PRIMARY KEY (`id_criteria`),
  UNIQUE KEY `criteria_code` (`criteria_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: saw_values  (di-generate otomatis oleh MatchingService::updateSawValues()
-- setiap kali mahasiswa menambah/mengubah skill — tidak perlu diseed)
-- ---------------------------------------------------------------------
CREATE TABLE `saw_values` (
  `id_value` INT(11) NOT NULL AUTO_INCREMENT,
  `id_user` INT(11) NOT NULL,
  `id_criteria` INT(11) NOT NULL,
  `value` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (`id_value`),
  UNIQUE KEY `unique_saw_value` (`id_user`,`id_criteria`),
  KEY `id_criteria` (`id_criteria`),
  CONSTRAINT `fk_saw_values_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `fk_saw_values_criteria` FOREIGN KEY (`id_criteria`) REFERENCES `criteria` (`id_criteria`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: group_recommendation_result  (hasil SAW disimpan otomatis oleh
-- MatchingService::buildMatchingData() setiap klik "Tentukan Rekomendasi"
-- — dikosongkan di seed, akan terisi sendiri saat aplikasi dipakai)
-- ---------------------------------------------------------------------
CREATE TABLE `group_recommendation_result` (
  `id_result` INT(11) NOT NULL AUTO_INCREMENT,
  `id_user` INT(11) NOT NULL,
  `id_class` INT(11) NOT NULL,
  `partner_id` INT(11) NOT NULL,
  `final_score` DECIMAL(10,4) NOT NULL,
  `ranking_position` INT(11) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_result`),
  UNIQUE KEY `unique_user_class_partner` (`id_user`,`id_class`,`partner_id`),
  KEY `id_class` (`id_class`),
  KEY `partner_id` (`partner_id`),
  CONSTRAINT `fk_grr_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `fk_grr_class` FOREIGN KEY (`id_class`) REFERENCES `classes` (`id_class`) ON DELETE CASCADE,
  CONSTRAINT `fk_grr_partner` FOREIGN KEY (`partner_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: system_logs  (dicatat otomatis oleh SystemLogService — dikosongkan
-- di seed; id_user nullable supaya log tetap ada walau akun dihapus)
-- ---------------------------------------------------------------------
CREATE TABLE `system_logs` (
  `id_log` INT(11) NOT NULL AUTO_INCREMENT,
  `id_user` INT(11) DEFAULT NULL,
  `action` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `fk_logs_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
