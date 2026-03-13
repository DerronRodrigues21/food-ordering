# 🍽️ Food Ordering System

A full-stack web application built with **PHP** and **MySQL** that simulates a real-world food ordering platform. The system supports three types of users — **Customers**, **Restaurants**, and **Delivery Personnel** — each with their own dashboard and role-based functionality.

---

## 🚀 Features

### 👤 Customer
- Register and log in as a customer
- Browse restaurant menus
- Place and track orders
- View order history
- Cancel orders

### 🏪 Restaurant
- View incoming orders
- Accept or manage orders

### 🚴 Delivery
- Register as a delivery agent
- View assigned orders
- Mark orders as delivered

---

## 🛠️ Tech Stack

| Layer      | Technology     |
|------------|----------------|
| Frontend   | HTML, CSS       |
| Backend    | PHP             |
| Database   | MySQL (SQL)     |

---

## 📁 Project Structure

```
food-ordering/
├── index.php                  # Landing / login page
├── config.php                 # Database configuration
├── customer_register.php      # Customer registration
├── customer_dashboard.php     # Customer home
├── create_order.php           # Place a new order
├── your_orders.php            # View your orders
├── cancel_order.php           # Cancel an order
├── restaurant_view.php        # Restaurant order view
├── accept_order.php           # Accept an order
├── delivery_register.php      # Delivery agent registration
├── delivery_dashboard.php     # Delivery agent home
├── mark_delivered.php         # Mark order as delivered
├── style.css                  # Global styles
├── images/                    # Image assets
├── sql/                       # Database schema/seed files
└── tools/                     # Utility scripts
```

---

## ⚙️ Getting Started

### Prerequisites
- PHP >= 7.4
- MySQL / MariaDB
- Apache or XAMPP / WAMP / MAMP

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/DerronRodrigues21/food-ordering.git
   cd food-ordering
   ```

2. **Set up the database**
   - Open **phpMyAdmin** or your MySQL client
   - Create a new database (e.g., `food_ordering`)
   - Import the SQL file from the `/sql` folder

3. **Configure the database connection**
   - Open `config.php`
   - Update with your credentials:
   ```php
   $host = "localhost";
   $user = "root";
   $password = "";
   $database = "food_ordering";
   ```

4. **Run the project**
   - Place the project folder in your server's `htdocs` (XAMPP) or `www` (WAMP) directory
   - Start Apache and MySQL
   - Visit `http://localhost/food-ordering/`

---

## 📸 Screenshots

> *(Add screenshots of the customer dashboard, order page, and delivery view here)*

---

## 📌 Notes

- This project was developed as a **DBMS Lab project** (V Semester)
- Focuses on relational database design and PHP-MySQL integration

---

## 👨‍💻 Author

**Derron Lawrence Rodrigues**  
[GitHub](https://github.com/DerronRodrigues21) · [LinkedIn](https://www.linkedin.com/in/)
