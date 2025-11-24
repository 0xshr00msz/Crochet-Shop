<?php
session_start();
require_once '../config/database.php';
require_once '../includes/ShopAlgorithms.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$shop = new ShopAlgorithms($pdo);

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_quantity'])) {
        $productId = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        $result = $shop->updateCartQuantity($_SESSION['user_id'], $productId, $quantity);
        $_SESSION['message'] = $result['message'];
        header('Location: cart.php');
        exit;
    }
    
    if (isset($_POST['remove_item'])) {
        $productId = $_POST['product_id'];
        $result = $shop->updateCartQuantity($_SESSION['user_id'], $productId, 0);
        $_SESSION['message'] = 'Item removed from cart';
        header('Location: cart.php');
        exit;
    }
}

$cart = $shop->getCartSummary($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Crochet Online Shop</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .cart-container {
            padding: 3rem 0;
        }
        .cart-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .cart-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .cart-table th {
            background: #ff6b9d;
            color: white;
            padding: 1rem;
            text-align: left;
        }
        .cart-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
        .quantity-input {
            width: 60px;
            padding: 0.5rem;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .cart-summary {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 2rem;
            max-width: 400px;
            margin-left: auto;
        }
        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
        }
        .remove-btn:hover {
            background: #c82333;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <h1><a href="../index.php" style="color: white; text-decoration: none;">🧶 Crochet Online Shop</a></h1>
            <nav>
                <a href="../index.php">Home</a>
                <a href="shop.php">Shop</a>
                <a href="cart.php">Cart</a>
                <a href="account.php">My Account</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container cart-container">
        <h2>🛒 Shopping Cart</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert-success">
                <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (!empty($cart['items'])): ?>
            <div class="cart-table">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart['items'] as $item): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <?php if ($item['image']): ?>
                                        <img src="../uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-img">
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                                        <p style="color: #999; font-size: 0.9rem;">Stock: <?= $item['stock'] ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>₱<?= number_format($item['price'], 2) ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" class="quantity-input">
                                    <button type="submit" name="update_quantity" class="btn btn-secondary" style="padding: 0.5rem;">Update</button>
                                </form>
                            </td>
                            <td><strong>₱<?= number_format($item['line_total'], 2) ?></strong></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                    <button type="submit" name="remove_item" class="remove-btn" onclick="return confirm('Remove this item?')">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Cart Summary -->
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div style="display: flex; justify-content: space-between; margin: 1rem 0;">
                    <span>Subtotal:</span>
                    <strong>₱<?= number_format($cart['subtotal'], 2) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin: 1rem 0;">
                    <span>Total Items:</span>
                    <strong><?= $cart['items_count'] ?></strong>
                </div>
                <hr>
                <div style="display: flex; justify-content: space-between; margin: 1rem 0; font-size: 1.5rem;">
                    <span>Total:</span>
                    <strong style="color: #ff6b9d;">₱<?= number_format($cart['subtotal'], 2) ?></strong>
                </div>
                <a href="checkout.php" class="btn btn-primary" style="width: 100%; text-align: center; display: block; margin-top: 1rem;">
                    Proceed to Checkout →
                </a>
            </div>

        <?php else: ?>
            <div style="text-align: center; padding: 5rem 0;">
                <h3>Your cart is empty 🛒</h3>
                <p style="color: #999; margin: 1rem 0;">Start shopping and add items to your cart!</p>
                <a href="shop.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 Crochet Online Shop. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>