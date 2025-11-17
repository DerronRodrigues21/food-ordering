<?php
include 'config.php';

// ensure user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: index.php');
    exit;
}

// must be POST from cart form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: customer_dashboard.php');
    exit;
}

$customer_id = $_SESSION['customer_id'];
$address = isset($_POST['address']) ? $conn->real_escape_string(trim($_POST['address'])) : '';
$method = isset($_POST['method']) ? $conn->real_escape_string($_POST['method']) : 'CASH';
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cart)) {
    die('Cart is empty. <a href="customer_dashboard.php">Back</a>');
}

$conn->begin_transaction();

try {
    // Determine restaurant (enforce single-restaurant cart)
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $res = $conn->query("SELECT restaurant_id FROM Menu_Item WHERE item_id IN ($ids) LIMIT 1");
    if (!$res || $res->num_rows === 0) throw new Exception('Invalid items in cart');
    $r = $res->fetch_assoc();
    $restaurant_id = intval($r['restaurant_id']);

    // Insert order header
    $stmt = $conn->prepare("INSERT INTO `Order` (customer_id, restaurant_id, delivery_address, status) VALUES (?, ?, ?, 'PLACED')");
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param('iis', $customer_id, $restaurant_id, $address);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Insert order items
    $stmtI = $conn->prepare("INSERT INTO Order_Item (order_id, item_id, quantity, item_price) VALUES (?, ?, ?, ?)");
    if (!$stmtI) throw new Exception($conn->error);
    foreach ($cart as $item_id => $qty) {
        $item_id = intval($item_id);
        $qty = max(1, intval($qty));
        $r2 = $conn->query("SELECT price FROM Menu_Item WHERE item_id = $item_id LIMIT 1");
        if (!$r2 || $r2->num_rows === 0) throw new Exception("Item $item_id not found");
        $row = $r2->fetch_assoc();
        $price = (float)$row['price'];
        $stmtI->bind_param('iiid', $order_id, $item_id, $qty, $price);
        $stmtI->execute();
    }

    // Compute total and update order header
    $resT = $conn->query("SELECT SUM(quantity * item_price) AS total FROM Order_Item WHERE order_id = $order_id");
    $tot = $resT->fetch_assoc()['total'];
    $stmtU = $conn->prepare("UPDATE `Order` SET total_amount = ? WHERE order_id = ?");
    $stmtU->bind_param('di', $tot, $order_id);
    $stmtU->execute();

    // Insert payment record
    $stmtP = $conn->prepare("INSERT INTO Payment (order_id, amount, status, method) VALUES (?, ?, 'PENDING', ?)");
    $stmtP->bind_param('ids', $order_id, $tot, $method);
    $stmtP->execute();

    $conn->commit();

    // Clear cart session
    unset($_SESSION['cart']);
    unset($_SESSION['cart_restaurant']);

    // Fetch order info & items for confirmation view
    $order_info = $conn->query("SELECT o.order_id, o.order_date, o.total_amount, o.delivery_address, p.method FROM `Order` o JOIN Payment p ON p.order_id=o.order_id WHERE o.order_id=$order_id")->fetch_assoc();
    $items = $conn->query("SELECT mi.name, oi.quantity, oi.item_price FROM Order_Item oi JOIN Menu_Item mi ON oi.item_id=mi.item_id WHERE oi.order_id=$order_id");

    // Output confirmation page
    ?>
    <!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Order Confirmed</title>
        <link rel="stylesheet" href="style.css">
        <style>
            .confirm-card { max-width:720px; margin:40px auto; padding:22px; border-radius:12px; box-shadow:0 12px 30px rgba(0,0,0,0.06); background:#fff; text-align:left; }
            .confirm-card h2 { margin-top:0; }
            .confirm-meta { color:#6b7280; font-size:14px; margin-bottom:8px; }
            .confirm-items { margin-top:12px; }
            .confirm-items li { margin-bottom:6px; }
            .confirm-actions { margin-top:18px; display:flex; gap:12px; align-items:center; }
        </style>
    </head>
    <body>
    <div class="container">
        <div class="confirm-card">
            <h2>Order Confirmed</h2>
            <div class="confirm-meta">Transaction time: <?php echo htmlspecialchars($order_info['order_date']); ?></div>
            <h3>Order #<?php echo (int)$order_info['order_id']; ?></h3>

            <div style="margin-top:12px">
                <h4>Items</h4>
                <ul class="confirm-items">
                    <?php while ($it = $items->fetch_assoc()) { ?>
                        <li><?php echo htmlspecialchars($it['name']); ?> × <?php echo (int)$it['quantity']; ?> — Rs <?php echo number_format($it['item_price'],2); ?></li>
                    <?php } ?>
                </ul>

                <p><strong>Total:</strong> Rs <?php echo number_format($order_info['total_amount'],2); ?></p>

                <p><strong>Delivery address:</strong><br><?php echo nl2br(htmlspecialchars($order_info['delivery_address'])); ?></p>
                <p><strong>Payment method:</strong> <?php echo htmlspecialchars($order_info['method']); ?></p>
            </div>

            <div class="confirm-actions">
                <a class="btn" href="your_orders.php">View your orders</a>
                <a class="btn secondary" href="index.php">Home</a>

                <!-- Cancel order button -->
                <a class="btn" href="cancel_order.php?order_id=<?php echo (int)$order_id; ?>"
                   style="margin-left:auto;background:#b71c1c;color:#fff;border:none;padding:10px 14px;border-radius:10px;text-decoration:none;">
                   Cancel Order
                </a>
            </div>
        </div>
    </div>
    </body>
    </html>

    <?php
    exit;

} catch (Exception $e) {
    $conn->rollback();
    // safe error message
    die('Error creating order: ' . htmlspecialchars($e->getMessage()));
}
