<?php
require 'db.php';

try {
    // 1. Create Database if strictly not exists (handled by initial connection somewhat, but let's be explicit)
    $pdo->exec("CREATE DATABASE IF NOT EXISTS factory_billing_db");
    $pdo->exec("USE factory_billing_db");

    // 2. Create 'works' table
    $sql_works = "CREATE TABLE IF NOT EXISTS works (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        description VARCHAR(255) NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        work_date DATE NOT NULL,
        status ENUM('Pending', 'Done') DEFAULT 'Pending',
        payment_status ENUM('Pending', 'Paid') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql_works);
    echo "Table 'works' created or already exists.<br>";

    // 3. Create 'invoices' table (optional, for keeping track of generated invoices)
    $sql_invoices = "CREATE TABLE IF NOT EXISTS invoices (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        invoice_number VARCHAR(50) NOT NULL UNIQUE,
        generated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        total_amount DECIMAL(10, 2) NOT NULL,
        work_ids TEXT -- Comma separated IDs of works included
    )";
    $pdo->exec($sql_invoices);
    echo "Table 'invoices' created or already exists.<br>";

    echo "Database setup completed successfully!";

} catch (PDOException $e) {
    die("Setup failed: " . $e->getMessage());
}
?>
