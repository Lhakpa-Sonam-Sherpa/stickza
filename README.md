# Sticker Shop 🎨.Stickza

A dynamic and fully functional e-commerce website development work in progress for a sticker shop, built from scratch using PHP & MySQL.

**[➡️ Live Demo (Coming Soon!)](https://your-live-demo-url.com)**

![Sticker Shop Screenshot]
*A preview of the Sticker Shop homepage.*

---

## ✨ Features

This project demonstrates a complete e-commerce user flow, from browsing products to placing an order.

*   **Product Catalog:** Browse products from a MySQL database, displayed in a clean, responsive grid.
*   **Product Details:** View detailed information for each individual product.
*   **User Authentication:** Secure user registration and login system with password hashing.
*   **Shopping Cart:** A session-based shopping cart to add, update, and remove items.
*   **Checkout Process:** A simulated checkout process that validates stock and creates a permanent record of the order.
*   **Order History:** Users can view their past orders.
*   **Database-Driven:** All product, user, and order information is stored and managed in a MySQL database.

---

## 🛠️ Technology Stack

*   **Backend:** PHP
*   **Database:** MySQL
*   **Frontend:** HTML5, CSS3 (with Flexbox & Grid)
*   **Development Environment:** XAMPP (Apache, MySQL, PHP)
*   **Version Control:** Git & GitHub

---

## 🚀 Getting Started

Follow these instructions to get a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

You will need a local server environment that supports PHP and MySQL. The easiest way to get this is by installing **XAMPP**.

*   [Download XAMPP](https://www.apachefriends.org/download.html) (includes Apache, PHP, and MySQL)

### Installation & Setup

1.  **Clone the Repository**
    Clone this project into your XAMPP `htdocs` folder. The `htdocs` folder is where XAMPP serves files from.
    ```bash
    # Navigate to your XAMPP htdocs directory
    # On Windows, this is usually C:/xampp/htdocs/
    cd C:/xampp/htdocs/

    # Clone the project
    git clone https://github.com/lhakap-sonam-sherpa/sticker-shop.github.io.git sticker-shop
    ```
    *(Note: You may want to rename your repository to just `sticker-shop` on GitHub for clarity)*

2.  **Start Your Services**
    Open the XAMPP Control Panel and start the **Apache** and **MySQL** services.

3.  **Create the Database**
    *   Open your web browser and navigate to `http://localhost/phpmyadmin/`.
    *   Click on the **"Databases"** tab.
    *   Under "Create database", enter the name `sticker_shop` and click **"Create"**.

4.  **Import the Database Schema**
    *   After creating the database, click on its name (`sticker_shop`) in the left sidebar.
    *   Click on the **"Import"** tab at the top.
    *   Click "Choose File" and select the `database.sql` file from the root of the project folder.
    *   Scroll down and click **"Go"**. This will create all the necessary tables (`products`, `customers`, `orders`, etc.).

5.  **Configure the Application**
    *   In the project folder, find the file `src/config.php`.
    *   Open it and make sure the database credentials match your local XAMPP setup. The default settings should work for a standard XAMPP installation.
    ```php
    // src/config.php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root'); // Default XAMPP user
    define('DB_PASS', '');     // Default XAMPP password is empty
    define('DB_NAME', 'sticker_shop');
    ```

6.  **Run the Application**
    You're all set! Open your web browser and navigate to:
    **`http://localhost/sticker-shop/public/`**

    You should now see the homepage of the Sticker Shop.

---

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).
