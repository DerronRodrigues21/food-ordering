<?php include 'config.php';
if (!isset($_SESSION['customer_id'])) { header('Location: index.php'); exit; }
$rid = intval($_GET['id']); if ($rid<=0) header('Location: customer_dashboard.php');
$r = $conn->query("SELECT * FROM Restaurant WHERE restaurant_id=$rid")->fetch_assoc();
$items = $conn->query("SELECT * FROM Menu_Item WHERE restaurant_id=$rid");
?>
<!doctype html><html><head><meta charset="utf-8"><title><?php echo htmlspecialchars($r['name']); ?></title><link rel="stylesheet" href="style.css">
<script>
function toggle(id){
  var el=document.getElementById('exp_'+id);
  el.style.display = (el.style.display==='block'?'none':'block');
}
</script>
</head><body>
<div class="container">
  <div class="header">
    <div class="brand"><div class="logo"><img src="images/logo.jpg" alt="logo"></div><div><div style="font-weight:700">Tomato</div><div class="small"><?php echo htmlspecialchars($r['cuisine_type']); ?></div></div></div>
    <div class="header-center"></div>
    <div class="header-actions"><a class="signout" href="customer_dashboard.php?signout=1">Sign out</a></div>
  </div>

  <div class="card">
    <h2><?php echo htmlspecialchars($r['name']); ?></h2>
    <p class="small"><?php echo nl2br(htmlspecialchars($r['address'])); ?></p>
    <h3>Menu</h3>
    <?php while($it=$items->fetch_assoc()){ $iid=$it['item_id']; $img='images/item_'.$iid.'.jpg'; if(!file_exists($img)) $img='images/item_'.$iid.'.png'; ?>
      <div class="menu-item">
        <div class="left">
          <img src="<?php echo $img; ?>" alt="">
          <div>
            <strong><?php echo htmlspecialchars($it['name']); ?></strong>
            <div class="item-desc"><?php echo htmlspecialchars($it['description']); ?></div>
            <div class="small">Rs <?php echo $it['price']; ?></div>
          </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end">
          <div style="font-weight:700">Rs <?php echo $it['price']; ?></div>
          <div><button class="exp-btn" onclick="toggle(<?php echo $iid; ?>)">Details</button> <a class="btn" href="customer_dashboard.php?add=<?php echo $iid; ?>&q=1">Add</a></div>
        </div>
      </div>
      <div id="exp_<?php echo $iid; ?>" style="display:none;padding:8px">
        <img src="<?php echo $img; ?>" style="width:280px;height:170px;object-fit:cover;border-radius:8px" onerror="this.style.display='none'">
        <p class="small"><?php echo htmlspecialchars($it['description']); ?></p>
      </div>
    <?php } ?>
  </div>
</div>
</body></html>
