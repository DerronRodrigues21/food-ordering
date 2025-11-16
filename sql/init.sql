CREATE DATABASE IF NOT EXISTS food_ordering;
USE food_ordering;
DROP TABLE IF EXISTS Payment;
DROP TABLE IF EXISTS Order_Item;
DROP TABLE IF EXISTS `Order`;
DROP TABLE IF EXISTS Menu_Item;
DROP TABLE IF EXISTS Restaurant;
DROP TABLE IF EXISTS Delivery_Person;
DROP TABLE IF EXISTS Customer;
CREATE TABLE Customer (
  customer_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(100),
  email VARCHAR(150),
  phone VARCHAR(20),
  address TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE Delivery_Person (
  delivery_person_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  name VARCHAR(100),
  phone VARCHAR(20),
  vehicle_info VARCHAR(100),
  status ENUM('AVAILABLE','BUSY','OFF') DEFAULT 'AVAILABLE'
);
CREATE TABLE Restaurant (
  restaurant_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  address TEXT,
  cuisine_type VARCHAR(80),
  contact VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE Menu_Item (
  item_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  restaurant_id INT NOT NULL,
  price DECIMAL(8,2) NOT NULL,
  description TEXT,
  is_available TINYINT(1) DEFAULT 1,
  FOREIGN KEY (restaurant_id) REFERENCES Restaurant(restaurant_id) ON DELETE CASCADE
);
CREATE TABLE `Order` (
  order_id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  total_amount DECIMAL(10,2) DEFAULT 0,
  status ENUM('PLACED','READY','OUT_FOR_DELIVERY','DELIVERED','CANCELLED') DEFAULT 'PLACED',
  restaurant_id INT NOT NULL,
  delivery_address TEXT,
  delivery_person_id INT NULL,
  FOREIGN KEY (customer_id) REFERENCES Customer(customer_id),
  FOREIGN KEY (restaurant_id) REFERENCES Restaurant(restaurant_id)
);
CREATE TABLE Order_Item (
  order_item_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  item_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  item_price DECIMAL(8,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES `Order`(order_id) ON DELETE CASCADE,
  FOREIGN KEY (item_id) REFERENCES Menu_Item(item_id)
);
CREATE TABLE Payment (
  payment_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL UNIQUE,
  amount DECIMAL(10,2) NOT NULL,
  method ENUM('CASH','CARD','UPI','WALLET') DEFAULT 'CARD',
  status ENUM('PENDING','COMPLETED','FAILED','REFUNDED') DEFAULT 'PENDING',
  paid_at DATETIME NULL,
  FOREIGN KEY (order_id) REFERENCES `Order`(order_id) ON DELETE CASCADE
);
INSERT INTO Customer (username, password, name, email, phone, address)
VALUES ('alice','alice123','Alice','alice@example.com','9999999999','123 Main St'),
       ('bob','bob123','Bob','bob@example.com','8888888888','45 Olive Ave');
INSERT INTO Delivery_Person (username,password,name,phone,vehicle_info)
VALUES ('dman1','deliver123','Dan','7777777777','Bike-001'),
       ('dman2','deliver456','Rex','6666666666','Bike-002');
INSERT INTO Restaurant (name,address,cuisine_type,contact) VALUES
('Spice Street','12 Curry Lane','Indian','9000000001'),
('Pizza Palace','7 Cheese St','Italian','9000000002'),
('Sushi Central','88 Fish Ave','Japanese','9000000003'),
('Burger Barn','3 Patty Rd','American','9000000004');
INSERT INTO Menu_Item (name,restaurant_id,price,description) VALUES
('Butter Chicken',1,250.00,'Creamy tomato gravy'),
('Paneer Tikka',1,180.00,'Charred paneer cubes'),
('Chicken Biryani',1,220.00,'Fragrant basmati with spices'),
('Veg Thali',1,150.00,'Assorted vegetarian dishes'),
('Garlic Naan',1,40.00,'Tandoor-baked naan with garlic'),
('Gulab Jamun (2 pcs)',1,60.00,'Sweet syrup balls'),
('Margherita Pizza',2,320.00,'Classic cheese & tomato'),
('Farmhouse Pizza',2,380.00,'Veggies and cheese'),
('Garlic Bread',2,80.00,'Buttery garlic bread'),
('Tomato Basil Pasta',2,260.00,'Pasta in tomato-basil sauce'),
('Cheesy Sticks',2,120.00,'Mozzarella sticks'),
('Tiramisu Slice',2,150.00,'Coffee-flavoured dessert'),
('Salmon Nigiri (2 pcs)',3,200.00,'Fresh salmon on rice'),
('California Roll (6 pcs)',3,280.00,'Crab and avocado roll'),
('Spicy Tuna Roll',3,300.00,'Tuna with spicy mayo'),
('Miso Soup',3,90.00,'Warm miso broth'),
('Chicken Katsu',3,250.00,'Breadcrumbed fried cutlet'),
('Matcha Ice Cream',3,110.00,'Green tea ice cream'),
('Classic Cheeseburger',4,180.00,'Beef patty with cheese'),
('Double Patty Burger',4,240.00,'Two beef patties'),
('Crispy Fries',4,70.00,'Golden fried potatoes'),
('Fried Chicken Wings (4pcs)',4,160.00,'Spicy wings'),
('Chocolate Milkshake',4,120.00,'Creamy chocolate shake'),
('Veggie Burger',4,150.00,'Grilled veggie patty');


ALTER TABLE `Order` ADD COLUMN delivered_at DATETIME NULL AFTER order_date;
