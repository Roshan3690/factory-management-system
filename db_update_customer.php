<?php
require 'db.php';

try {
    // Add customer_name column
    try {
        $pdo->exec("ALTER TABLE works ADD COLUMN customer_name VARCHAR(255) DEFAULT NULL AFTER job_name");
        echo "Added 'customer_name' column.<br>";
    } catch (PDOException $e) {
        echo "Column 'customer_name' might already exist or error: " . $e->getMessage() . "<br>";
    }

    echo "Database update completed.";

} catch (PDOException $e) {
    die("Update failed: " . $e->getMessage());
}
?>
