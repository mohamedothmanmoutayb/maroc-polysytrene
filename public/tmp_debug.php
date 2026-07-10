<?php
// Temp debug script - REMOVE AFTER USE
if (!isset($_GET['key']) || $_GET['key'] !== 'debug2026') {
    die('Access denied');
}

$host = '127.0.0.1';
$db = 'admdeyfm_maroc_polysytrene';
$user = 'admdeyfm_mohamed_othman';
$pass = 'PWmyphF{+7v@J-2+';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT order_id, order_number, product_id, source_product_id, required_quantity, quantity_to_produce, additional_data FROM production_orders WHERE order_number IN ('PO-202606-0111', 'PO-202606-0110') ORDER BY order_id");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>";
    foreach ($orders as $order) {
        echo "Order: " . $order['order_number'] . " (id=" . $order['order_id'] . ")\n";
        echo "  product_id: " . $order['product_id'] . "\n";
        echo "  source_product_id: " . $order['source_product_id'] . "\n";
        echo "  required_quantity: " . $order['required_quantity'] . "\n";
        echo "  quantity_to_produce: " . $order['quantity_to_produce'] . "\n";
        $ad = json_decode($order['additional_data'], true);
        echo "  additional_data.source_products: " . json_encode($ad['source_products'] ?? 'MISSING') . "\n";
        echo "  additional_data.products_count: " . ($ad['products_count'] ?? 'MISSING') . "\n";
        echo "\n";
    }

    $stmt2 = $pdo->prepare("SELECT pop.production_order_id, pop.product_id, pop.quantity_to_produce, p.product_name FROM production_order_products pop JOIN products p ON p.product_id = pop.product_id WHERE pop.production_order_id IN (148, 149) ORDER BY pop.production_order_id, pop.id");
    $stmt2->execute();
    $products = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo "production_order_products:\n";
    foreach ($products as $p) {
        echo "  order_id=" . $p['production_order_id'] . " product_id=" . $p['product_id'] . " qty=" . $p['quantity_to_produce'] . " name=" . $p['product_name'] . "\n";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
