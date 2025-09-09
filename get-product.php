<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false];
$barcode = $_GET['code'] ?? '';

if (!empty($barcode)) {
    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, barcode, name, price FROM products WHERE barcode = ?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($product = $result->fetch_assoc()) {
        $response['success'] = true;
        $response['product'] = $product;
    } else {
        $response['message'] = 'Product not found.';
    }
    $stmt->close();
} else {
    $response['message'] = 'Barcode parameter is missing.';
}

echo json_encode($response);

$conn->close();
?>