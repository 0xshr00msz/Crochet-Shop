<?php
class ShopAlgorithms {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    // ===== GET ALL PRODUCTS WITH FILTERS =====
    public function getProducts($filters = [], $sortBy = 'newest', $page = 1, $perPage = 12) {
        $where = ["p.is_active = 1"];
        $params = [];
        
        // Category filter
        if (!empty($filters['category'])) {
            $where[] = "p.category = ?";
            $params[] = $filters['category'];
        }
        
        // Price range
        if (isset($filters['min_price'])) {
            $where[] = "p.price >= ?";
            $params[] = $filters['min_price'];
        }
        if (isset($filters['max_price'])) {
            $where[] = "p.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        // In stock only
        if (!empty($filters['in_stock'])) {
            $where[] = "p.stock > 0";
        }
        
        // Sort
        $orderBy = match($sortBy) {
            'price_asc' => "p.price ASC",
            'price_desc' => "p.price DESC",
            'name' => "p.name ASC",
            default => "p.created_at DESC"
        };
        
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT p.*, 
                (SELECT filename FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image,
                COALESCE((SELECT AVG(rating) FROM reviews WHERE product_id = p.id AND is_approved = 1), 0) as avg_rating,
                (SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND is_approved = 1) as review_count
                FROM products p 
                WHERE " . implode(" AND ", $where) . "
                ORDER BY {$orderBy}
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // ===== GET FEATURED PRODUCTS =====
    public function getFeaturedProducts($limit = 6) {
        $sql = "SELECT p.*, 
                (SELECT filename FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
                FROM products p 
                WHERE p.is_featured = 1 AND p.is_active = 1 AND p.stock > 0
                ORDER BY p.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }
    
    // ===== GET SINGLE PRODUCT =====
    public function getProduct($id) {
        $sql = "SELECT p.*, 
                COALESCE((SELECT AVG(rating) FROM reviews WHERE product_id = p.id AND is_approved = 1), 0) as avg_rating,
                (SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND is_approved = 1) as review_count
                FROM products p 
                WHERE p.id = ? AND p.is_active = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch();
    }
    
    // ===== GET PRODUCT IMAGES =====
    public function getProductImages($productId) {
        $sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        
        return $stmt->fetchAll();
    }
    
    // ===== ADD TO CART =====
    public function addToCart($userId, $productId, $quantity = 1) {
        // Check product stock
        $product = $this->db->prepare("SELECT stock, name FROM products WHERE id = ? AND is_active = 1");
        $product->execute([$productId]);
        $prod = $product->fetch();
        
        if (!$prod) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        // Check existing cart item
        $existing = $this->db->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $existing->execute([$userId, $productId]);
        $cartItem = $existing->fetch();
        
        $newQuantity = $cartItem ? $cartItem['quantity'] + $quantity : $quantity;
        
        // Validate stock
        if ($newQuantity > $prod['stock']) {
            return ['success' => false, 'message' => "Only {$prod['stock']} items in stock"];
        }
        
        // Insert or update
        if ($cartItem) {
            $stmt = $this->db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$newQuantity, $userId, $productId]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $productId, $quantity]);
        }
        
        return ['success' => true, 'message' => 'Added to cart'];
    }
    
    // ===== GET CART SUMMARY =====
    public function getCartSummary($userId) {
        $sql = "SELECT c.*, p.name, p.price, p.stock,
                (SELECT filename FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
                FROM cart c
                JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ? AND p.is_active = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $items = $stmt->fetchAll();
        
        $subtotal = 0;
        $itemsCount = 0;
        
        foreach ($items as &$item) {
            $item['line_total'] = $item['price'] * $item['quantity'];
            $subtotal += $item['line_total'];
            $itemsCount += $item['quantity'];
        }
        
        return [
            'subtotal' => $subtotal,
            'items_count' => $itemsCount,
            'items' => $items
        ];
    }
    
    // ===== UPDATE CART QUANTITY =====
    public function updateCartQuantity($userId, $productId, $quantity) {
        if ($quantity <= 0) {
            $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            return ['success' => true, 'message' => 'Item removed'];
        }
        
        // Check stock
        $product = $this->db->prepare("SELECT stock FROM products WHERE id = ?");
        $product->execute([$productId]);
        $prod = $product->fetch();
        
        if ($quantity > $prod['stock']) {
            return ['success' => false, 'message' => "Only {$prod['stock']} items available"];
        }
        
        $stmt = $this->db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$quantity, $userId, $productId]);
        
        return ['success' => true, 'message' => 'Cart updated'];
    }
    
    // ===== CHECKOUT =====
    public function checkout($userId, $shippingData, $paymentMethod = 'cod') {
        try {
            $this->db->beginTransaction();
            
            // Get cart
            $cart = $this->getCartSummary($userId);
            
            if (empty($cart['items'])) {
                throw new Exception('Cart is empty');
            }
            
            // Validate stock again
            foreach ($cart['items'] as $item) {
                if ($item['quantity'] > $item['stock']) {
                    throw new Exception("{$item['name']}: Insufficient stock");
                }
            }
            
            // Generate order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            // Create order
            $orderStmt = $this->db->prepare(
                "INSERT INTO orders (
                    user_id, order_number, total_amount, payment_method,
                    shipping_first_name, shipping_last_name, shipping_phone,
                    shipping_address, shipping_city, shipping_province, shipping_postal_code
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            $orderStmt->execute([
                $userId,
                $orderNumber,
                $cart['subtotal'],
                $paymentMethod,
                $shippingData['first_name'],
                $shippingData['last_name'],
                $shippingData['phone'],
                $shippingData['address'],
                $shippingData['city'],
                $shippingData['province'],
                $shippingData['postal_code'] ?? null
            ]);
            
            $orderId = $this->db->lastInsertId();
            
            // Create order items & reduce stock
            $itemStmt = $this->db->prepare(
                "INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)"
            );
            
            $stockStmt = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            
            foreach ($cart['items'] as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['name'],
                    $item['quantity'],
                    $item['price'],
                    $item['line_total']
                ]);
                
                $stockStmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Clear cart
            $this->db->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$userId]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'message' => 'Order placed successfully'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // ===== SEARCH PRODUCTS =====
    public function searchProducts($query, $limit = 20) {
        $searchTerm = "%{$query}%";
        
        $sql = "SELECT p.*, 
                (SELECT filename FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
                FROM products p
                WHERE p.is_active = 1 
                AND (LOWER(p.name) LIKE LOWER(?) OR LOWER(p.description) LIKE LOWER(?))
                ORDER BY p.name ASC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $limit]);
        
        return $stmt->fetchAll();
    }
}
?>