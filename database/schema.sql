CREATE DATABASE IF NOT EXISTS sdo_mdd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sdo_mdd;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  fullname VARCHAR(255) NOT NULL,
  role ENUM('admin', 'medical', 'dental') NOT NULL DEFAULT 'medical',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

INSERT IGNORE INTO users (username, password_hash, fullname, role)
VALUES ('admin', '$2y$10$fyN3NjOLtCjZ.1OwzaBskOPbKFVmWR6buqkBXH.0kjVyynEWNLIlu', 'System Administrator', 'admin');

CREATE TABLE IF NOT EXISTS patients (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  school VARCHAR(255) NOT NULL,
  level ENUM('Elementary','Secondary') NOT NULL,
  entry_date DATE NOT NULL,
  fullname VARCHAR(255) NOT NULL,
  age INT UNSIGNED NULL,
  sex ENUM('Male','Female','Others') NULL,
  address VARCHAR(255) NULL,
  contact_number VARCHAR(30) NULL,
  date_of_birth DATE NULL,
  civil_status VARCHAR(50) NULL,
  region VARCHAR(100) NOT NULL,
  division VARCHAR(100) NOT NULL,
  district VARCHAR(100) NULL,
  hmo_provider VARCHAR(100) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_entry_date (entry_date),
  KEY idx_school (school)
) ENGINE=InnoDB;
