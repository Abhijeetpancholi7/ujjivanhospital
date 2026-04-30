CREATE DATABASE IF NOT EXISTS clinic
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE clinic;

CREATE TABLE IF NOT EXISTS indoor_patient_register (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  yearly_no VARCHAR(30) NULL,
  monthly_no VARCHAR(30) NULL,
  admission_date DATE NOT NULL,
  admission_time TIME NULL,
  employee_no VARCHAR(50) NULL,
  patient_name VARCHAR(150) NOT NULL,
  address TEXT NULL,
  age TINYINT UNSIGNED NOT NULL,
  sex VARCHAR(10) NOT NULL,
  diagnosis_complaints TEXT NOT NULL,
  ent_pvt VARCHAR(30) NULL,
  nonent VARCHAR(30) NULL,
  dod DATE NULL,
  staff_nurse VARCHAR(120) NULL,
  doctor_name VARCHAR(120) NULL,
  remarks TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_admission_date (admission_date),
  INDEX idx_patient_name (patient_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
