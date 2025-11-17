<?php include 'config.php'; ?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tomato - Login</title>
    <link rel="stylesheet" href="style.css">

    <style>
        /* PAGE-SPECIFIC BRANDING */
        .login-logo {
            position: fixed;
            top: 24px;
            left: 24px;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .login-logo img {
            width: 82px;
            height: 82px;
            border-radius: 14px;
            object-fit: cover;
            box-shadow: 0 5px 16px rgba(0,0,0,0.18);
        }
        .brand-text {
            font-size: 36px;
            font-weight: 800;
            color: #E23744; /* Tomato Red */
            letter-spacing: 1px;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        /* LOGIN CARD SPACING FIX */
        .login-card {
            max-width: 420px;
            margin: 160px auto 0 auto; 
            padding: 28px;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 12px 34px rgba(0,0,0,0.08);
        }
        .login-card h2 {
            text-align: center;
            margin-bottom: 12px;
            margin-top: 0;
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        .login-card input,
        .login-card select {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border-radius: 10px;
            border: 1px solid #e5e5e5;
            font-size: 15px;
        }
    </style>
</head>

<body>

<!-- BRANDING LOGO + TEXT -->
<div class="login-logo">
    <img src="images/logo.png" alt="Tomato logo">
    <span class="brand-text">Tomato</span>
</div>


<!-- LOGIN BOX -->
<div class="login-card">

<h2>Login</h2>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='signin') {

    $role = $_POST['role'] === 'delivery' ? 'delivery' : 'customer';
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($role === 'customer') {
        $stmt = $conn->prepare("SELECT customer_id,name FROM Customer WHERE username=? AND password=?");
    } else {
        $stmt = $conn->prepare("SELECT delivery_person_id,name FROM Delivery_Person WHERE username=? AND password=?");
    }

    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();
        
        if ($role === 'customer') { 
            $_SESSION['role'] = 'customer';
            $_SESSION['customer_id'] = $row['customer_id']; 
            header('Location: customer_dashboard.php'); 
            exit; 
        } else {
            $_SESSION['role'] = 'delivery';
            $_SESSION['delivery_person_id'] = $row['delivery_person_id']; 
            header('Location: delivery_dashboard.php'); 
            exit;
        }

    } else {
        echo '<div style="color:#b71c1c;font-weight:700;margin-bottom:12px;text-align:center;">Invalid username or password</div>';
    }
}
?>


<form method="post">
    <input type="hidden" name="action" value="signin">

    <select name="role">
        <option value="customer">Customer</option>
        <option value="delivery">Delivery</option>
    </select>

    <input name="username" placeholder="Username" required>
    <input name="password" type="password" placeholder="Password" required>

    <button class="btn" type="submit" style="margin-top:16px;width:100%;">Sign In</button>
</form>

<div style="text-align:center;margin-top:20px">
    <a class="btn secondary" href="customer_register.php" style="width:100%;display:block;margin-bottom:8px;">Sign up (Customer)</a>
    <a class="btn secondary" href="delivery_register.php" style="width:100%;display:block;">Sign up (Delivery)</a>
</div>

</div>

</body>
</html>