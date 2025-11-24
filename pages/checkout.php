<?php
session_start();
require_once '../config/database.php';
require_once '../includes/shopalgorithms.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$shop = new ShopAlgorithms($pdo);
$cartItems = $shop->getCartItems($_SESSION['user_id']);

if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

$total = array_sum(array_column($cartItems, 'subtotal'));
$error = '';
$success = '';

if ($_POST) {
    $address = trim($_POST['address']);
    
    if (empty($address)) {
        $error = 'Please provide a shipping address';
    } else {
        try {
            $orderId = $shop->createOrder($_SESSION['user_id'], $address);
            $_SESSION['cart_count'] = 0;
            header('Location: order-confirmation.php?order=' . $orderId);
            exit;
        } catch (Exception $e) {
            $error = 'Order failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Crochet Online Shop</title>
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
        <h1>🛒 Checkout</h1>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="checkout-container">
            <div class="order-summary">
                <h2>Order Summary</h2>
                <?php foreach ($cartItems as $item): ?>
                <div class="order-item">
                    <span><?= htmlspecialchars($item['name']) ?></span>
                    <span>Qty: <?= $item['quantity'] ?></span>
                    <span>₱<?= number_format($item['subtotal'], 2) ?></span>
                </div>
                <?php endforeach; ?>
                <div class="order-total">
                    <strong>Total: ₱<?= number_format($total, 2) ?></strong>
                </div>
            </div>

            <div class="checkout-form">
                <h2>Shipping Information</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="address">Shipping Address *</label>
                        <textarea id="address" name="address" rows="4" required placeholder="Enter your complete shipping address"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                    </div>

                    <div class="form-actions">
                        <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                        <button type="submit" class="btn btn-primary">Place Order</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Crochet Online Shop. All rights reserved.</p>
        </div>
    </footer>

    <style>
        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        .order-summary, .checkout-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .order-total {
            padding: 1rem 0;
            font-size: 1.2rem;
            text-align: right;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
