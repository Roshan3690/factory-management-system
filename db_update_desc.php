<?php
require 'db.php';

try {
    // Modify description column to allow NULL
    $pdo->exec("ALTER TABLE works MODIFY COLUMN description VARCHAR(255) NULL");
    echo "Updated 'description' column to allow NULL details.<br>";

} catch (PDOException $e) {
    die("Update failed: " . $e->getMessage());
}
?>
