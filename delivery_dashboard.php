<?php include 'config.php';
if (!isset($_SESSION['delivery_person_id'])) { header('Location: index.php'); exit; }
$dpid = $_SESSION['delivery_person_id'];
?>
<!doctype html><html><head><meta charset="utf-8"><title>Delivery Dashboard</title><link rel="stylesheet" href="style.css"></head><body>
<div class="container"><div class="card" style="max-width:900px;margin:40px auto">
  <h3>Available Orders</h3>
  <?php
  $res = $conn->query("SELECT o.order_id,o.order_date,o.delivery_address,o.total_amount,c.name AS customer_name,c.phone FROM `Order` o JOIN Customer c ON o.customer_id=c.customer_id WHERE o.status='PLACED' AND (o.delivery_person_id IS NULL OR o.delivery_person_id=0)");
  while($o=$res->fetch_assoc()){
      echo '<div style="padding:12px;border-bottom:1px solid #eee"><strong>Order #'.$o['order_id'].'</strong> • Rs '.$o['total_amount'].'<br>';
      echo '<div class="small">Address: '.$o['delivery_address'].'</div>';
      echo '<div class="small">Received: '.$o['order_date'].'</div>';
      echo '<div style="margin-top:8px"><a class="btn" href="accept_order.php?order_id='.$o['order_id'].'">Accept</a></div></div>';
  }
  ?>
  <h3 style="margin-top:18px">Your Assigned Orders</h3>
  <?php
  $r2 = $conn->query("SELECT o.*, c.name AS customer_name FROM `Order` o JOIN Customer c ON o.customer_id=c.customer_id WHERE o.delivery_person_id=$dpid ORDER BY order_date DESC");
  while($a=$r2->fetch_assoc()){
      echo '<div style="padding:12px;border-bottom:1px solid #eee"><strong>Order #'.$a['order_id'].'</strong> • '.$a['status'].' • Rs '.$a['total_amount'].'<br>';
      echo '<div class="small">Address: '.htmlspecialchars($a['delivery_address']).'</div>';
      echo '<div class="small">Received: '.$a['order_date'].'</div>';
      echo '<div class="small">Delivered: '.($a['delivered_at'] ? $a['delivered_at'] : '—').'</div>';
      if ($a['status'] !== 'DELIVERED') echo '<div style="margin-top:8px"><a class="btn secondary" href="mark_delivered.php?order_id='.$a['order_id'].'">Mark Delivered</a></div>';
      echo '</div>';
  }
  ?>
</div></div></body></html>
