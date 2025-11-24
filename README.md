# 🧶 Crochet Online Shop

Project in Trisha nga gipabuhat sa akoa WAHAHAHAHAHA

A complete PHP e-commerce website for selling handmade crochet products.

## Features

### ✅ User Authentication
- User registration with validation
- Secure login/logout
- Password hashing
- Session management

### ✅ Product Management
- Product catalog with categories
- Product search and filtering
- Featured products display
- Stock management

### ✅ Shopping Cart
- Add/remove items from cart
- Update quantities
- Cart persistence across sessions
- Real-time cart count

### ✅ Order Processing
- Secure checkout process
- Order confirmation
- Order history tracking
- Order status management

## Setup Instructions

1. **Database Setup**
   ```bash
   # Make sure MySQL is running
   php setup.php
   ```

2. **Web Server**
   - Place files in your web server directory (htdocs, www, etc.)
   - Or use PHP built-in server:
   ```bash
   php -S localhost:8000
   ```

3. **Configuration**
   - Update database credentials in `config/database.php` if needed
   - Default: localhost, root, no password, database: crochet_onlineshop

## File Structure

```
crochet_onlineshop/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   └── shopalgorithms.php    # Core shop functionality
├── pages/
│   ├── login.php            # User login
│   ├── register.php         # User registration
│   ├── shop.php             # Product catalog
│   ├── product.php          # Product details
│   ├── cart.php             # Shopping cart
│   ├── checkout.php         # Order checkout
│   ├── account.php          # User account
│   └── logout.php           # User logout
├── css/
│   └── style.css            # Responsive styling
├── uploads/                 # Product images
├── index.php               # Homepage
├── database.sql            # Database schema
└── setup.php              # Database setup script
```

## Usage

1. **Homepage**: Browse featured products
2. **Shop**: View all products with filtering options
3. **Register**: Create a new account
4. **Login**: Access your account
5. **Cart**: Manage your shopping cart
6. **Checkout**: Complete your purchase
7. **Account**: View order history

## Sample Data

The setup includes sample crochet products:
- Cute Teddy Bear (₱450)
- Baby Blanket (₱850)
- Crochet Bag (₱320)
- Winter Scarf (₱280)
- Baby Booties (₱180)
- Flower Pot Cover (₱150)

## Technologies Used

- **Backend**: PHP 8+, MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Security**: Password hashing, prepared statements
- **Design**: Responsive grid layout, mobile-friendly

## Security Features

- SQL injection prevention with prepared statements
- Password hashing with PHP's password_hash()
- Session-based authentication
- Input validation and sanitization
- CSRF protection ready

## Browser Support

- Chrome, Firefox, Safari, Edge
- Mobile responsive design
- Works on tablets and smartphones

---

**Ready to use!** 🚀 The complete crochet online shop with login, register, and shopping functionality is now set up and ready to run.
