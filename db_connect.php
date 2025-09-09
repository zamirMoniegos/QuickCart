<?php

// --- IMPORTANT: REPLACE WITH YOUR ACTUAL DATABASE CREDENTIALS ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kopistone_db"; // The database containing the 'products' table

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // For a real application, you might want to log this error instead of echoing it.
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for full Unicode support
$conn->set_charset("utf8mb4");
?>