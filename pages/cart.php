<?php
session_start();
require_once '../config/database.php';
require_once '../includes/shopalgorithms.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$shop = new ShopAlgorithms($pdo);
$message = '';

// Handle cart actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $productId = $_POST['product_id'];
        $quantity = $_POST['quantity'] ?? 1;
        
        if ($shop->addToCart($_SESSION['user_id'], $productId, $quantity)) {
            $message = 'Product added to cart!';
            $_SESSION['cart_count'] = $shop->getCartCount($_SESSION['user_id']);
        } else {
            $message = 'Failed to add product to cart.';
        }
    } elseif ($action === 'update') {
        $cartId = $_POST['cart_id'];
        $quantity = $_POST['quantity'];
        
        if ($shop->updateCartQuantity($_SESSION['user_id'], $cartId, $quantity)) {
            $message = 'Cart updated!';
            $_SESSION['cart_count'] = $shop->getCartCount($_SESSION['user_id']);
        }
    } elseif ($action === 'remove') {
        $cartId = $_POST['cart_id'];
        
        if ($shop->removeFromCart($_SESSION['user_id'], $cartId)) {
            $message = 'Item removed from cart!';
            $_SESSION['cart_count'] = $shop->getCartCount($_SESSION['user_id']);
        }
    }
}

$cartItems = $shop->getCartItems($_SESSION['user_id']);
$total = array_sum(array_column($cartItems, 'subtotal'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Crochet Online Shop</title>
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
        <h1>🛒 Shopping Cart</h1>

        <?php if ($message): ?>
            <div class="success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <h3>Your cart is empty</h3>
                <p><a href="shop.php" class="btn btn-primary">Continue Shopping</a></p>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <div class="item-image">
                        <?php if ($item['image']): ?>
                            <img src="../uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <?php else: ?>
                            <div class="no-image">📷</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="item-details">
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="price">₱<?= number_format($item['price'], 2) ?></p>
                        <p class="stock">Available: <?= $item['stock'] ?></p>
                    </div>
                    
                    <div class="item-quantity">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>">
                            <button type="submit" class="btn btn-small">Update</button>
                        </form>
                    </div>
                    
                    <div class="item-subtotal">
                        <strong>₱<?= number_format($item['subtotal'], 2) ?></strong>
                    </div>
                    
                    <div class="item-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Remove this item?')">Remove</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="total">
                    <strong>Total: ₱<?= number_format($total, 2) ?></strong>
                </div>
                <div class="checkout-actions">
                    <a href="shop.php" class="btn btn-secondary">Continue Shopping</a>
                    <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 Crochet Online Shop. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
