<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user orders with items
$stmt = $pdo->prepare("
    SELECT o.*, 
           GROUP_CONCAT(CONCAT(p.name, ' x', oi.quantity) SEPARATOR ', ') as items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Crochet Online Shop</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="../index.php">🧶 Crochet Online Shop</a></h1>
            <nav>
                <a href="../index.php">Home</a>
                <a href="shop.php">Shop</a>
                <a href="cart.php">Cart</a>
                <a href="account.php">My Account</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <h2>My Orders</h2>
        
        <?php if ($orders): ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <h3>Order #<?= $order['id'] ?></h3>
                            <span class="order-status status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                        </div>
                        
                        <div class="order-info">
                            <p><strong>Date:</strong> <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
                            <p><strong>Total:</strong> ₱<?= number_format($order['total'], 2) ?></p>
                            <p><strong>Payment:</strong> <?= ucfirst($order['payment_method']) ?></p>
                            <p><strong>Items:</strong> <?= htmlspecialchars($order['items']) ?></p>
                        </div>
                        
                        <div class="order-actions">
                            <a href="order-confirmation.php?order=<?= $order['id'] ?>" class="btn btn-secondary">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-orders">
                <p>You haven't placed any orders yet.</p>
                <a href="shop.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
