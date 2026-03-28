<?php
require_once 'config.php';
requireLogin();

$data = json_decode(file_get_contents('php://input'), true);
$cart = $data['cart'];
$customer = $data['customer'] ?? '';
$payment = $data['payment'] ?? 'cash';

if (empty($cart)) {
    echo json_encode(['success' => false, 'error' => 'Cart is empty']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Generate invoice number
    $invoice = 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $tax = $subtotal * 0.1;
    $total = $subtotal + $tax;

    // Insert sale
    $stmt = $pdo->prepare("INSERT INTO sales (invoice_number, user_id, customer_name, subtotal, tax, total_amount, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'paid')");
    $stmt->execute([$invoice, $_SESSION['user_id'], $customer, $subtotal, $tax, $total, $payment]);
    $sale_id = $pdo->lastInsertId();

    // Insert sale items and update stock
    foreach ($cart as $item) {
        $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$sale_id, $item['id'], $item['quantity'], $item['price'], $item['price'] * $item['quantity']]);

        $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['id']]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'invoice' => $invoice]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
