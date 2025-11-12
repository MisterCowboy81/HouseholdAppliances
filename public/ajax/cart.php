<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/Cart.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';
$cart = new Cart();

switch ($action) {
    case 'add':
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        
        if ($productId <= 0 || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'اطلاعات نامعتبر']);
            exit;
        }
        
        $result = $cart->addItem($productId, $quantity);
        echo json_encode($result);
        break;
        
    case 'update':
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 0);
        
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'اطلاعات نامعتبر']);
            exit;
        }
        
        $result = $cart->updateQuantity($productId, $quantity);
        echo json_encode($result);
        break;
        
    case 'remove':
        $productId = intval($_POST['product_id'] ?? 0);
        
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'اطلاعات نامعتبر']);
            exit;
        }
        
        $result = $cart->removeItem($productId);
        echo json_encode($result);
        break;
        
    case 'count':
        $count = $cart->getCount();
        echo json_encode(['success' => true, 'count' => $count]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'عملیات نامعتبر']);
}
