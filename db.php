<?php
$host = 'localhost';
$user = 'root'; // Default XAMPP user
$pass = '';     // Default XAMPP password
$dbname = 'factory_billing_db';

try {
    // connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If database doesn't exist, we might be running setup, so connection to just host is needed
    try {
         $pdo = new PDO("mysql:host=$host", $user, $pass);
         $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e2) {
        die("Connection failed: " . $e2->getMessage());
    }
}
?>
