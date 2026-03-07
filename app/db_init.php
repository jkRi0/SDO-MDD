<?php

declare(strict_types=1);

/**
 * Handles automatic database schema creation and updates (seeding)
 */
function sync_database(PDO $pdo): void
{
    // 1. Create or Update Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        fullname VARCHAR(255) NOT NULL,
        role ENUM('admin', 'medical', 'dental') NOT NULL DEFAULT 'medical',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB");

    // Add missing columns if table already exists from old schema
    $columns = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('fullname', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN fullname VARCHAR(255) NOT NULL AFTER password_hash");
    }
    
    if (!in_array('role', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('admin', 'medical', 'dental') NOT NULL DEFAULT 'medical' AFTER fullname");
    }

    // 2. Create Patients Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS patients (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        school VARCHAR(255) NOT NULL,
        level ENUM('Elementary','Secondary','DepEd City Schools Division of Cabuyao') NOT NULL,
        entry_date DATE NOT NULL,
        fullname VARCHAR(255) NOT NULL,
        age INT UNSIGNED NULL,
        sex ENUM('Male','Female','Others') NULL,
        address VARCHAR(255) NULL,
        date_of_birth DATE NULL,
        civil_status VARCHAR(50) NULL,
        designation VARCHAR(255) NULL,
        region VARCHAR(100) NOT NULL,
        division VARCHAR(100) NOT NULL,
        district VARCHAR(100) NULL,
        hmo_provider VARCHAR(100) NULL,
        medical_checked TINYINT(1) NOT NULL DEFAULT 0,
        medical_checked_at DATETIME NULL,
        dental_checked TINYINT(1) NOT NULL DEFAULT 0,
        dental_checked_at DATETIME NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_entry_date (entry_date),
        KEY idx_school (school)
    ) ENGINE=InnoDB");

    // Ensure existing DB supports new level option
    try {
        $pdo->exec("ALTER TABLE patients MODIFY COLUMN level ENUM('Elementary','Secondary','DepEd City Schools Division of Cabuyao') NOT NULL");
    } catch (Throwable $e) {
        // ignore migration failure (e.g., table missing during initial bootstrap)
    }

    // Add assessment columns if missing
    try {
        $patientColumns = $pdo->query("DESCRIBE patients")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('designation', $patientColumns, true)) {
            $pdo->exec("ALTER TABLE patients ADD COLUMN designation VARCHAR(255) NULL AFTER civil_status");
        } else {
            try {
                $pdo->exec("ALTER TABLE patients MODIFY COLUMN designation VARCHAR(255) NULL");
            } catch (Throwable $e) {
                // ignore migration failure
            }
        }
        if (!in_array('medical_checked', $patientColumns, true)) {
            $pdo->exec("ALTER TABLE patients ADD COLUMN medical_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER hmo_provider");
        }
        if (!in_array('medical_checked_at', $patientColumns, true)) {
            $pdo->exec("ALTER TABLE patients ADD COLUMN medical_checked_at DATETIME NULL AFTER medical_checked");
        }
        if (!in_array('dental_checked', $patientColumns, true)) {
            $pdo->exec("ALTER TABLE patients ADD COLUMN dental_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER medical_checked_at");
        }
        if (!in_array('dental_checked_at', $patientColumns, true)) {
            $pdo->exec("ALTER TABLE patients ADD COLUMN dental_checked_at DATETIME NULL AFTER dental_checked");
        }
    } catch (Throwable $e) {
        // ignore migration failure
    }

    // 3. Create Medical Assessments Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS medical_assessments (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        patient_id INT UNSIGNED NOT NULL,
        assessed_by_name VARCHAR(255) NOT NULL,
        license_no VARCHAR(100) NOT NULL,
        height_cm DECIMAL(6,2) NULL,
        weight_kg DECIMAL(6,2) NULL,
        temperature_c DECIMAL(4,1) NULL,
        pulse_rate INT UNSIGNED NULL,
        rr INT UNSIGNED NULL,
        o2_sat INT UNSIGNED NULL,
        bp_systolic INT UNSIGNED NULL,
        bp_diastolic INT UNSIGNED NULL,
        past_medical_history TEXT NULL,
        ob_lmp VARCHAR(50) NULL,
        ob_gtpal VARCHAR(50) NULL,
        ob_chest_xray VARCHAR(100) NULL,
        ob_ecg VARCHAR(100) NULL,
        physical_findings TEXT NULL,
        stress_level TINYINT UNSIGNED NULL,
        coping_level TINYINT UNSIGNED NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_patient_id (patient_id),
        KEY idx_created_at (created_at)
    ) ENGINE=InnoDB");

    // 4. Seed/Update Default Admin
    $stmt = $pdo->prepare("SELECT id, role FROM users WHERE username = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();

    if (!$admin) {
        $hash = password_hash('123', PASSWORD_DEFAULT);
        $ins = $pdo->prepare("INSERT INTO users (username, password_hash, fullname, role) VALUES (?, ?, ?, ?)");
        $ins->execute(['admin', $hash, 'System Administrator', 'admin']);
    } else {
        // Ensure existing admin user has the admin role and a fullname
        $pdo->exec("UPDATE users SET role = 'admin', fullname = 'System Administrator' WHERE username = 'admin' AND (role != 'admin' OR fullname = '')");
    }
}
