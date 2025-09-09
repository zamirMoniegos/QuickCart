<?php

header('Content-Type: application/json');
require_once 'db_connect.php';

$barcode = trim($_POST['barcode'] ?? '');
if ($barcode === '') {
  echo json_encode(['success' => false, 'error' => 'No barcode provided']);
  exit;
}

$stmt = $conn->prepare("SELECT name, price FROM products WHERE barcode = ?");
$stmt->bind_param("s", $barcode);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  echo json_encode([
    'success' => true,
    'name' => $row['name'],
    'price' => $row['price']
  ]);
} else {
  echo json_encode(['success' => false, 'error' => 'Product not found']);
}

$stmt->close();
$conn->close();
?>