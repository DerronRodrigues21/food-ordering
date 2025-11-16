<?php include 'config.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $username=$_POST['username']; $password=$_POST['password']; $name=$_POST['name'];
    $stmt = $conn->prepare("INSERT INTO Delivery_Person (username,password,name) VALUES (?,?,?)");
    $stmt->bind_param("sss",$username,$password,$name);
    if ($stmt->execute()) { header('Location: index.php'); exit; } else { $err=$stmt->error; }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Delivery Register</title><link rel="stylesheet" href="style.css"></head><body>
<div class="container"><div class="card" style="max-width:420px;margin:40px auto;text-align:center">
  <h3>Delivery Register</h3><?php if(isset($err)) echo '<div style="color:#b71c1c">'.$err.'</div>'; ?>
  <form method="post">
    <input name="username" placeholder="Username" required>
    <input name="password" placeholder="Password" required>
    <input name="name" placeholder="Full name">
    <div style="margin-top:12px"><button class="btn">Register</button></div>
  </form>
</div></div></body></html>
