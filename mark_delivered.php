<?php include 'config.php';
if (!isset($_SESSION['delivery_person_id'])) { die('login required'); }
$dpid = $_SESSION['delivery_person_id']; $oid = intval($_GET['order_id']);
$conn->begin_transaction();
try {
    $stmt = $conn->prepare("UPDATE `Order` SET status='DELIVERED', delivered_at=NOW() WHERE order_id=? AND delivery_person_id=?");
    $stmt->bind_param('ii',$oid,$dpid); $stmt->execute();
    if ($stmt->affected_rows!==1) { throw new Exception('Not assigned to you'); }
    $conn->query("UPDATE Delivery_Person SET status='AVAILABLE' WHERE delivery_person_id=$dpid");
    $conn->query("UPDATE Payment p JOIN `Order` o ON p.order_id=o.order_id SET p.status='COMPLETED', p.paid_at=NOW() WHERE p.order_id=$oid");
    $conn->commit();
    echo 'Marked delivered. <a href="delivery_dashboard.php">Back</a>';
} catch(Exception $e){ $conn->rollback(); echo 'Error: '.$e->getMessage(); }
