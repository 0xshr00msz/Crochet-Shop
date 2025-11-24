<?php
class ShopAlgorithms {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    // Get featured products
    public function getFeaturedProducts($limit = 6) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE is_featured = 1 AND is_active = 1 AND stock > 0 LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    // Get all products with filters
    public function getProducts($filters = [], $sortBy = 'newest', $page = 1, $perPage = 12) {
        $where = ["is_active = 1"];
        $params = [];
        
        if (!empty($filters['category'])) {
            $where[] = "category = ?";
            $params[] = $filters['category'];
        }
        
        if (isset($filters['min_price'])) {
            $where[] = "price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (isset($filters['max_price'])) {
            $where[] = "price <= ?";
            $params[] = $filters['max_price'];
        }
        
        if (!empty($filters['in_stock'])) {
            $where[] = "stock > 0";
        }
        
        $orderBy = match($sortBy) {
            'price_asc' => "price ASC",
            'price_desc' => "price DESC",
            'name' => "name ASC",
            default => "created_at DESC"
        };
        
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM products WHERE " . implode(" AND ", $where) . " ORDER BY " . $orderBy . " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Search products
    public function searchProducts($query) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE (name LIKE ? OR description LIKE ?) AND is_active = 1 ORDER BY name");
        $searchTerm = "%$query%";
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    // Get single product
    public function getProduct($id) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Get categories
    public function getCategories() {
        $stmt = $this->db->query("SELECT DISTINCT category FROM products WHERE is_active = 1");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Add to cart
    public function addToCart($userId, $productId, $quantity = 1) {
        // Check if item already in cart
        $stmt = $this->db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update quantity
            $newQuantity = $existing['quantity'] + $quantity;
            $stmt = $this->db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            return $stmt->execute([$newQuantity, $existing['id']]);
        } else {
            // Insert new item
            $stmt = $this->db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            return $stmt->execute([$userId, $productId, $quantity]);
        }
    }
    
    // Get cart items
    public function getCartItems($userId) {
        $stmt = $this->db->prepare("
            SELECT c.*, p.name, p.price, p.image, p.stock,
                   (c.quantity * p.price) as subtotal
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ? AND p.is_active = 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    // Get cart count
    public function getCartCount($userId) {
        $stmt = $this->db->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() ?: 0;
    }
    
    // Remove from cart
    public function removeFromCart($userId, $cartId) {
        $stmt = $this->db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        return $stmt->execute([$cartId, $userId]);
    }
    
    // Update cart quantity
    public function updateCartQuantity($userId, $cartId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeFromCart($userId, $cartId);
        }
        
        $stmt = $this->db->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        return $stmt->execute([$quantity, $cartId, $userId]);
    }
    
    // Create order
    public function createOrder($userId, $shippingAddress) {
        try {
            $this->db->beginTransaction();
            
            // Get cart items
            $cartItems = $this->getCartItems($userId);
            if (empty($cartItems)) {
                throw new Exception("Cart is empty");
            }
            
            // Calculate total
            $total = array_sum(array_column($cartItems, 'subtotal'));
            
            // Generate order number
            $orderNumber = 'ORD' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Create order
            $stmt = $this->db->prepare("INSERT INTO orders (user_id, order_number, total_amount, shipping_first_name, shipping_last_name, shipping_phone, shipping_address, shipping_city, shipping_province) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $orderNumber, $total, 'Customer', 'Name', '0000000000', $shippingAddress, 'City', 'Province']);
            $orderId = $this->db->lastInsertId();
            
            // Add order items
            foreach ($cartItems as $item) {
                $stmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$orderId, $item['product_id'], $item['name'], $item['quantity'], $item['price'], $item['subtotal']]);
                
                // Update stock
                $stmt = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Clear cart
            $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            $this->db->commit();
            return $orderId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    // Get user orders
    public function getUserOrders($userId) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
?>
