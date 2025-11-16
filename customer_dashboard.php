<?php include 'config.php';
if (!isset($_SESSION['customer_id'])) { header('Location: index.php'); exit; }
if (isset($_GET['signout'])) { session_destroy(); header('Location: index.php'); exit; }

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($search !== '') {
    $s = $conn->real_escape_string($search);
    $sqlR = "SELECT DISTINCT r.* FROM Restaurant r 
             LEFT JOIN Menu_Item m ON m.restaurant_id = r.restaurant_id
             WHERE r.name LIKE '%$s%' OR r.cuisine_type LIKE '%$s%' OR m.name LIKE '%$s%'
             ORDER BY r.restaurant_id";
} else {
    $sqlR = "SELECT * FROM Restaurant ORDER BY restaurant_id";
}
$resR = $conn->query($sqlR);

$matchingItems = [];
if ($search !== '') {
    $s = $conn->real_escape_string($search);
    $resI = $conn->query("SELECT m.*, r.name AS restaurant_name, r.restaurant_id FROM Menu_Item m JOIN Restaurant r ON m.restaurant_id=r.restaurant_id WHERE m.name LIKE '%$s%'");
    while($it = $resI->fetch_assoc()) $matchingItems[] = $it;
}

if (isset($_GET['add'])) {
    $item_id = intval($_GET['add']); $qty = isset($_GET['q'])?intval($_GET['q']):1;
    $rres = $conn->query("SELECT restaurant_id FROM Menu_Item WHERE item_id=$item_id LIMIT 1");
    if ($rres && $rres->num_rows) {
        $rrow = $rres->fetch_assoc(); $r_id = $rrow['restaurant_id'];
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) { $_SESSION['cart']=[]; $_SESSION['cart_restaurant']=$r_id; }
        else { if ($_SESSION['cart_restaurant'] != $r_id) { $_SESSION['cart']=[]; $_SESSION['cart_restaurant']=$r_id; } }
        if (!isset($_SESSION['cart'][$item_id])) $_SESSION['cart'][$item_id]=0; $_SESSION['cart'][$item_id]+=$qty;
    }
    header('Location: customer_dashboard.php'.($search?('?q='.urlencode($search)):''));
    exit;
}

if (isset($_GET['change']) && isset($_GET['item'])) {
    $iid=intval($_GET['item']); $delta=intval($_GET['change']);
    if (!isset($_SESSION['cart'][$iid])) $_SESSION['cart'][$iid]=0; $_SESSION['cart'][$iid]+=$delta;
    if ($_SESSION['cart'][$iid]<=0) unset($_SESSION['cart'][$iid]);
    header('Location: customer_dashboard.php'.($search?('?q='.urlencode($search)):''));
    exit;
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Tomato - Restaurants</title><link rel="stylesheet" href="style.css"></head><body>
<div class="container">
  <div class="header">
    <div class="brand"><div class="logo"><img src="images/logo.jpg" alt="logo"></div><div><div style="font-weight:700">Tomato</div><div class="small">Find restaurants</div></div></div>

    <div class="header-center">
      <form method="get" action="customer_dashboard.php" class="search-bar" style="width:100%;">
        <input name="q" placeholder="Search restaurants, cuisine or dishes (e.g. pizza, Indian)" value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn" type="submit">Search</button>
      </form>
    </div>

    <div class="header-actions">
      <a class="signout" href="customer_dashboard.php?signout=1">Sign out</a>
    </div>
  </div>

  <div style="display:flex;gap:20px">
    <div style="flex:1">
      <?php
      if (!empty($matchingItems)) {
          echo '<div class="card"><h3>Matching items</h3>';
          foreach($matchingItems as $it){
              $iid = $it['item_id'];
              $img = "images/item_{$iid}.jpg"; if(!file_exists($img)) $img="images/item_{$iid}.png";
              echo '<div class="menu-item"><div class="left"><div class="img-wrap"><img src="'.$img.'" alt=""></div><div><strong>'.htmlspecialchars($it['name']).'</strong><div class="item-desc">'.htmlspecialchars($it['description']).'</div><div class="small">Restaurant: '.htmlspecialchars($it['restaurant_name']).'</div></div></div>';
              echo '<div><div style="text-align:right">Rs '.$it['price'].'<br><a class="btn" href="customer_dashboard.php?add='.$iid.'&q=1">Add</a></div></div></div>';
          }
          echo '</div>';
      }

      while($r=$resR->fetch_assoc()){
          $rid = $r['restaurant_id'];
          $img = "images/restaurant_".$rid.".jpg"; if(!file_exists($img)) $img="images/restaurant_".$rid.".png";
          // compute price point
          $pr = $conn->query("SELECT AVG(price) AS avgp FROM Menu_Item WHERE restaurant_id=$rid")->fetch_assoc()['avgp'];
          $pp = 1; if ($pr>200) $pp=3; elseif($pr>100) $pp=2;
          // build rupee string
          $rupeeStr = str_repeat('₹',$pp);
          echo '<div class="restaurant-card">';
          echo '<div class="img-wrap"><img src="'.$img.'" alt=""><div class="rupee-badge"><strong style="font-size:13px">'.$rupeeStr.'</strong><span class="dot"></span></div></div>';
          echo '<div class="restaurant-meta"><div class="topline"><div><strong>'.htmlspecialchars($r['name']).'</strong><div class="small">'.htmlspecialchars($r['address']).'</div></div>';
          echo '<div style="display:flex;flex-direction:column;align-items:flex-end"><div><span class="cuisine">'.htmlspecialchars($r['cuisine_type']).'</span></div><div style="margin-top:6px"><a class="btn" href="restaurant_view.php?id='.$rid.'">View menu</a></div></div>';
          echo '</div></div></div>';
      }
      ?>
    </div>

    <div style="width:360px">
      <div class="card">
        <h3>Your Cart</h3>
        <?php
        if (empty($_SESSION['cart'])) { echo '<div class="small">Cart empty</div>'; }
        else {
            $ids = implode(',', array_keys($_SESSION['cart']));
            $res2 = $conn->query("SELECT * FROM Menu_Item WHERE item_id IN ($ids)");
            $total=0;
            echo "<table class='cart-table'>";
            while($r=$res2->fetch_assoc()){
                $q = $_SESSION['cart'][$r['item_id']]; $price=$r['price']*$q; $total+=$price;
                echo "<tr><td>".$r['name']."</td><td><a class='btn secondary' href='?change=-1&item=".$r['item_id']."'>-</a> <span style='padding:0 8px'>".$q."</span> <a class='btn' href='?change=1&item=".$r['item_id']."'>+</a></td><td>Rs ".$price."</td></tr>";
            }
            echo "<tr><td colspan=2 class='small'>Total</td><td>Rs ".$total."</td></tr>";
            echo "</table>";
            echo "<form method='post' action='create_order.php'><label>Delivery address</label><br><input name='address' required><label style='margin-top:8px'>Payment</label><br><select name='method'><option value='CASH'>Cash</option><option value='CARD'>Card</option><option value='UPI'>UPI</option><option value='WALLET'>Wallet</option></select><br><button class='btn' style='margin-top:12px'>Place Order</button></form>";
        }
        ?>
      </div>
    </div>

  </div>
</div>
</body></html>
