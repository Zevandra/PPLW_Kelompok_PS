<?php
require_once __DIR__ . '/callinglibs.php';
session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: AdminControlPanel.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new DBconnection();
    $userClass = new User($db);
    
    $respon = $userClass->loginAdmin($_POST['username'] ?? '', $_POST['password'] ?? '');
    
    if ($respon->status) {
        header("Location: AdminControlPanel.php");
        exit();
    } else {
        $error = $respon->message;
    }
    $db->close_connection();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - AllInOneShop</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0; }
        .login-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 100%; max-width: 400px; text-align: center; }
        .login-card h2 { margin-top: 0; color: #2c3e50; font-size: 2em; margin-bottom: 10px; }
        .login-card p { color: #7f8c8d; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #34495e; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 1em; transition: 0.3s; }
        .form-group input:focus { border-color: #3498db; outline: none; box-shadow: 0 0 5px rgba(52, 152, 219, 0.3); }
        .btn-login { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 6px; font-size: 1.1em; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-login:hover { background: #0056b3; }
        .alert { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 6px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Admin Portal</h2>
        <p>Silakan login untuk mengelola sistem.</p>
        
        <?php if($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username admin" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>
</body>
</html>