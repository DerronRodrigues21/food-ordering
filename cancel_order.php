<?php
// cancel_order.php
// Cancels (deletes) an order if it belongs to the logged-in customer and is cancellable.

include 'config.php';

if (!isset($_SESSION['customer_id'])) {
    // not logged-in
    header('Location: index.php');
    exit;
}

if (!isset($_GET['order_id'])) {
    echo "Invalid request. No order specified.";
    exit;
}

$order_id = intval($_GET['order_id']);
$customer_id = intval($_SESSION['customer_id']);

// Verify order exists and belongs to the user, and check status
$check = $conn->prepare("SELECT order_id, status FROM `Order` WHERE order_id = ? AND customer_id = ? LIMIT 1");
$check->bind_param('ii', $order_id, $customer_id);
$check->execute();
$res = $check->get_result();

if (!$res || $res->num_rows !== 1) {
    echo "<!doctype html><html><head><meta charset='utf-8'><title>Cancel Order</title><link rel='stylesheet' href='style.css'></head><body>";
    echo "<div class='container'><div class='card' style='max-width:700px;margin:40px auto;text-align:center'><h2>Cannot cancel</h2><p>Order not found or you do not have permission to cancel this order.</p><a class='btn' href='your_orders.php'>Back to Orders</a></div></div></body></html>";
    exit;
}

$row = $res->fetch_assoc();
$status = $row['status'];

// define which statuses are cancellable
$cancellable = ['PLACED','READY'];

if (!in_array($status, $cancellable)) {
    echo "<!doctype html><html><head><meta charset='utf-8'><title>Cancel Order</title><link rel='stylesheet' href='style.css'></head><body>";
    echo "<div class='container'><div class='card' style='max-width:700px;margin:40px auto;text-align:center'><h2>Cannot cancel</h2><p>This order cannot be cancelled because its status is <strong>" . htmlspecialchars($status) . "</strong>.</p><a class='btn' href='your_orders.php'>Back to Orders</a></div></div></body></html>";
    exit;
}

// Proceed to delete the order inside a transaction
$conn->begin_transaction();
try {
    // Delete order row (Order_Item and Payment should cascade if FK ON DELETE CASCADE is set)
    $del = $conn->prepare("DELETE FROM `Order` WHERE order_id = ? AND customer_id = ?");
    $del->bind_param('ii', $order_id, $customer_id);
    $del->execute();

    if ($del->affected_rows !== 1) {
        // unexpected - rollback
        throw new Exception('Unable to cancel order (no rows deleted).');
    }

    $conn->commit();

    // show success page
    echo "<!doctype html><html><head><meta charset='utf-8'><title>Order Cancelled</title><link rel='stylesheet' href='style.css'></head><body>";
    echo "<div class='container'><div class='card' style='max-width:720px;margin:40px auto;text-align:center'><h2 style='color:#b71c1c'>Order Cancelled</h2><p>Your order #".intval($order_id)." has been cancelled and removed.</p><a class='btn' href='your_orders.php'>Back to Orders</a></div></div></body></html>";
    exit;

} catch (Exception $e) {
    $conn->rollback();
    // show error
    echo "<!doctype html><html><head><meta charset='utf-8'><title>Cancel Error</title><link rel='stylesheet' href='style.css'></head><body>";
    echo "<div class='container'><div class='card' style='max-width:700px;margin:40px auto;text-align:center'><h2>Error</h2><p>Could not cancel order: " . htmlspecialchars($e->getMessage()) . "</p><a class='btn' href='your_orders.php'>Back to Orders</a></div></div></body></html>";
    exit;
}
?>