<?php
require_once 'db_connect.php';
session_start();

header('Content-Type: application/json');

// Ensure an admin is logged in to perform actions
if (!isset($_SESSION["admin_logged_in"])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

// Use prepared statements for all operations
try {
    switch ($action) {
        case 'add':
            $stmt = $conn->prepare("INSERT INTO products (barcode, name, price) VALUES (?, ?, ?)");
            $stmt->bind_param("ssd", $_POST['barcode'], $_POST['name'], $_POST['price']);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Product added successfully!'];
            } else {
                // Check for duplicate entry error
                if ($conn->errno == 1062) {
                     $response['message'] = 'Error: A product with this barcode already exists.';
                } else {
                     $response['message'] = 'Error adding product: ' . $stmt->error;
                }
            }
            $stmt->close();
            break;

        case 'update':
            $stmt = $conn->prepare("UPDATE products SET barcode = ?, name = ?, price = ? WHERE id = ?");
            $stmt->bind_param("ssdi", $_POST['barcode'], $_POST['name'], $_POST['price'], $_POST['id']);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Product updated successfully!'];
            } else {
                 if ($conn->errno == 1062) {
                     $response['message'] = 'Error: A product with this barcode already exists.';
                } else {
                     $response['message'] = 'Error updating product: ' . $stmt->error;
                }
            }
            $stmt->close();
            break;

        case 'delete':
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $_POST['id']);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Product deleted successfully!'];
            } else {
                $response['message'] = 'Error deleting product: ' . $stmt->error;
            }
            $stmt->close();
            break;

        // New action to find a product by barcode for the admin panel
        case 'find_by_barcode':
            $stmt = $conn->prepare("SELECT id, barcode, name, price FROM products WHERE barcode = ?");
            $stmt->bind_param("s", $_POST['barcode']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($product = $result->fetch_assoc()) {
                $response = ['success' => true, 'product' => $product];
            } else {
                $response = ['success' => false, 'message' => 'No product found with that barcode.'];
            }
            $stmt->close();
            break;
    }
} catch (Exception $e) {
    $response['message'] = 'A server error occurred: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>