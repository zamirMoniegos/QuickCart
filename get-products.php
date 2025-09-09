<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$result = $conn->query("SELECT id, barcode, name, price FROM products ORDER BY id DESC");

$products = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode(['data' => $products]);

$conn->close();
?>