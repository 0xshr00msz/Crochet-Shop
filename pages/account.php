<?php
session_start();
require_once '../config/database.php';
require_once '../includes/shopalgorithms.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$shop = new ShopAlgorithms($pdo);

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get user orders
$orders = $shop->getUserOrders($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Crochet Online Shop</title>
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
        <h1>👤 My Account</h1>

        <div class="account-container">
            <div class="user-info">
                <h2>Account Information</h2>
                <div class="info-card">
                    <p><strong>Name:</strong> <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <?php if ($user['phone']): ?>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                    <?php endif; ?>
                    <p><strong>Member since:</strong> <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
                </div>
            </div>

            <div class="order-history">
                <h2>Order History</h2>
                <?php if (empty($orders)): ?>
                    <div class="no-orders">
                        <p>You haven't placed any orders yet.</p>
                        <a href="shop.php" class="btn btn-primary">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <h3>Order #<?= $order['id'] ?></h3>
                                <span class="order-status status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span>
                            </div>
                            <div class="order-details">
                                <p><strong>Date:</strong> <?= date('F j, Y', strtotime($order['created_at'])) ?></p>
                                <p><strong>Total:</strong> ₱<?= number_format($order['total_amount'], 2) ?></p>
                                <p><strong>Status:</strong> <?= ucfirst($order['status']) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Crochet Online Shop. All rights reserved.</p>
        </div>
    </footer>

    <style>
        .account-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        .user-info, .order-history {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .info-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
        }

        .info-card p {
            margin-bottom: 0.5rem;
        }

        .order-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .order-status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .status-pending {
            background: #ffeaa7;
            color: #d63031;
        }

        .status-processing {
            background: #74b9ff;
            color: white;
        }

        .status-shipped {
            background: #fd79a8;
            color: white;
        }

        .status-delivered {
            background: #00b894;
            color: white;
        }

        .status-cancelled {
            background: #ff7675;
            color: white;
        }

        .no-orders {
            text-align: center;
            padding: 3rem;
        }

        @media (max-width: 768px) {
            .account-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
