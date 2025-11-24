<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order'])) {
    header('Location: ../index.php');
    exit;
}

$orderId = $_GET['order'];

// Get order details
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ../index.php');
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Crochet Online Shop</title>
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
        <div class="confirmation-container">
            <div class="success-message">
                <h1>✅ Order Confirmed!</h1>
                <p>Thank you for your order. Your order has been successfully placed.</p>
            </div>

            <div class="order-details">
                <h2>Order Details</h2>
                <div class="order-info">
                    <p><strong>Order ID:</strong> #<?= $order['id'] ?></p>
                    <p><strong>Order Date:</strong> <?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></p>
                    <p><strong>Status:</strong> <?= ucfirst($order['status']) ?></p>
                    <p><strong>Total Amount:</strong> ₱<?= number_format($order['total_amount'], 2) ?></p>
                </div>

                <h3>Items Ordered</h3>
                <div class="order-items">
                    <?php foreach ($orderItems as $item): ?>
                    <div class="order-item">
                        <span><?= htmlspecialchars($item['name']) ?></span>
                        <span>Qty: <?= $item['quantity'] ?></span>
                        <span>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="shipping-info">
                    <h3>Shipping Address</h3>
                    <p><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                </div>
            </div>

            <div class="next-steps">
                <h3>What's Next?</h3>
                <ul>
                    <li>You will receive an email confirmation shortly</li>
                    <li>We will process your order within 1-2 business days</li>
                    <li>You can track your order status in your account</li>
                </ul>

                <div class="action-buttons">
                    <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
                    <a href="account.php" class="btn btn-secondary">View My Orders</a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Crochet Online Shop. All rights reserved.</p>
        </div>
    </footer>

    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 2rem auto;
        }

        .success-message {
            background: linear-gradient(135deg, #00b894, #00a085);
            color: white;
            padding: 3rem;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 2rem;
        }

        .success-message h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .order-details, .next-steps {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .order-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 2rem;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .shipping-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 2rem;
        }

        .next-steps ul {
            margin: 1rem 0 2rem 2rem;
        }

        .next-steps li {
            margin-bottom: 0.5rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .success-message h1 {
                font-size: 2rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
