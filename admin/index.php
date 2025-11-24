<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$productCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$orderCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'");
$userCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'delivered'");
$totalRevenue = $stmt->fetchColumn() ?: 0;

// Recent orders
$stmt = $pdo->query("SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
$recentOrders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Crochet Online Shop</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            font-size: 2rem;
            color: #e84393;
            margin-bottom: 0.5rem;
        }
        table {
            width: 100%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>🧶 Admin Panel</h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="products.php">Products</a>
                <a href="orders.php">Orders</a>
                <a href="users.php">Users</a>
                <a href="../index.php">View Site</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <h2>Dashboard</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= $productCount ?></h3>
                <p>Products</p>
            </div>
            <div class="stat-card">
                <h3><?= $orderCount ?></h3>
                <p>Orders</p>
            </div>
            <div class="stat-card">
                <h3><?= $userCount ?></h3>
                <p>Users</p>
            </div>
            <div class="stat-card">
                <h3>₱<?= number_format($totalRevenue, 2) ?></h3>
                <p>Revenue</p>
            </div>
        </div>
        
        <section class="recent-orders">
            <h3>Recent Orders</h3>
            <?php if ($recentOrders): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['user_name']) ?></td>
                                <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                <td><?= ucfirst($order['status']) ?></td>
                                <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No orders yet.</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
