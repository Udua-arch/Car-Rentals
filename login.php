<?php
session_start();
require_once 'storage.php';


if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        
        if ($email === 'admin@ikarrental.hu' && $password === 'admin') {
            $_SESSION['admin'] = true;
            $_SESSION['user'] = [
                'email' => $email,
                'fullname' => 'Administrator',
                'is_admin' => true
            ];
            header('Location: admin.php');
            exit();
        }

      
        $users_storage = new Storage(new JsonIO('data/users.json'));
        $user = $users_storage->findOne(['email' => $email]);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'fullname' => $user['fullname'],
                'is_admin' => false
            ];
            header('Location: index.php');
            exit();
        } else {
            $error = "Invalid email or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - iKarRental</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">iKarRental</a>
        <div class="auth-buttons">
            <a href="register.php" class="button">Register</a>
        </div>
    </header>

    <main class="container">
        <div class="auth-form">
            <h1>Login</h1>
            
            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form" novalidate>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="button primary">Login</button>
            </form>

            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </main>
</body>
</html>