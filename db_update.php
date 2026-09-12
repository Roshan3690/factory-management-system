<?php
require 'db.php';

try {
    // Add job_name column
    try {
        $pdo->exec("ALTER TABLE works ADD COLUMN job_name VARCHAR(255) DEFAULT NULL AFTER id");
        echo "Added 'job_name' column.<br>";
    } catch (PDOException $e) {
        echo "Column 'job_name' might already exist or error: " . $e->getMessage() . "<br>";
    }

    // Add category column
    try {
        $pdo->exec("ALTER TABLE works ADD COLUMN category VARCHAR(100) DEFAULT 'General' AFTER job_name");
        echo "Added 'category' column.<br>";
    } catch (PDOException $e) {
        echo "Column 'category' might already exist or error: " . $e->getMessage() . "<br>";
    }

    echo "Database update completed.";

} catch (PDOException $e) {
    die("Update failed: " . $e->getMessage());
}
?>
