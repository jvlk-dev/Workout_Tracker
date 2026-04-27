<?php
require_once 'config/db.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);
        header("Location: login.php?msg=registered");
    } catch (PDOException $e) {
        $error = "Username already taken.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up | Web Workout</title>
    <link rel="stylesheet" href="assets/css/index_style.css">
    <style>
        .auth-container { max-width: 400px; margin: 100px auto; padding: 2rem; background: var(--bg-side); border-radius: 12px; border: 1px solid var(--border); }
        .auth-input { width: 100%; padding: 12px; margin: 10px 0; background: var(--bg-input); border: 1px solid var(--border); color: white; border-radius: 6px; box-sizing: border-box; }
        .auth-btn { width: 100%; padding: 12px; background: #2ea043; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 10px; }
    </style>
</head>
<body style="display: flex; justify-content: center; align-items: center; min-height: 100vh;">
    <div class="auth-container">
        <h2 style="text-align: center; color: #2ea043;">Create Account</h2>
        <?php if($error): ?> <p style="color: var(--hard); font-size: 0.8rem;"><?php echo $error; ?></p> <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" class="auth-input" placeholder="Choose Username" required>
            <input type="password" name="password" class="auth-input" placeholder="Choose Password" required>
            <button type="submit" class="auth-btn">Register</button>
        </form>
        <p style="text-align: center; font-size: 0.9rem; margin-top: 20px;">
            Already have an account? <a href="login.php" style="color: var(--accent);">Login</a>
        </p>
    </div>
</body>
</html>