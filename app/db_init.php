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
        region VARCHAR(100) NOT NULL,
        division VARCHAR(100) NOT NULL,
        district VARCHAR(100) NULL,
        hmo_provider VARCHAR(100) NULL,
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

    // 3. Seed/Update Default Admin
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
