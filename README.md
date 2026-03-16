# Stickza - E-commerce Sticker Shop

A full-featured e-commerce platform for a sticker shop, built from scratch with PHP and MySQL. Features a complete shopping experience with user authentication, cart management, order processing, and a dedicated admin panel.

---

## Features

### Customer Features
- **Product Catalog** - Browse stickers by category (Cute & Animals, Retro, Nature & Travel, Meme, Cartoon)
- **Product Search** - Find stickers quickly with search functionality
- **Shopping Cart** - Session-based cart with add, update, and remove capabilities
- **Checkout Process** - Complete order placement with stock validation
- **User Accounts** - Registration, login, and profile management
- **Order History** - View past orders and order status
- **Password Recovery** - Email-based password reset via SMTP
- **Feedback System** - Submit feedback and suggestions
- **Theme Toggle** - Switch between light and dark mode

### Admin Panel
- **Dashboard** - Overview of store activity
- **Product Management** - Add, edit, and delete products with image uploads
- **Order Management** - View orders and update status (pending, processing, shipped, cancelled)
- **User Management** - View and manage customer accounts
- **Feedback Management** - Review and respond to customer feedback

### Security
- CSRF protection on all forms
- Secure session handling with timeout
- Password hashing (bcrypt)
- Input validation and sanitization
- SQL prepared statements

---

## Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.0+ |
| Database | MySQL / MariaDB |
| Frontend | HTML5, CSS3 (Flexbox & Grid) |
| Email | PHPMailer (SMTP) |
| Server | Apache (XAMPP) |

---

## Project Structure

```
website/
├── admin/                  # Admin panel
│   ├── css/               # Admin styles
│   ├── feedback/          # Feedback management
│   ├── includes/          # Header, footer
│   ├── orders/            # Order management
│   ├── products/          # Product CRUD
│   ├── users/             # User management
│   └── login.php          # Admin authentication
├── database/
│   └── website_db.sql     # Database schema & seed data
├── public/                 # Public-facing pages
│   ├── css/               # Public styles
│   ├── images/            # Product images
│   ├── index.php          # Homepage
│   ├── product.php        # Product details
│   ├── cart.php           # Shopping cart
│   ├── checkout.php       # Checkout process
│   ├── login.php          # User login
│   ├── register.php       # User registration
│   └── profile.php        # User profile
├── src/
│   ├── classes/           # PHP classes
│   │   ├── Admin.php
│   │   ├── AdminProduct.php
│   │   ├── Cart.php
│   │   ├── Database.php
│   │   ├── Order.php
│   │   ├── Product.php
│   │   └── User.php
│   ├── helpers/           # Helper functions
│   │   └── Validator.php
│   ├── includes/          # Shared components
│   └── config.php         # Configuration
├── vendor/                 # Composer dependencies
└── README.md
```

---

## Getting Started

### Prerequisites

- [XAMPP](https://www.apachefriends.org/download.html) (Apache, MySQL, PHP 8.0+)
- Web browser
- Git (optional)

### Installation

1. **Clone or download the project**

   Place the project in your XAMPP `htdocs` folder:
   ```bash
   cd C:/xampp/htdocs/
   git clone https://github.com/your-username/stickza.git website
   ```

   Or download and extract to `C:/xampp/htdocs/website`

2. **Start XAMPP services**

   Open XAMPP Control Panel and start:
   - Apache
   - MySQL

3. **Create the database**

   - Open [phpMyAdmin](http://localhost/phpmyadmin/)
   - Click "Databases" tab
   - Create a new database named `website_db`
   - Click "Create"

4. **Import the database schema**

   - Select the `website_db` database
   - Click the "Import" tab
   - Choose the file: `database/website_db.sql`
   - Click "Go"

5. **Configure the application**

   Open `src/config.php` and verify database settings:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');       // Default XAMPP user
   define('DB_PASS', '');           // Default XAMPP password (empty)
   define('DB_NAME', 'website_db');
   ```

6. **Configure email (optional)**

   For password reset functionality, update SMTP settings in `src/config.php`:
   ```php
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_USER', 'your-email@gmail.com');
   define('SMTP_PASS', 'your-app-password');
   ```

7. **Access the application**

   - **Store**: http://localhost/website/public/
   - **Admin Panel**: http://localhost/website/admin/

---

## Default Accounts

### Admin Access
To access the admin panel, use an account with `is_admin = 1` in the database:
- Email: `example2@gmail.com`
- Password: Set during registration (check database for existing test accounts)

### Test Customers
Several test accounts exist in the seed data. Register a new account for fresh testing.

---

## Configuration

### Environment Variables (Optional)

For production, set these environment variables instead of hardcoding in config:
```
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
SMTP_FROM_EMAIL=no-reply@stickza.com
```

### Session Settings

Sessions are configured with:
- 30-minute inactivity timeout
- HTTP-only cookies
- SameSite=Lax protection

---

## Database Schema

| Table | Description |
|-------|-------------|
| `categories` | Product categories |
| `products` | Product catalog with pricing and stock |
| `customers` | User accounts (customers and admins) |
| `orders` | Order records with status tracking |
| `order_items` | Individual items within orders |
| `feedback` | Customer feedback submissions |
| `password_resets` | Password reset tokens |

---

## Screenshots

### Homepage
*Product catalog with category filtering*

### Shopping Cart
*Cart management with quantity controls*

### Admin Dashboard
*Order and product management interface*

---

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit changes (`git commit -m 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Open a Pull Request

---

## License

This project is open-source and available under the [MIT License](LICENSE).

---

## Acknowledgments

- Built as a learning project for PHP & MySQL e-commerce development
- Icons and placeholder images for demonstration purposes
