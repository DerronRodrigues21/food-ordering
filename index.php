<?php include 'config.php'; ?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Tomato - Login</title><link rel="stylesheet" href="style.css"></head><body>
<div style="position:fixed;top:18px;left:18px;z-index:10"><img src="images/logo.jpg" style="width:56px;height:56px;border-radius:8px" alt="Tomato logo"></div>

<div class="login-card">
  <h2>Tomato</h2>
  <p class="small">Sign in to continue</p>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='signin') {
    $role = $_POST['role'] === 'delivery' ? 'delivery' : 'customer';
    $username = $_POST['username']; $password = $_POST['password'];
    if ($role === 'customer') $stmt = $conn->prepare("SELECT customer_id,name FROM Customer WHERE username=? AND password=?");
    else $stmt = $conn->prepare("SELECT delivery_person_id,name FROM Delivery_Person WHERE username=? AND password=?");
    $stmt->bind_param("ss", $username, $password); $stmt->execute(); $res = $stmt->get_result();
    if ($res && $res->num_rows===1) {
        $row = $res->fetch_assoc();
        if ($role==='customer') { $_SESSION['role']='customer'; $_SESSION['customer_id']=$row['customer_id']; header('Location: customer_dashboard.php'); exit; }
        else { $_SESSION['role']='delivery'; $_SESSION['delivery_person_id']=$row['delivery_person_id']; header('Location: delivery_dashboard.php'); exit; }
    } else { echo '<div style="color:#b71c1c;font-weight:700;margin-bottom:8px;">Invalid username or password</div>'; }
}
?>

<form method="post" style="max-width:360px;margin:0 auto">
<input type="hidden" name="action" value="signin">
<select name="role" style="width:100%;padding:10px;border-radius:8px;margin-bottom:8px">
  <option value="customer">Customer</option>
  <option value="delivery">Delivery</option>
</select>
<input name="username" placeholder="Username" required>
<input name="password" type="password" placeholder="Password" required>
<button class="btn" type="submit" style="margin-top:10px">Sign in</button>
</form>

<div style="text-align:center;margin-top:12px">
  <a class="btn secondary" href="customer_register.php">Sign up (Customer)</a>
  <a class="btn secondary" href="delivery_register.php">Sign up (Delivery)</a>
</div>
</div>
</body></html>
