<?php
require_once 'includes/header.php';
require_once 'storage.php';


$errors = [];
$success = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    
    if (empty($fullname)) {
        $errors['fullname'] = 'Full name is required';
    } elseif (strlen($fullname) < 2) {
        $errors['fullname'] = 'Full name must be at least 2 characters long';
    }
    

    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    } else {
      
        $users_storage = new Storage(new JsonIO('data/users.json'));
        $existing_user = $users_storage->findOne(['email' => $email]);
        if ($existing_user) {
            $errors['email'] = 'This email is already registered';
        }
    }
    
   
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors['password'] = 'Password must contain at least one lowercase letter';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must contain at least one number';
    }
    
   
    if (empty($errors)) {
        $users_storage = new Storage(new JsonIO('data/users.json'));
        $new_user = [
            'fullname' => $fullname,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'is_admin' => false
        ];
        
        $users_storage->add($new_user);
        $success = true;
        
        
        header("refresh:2;url=login.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - iKarRental</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <main class="container">
        <?php if ($success): ?>
            <div class="success-message">
                Registration successful! Redirecting to login page...
            </div>
        <?php else: ?>
            <form method="POST" action="" class="auth-form" novalidate>
                <h1>Register</h1>
                
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" 
                           value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>"
                           class="<?= isset($errors['fullname']) ? 'error' : '' ?>">
                    <?php if (isset($errors['fullname'])): ?>
                        <span class="error-message"><?= $errors['fullname'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           class="<?= isset($errors['email']) ? 'error' : '' ?>">
                    <?php if (isset($errors['email'])): ?>
                        <span class="error-message"><?= $errors['email'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           class="<?= isset($errors['password']) ? 'error' : '' ?>">
                    <?php if (isset($errors['password'])): ?>
                        <span class="error-message"><?= $errors['password'] ?></span>
                    <?php endif; ?>
                    <small class="password-requirements">
                        Password must be at least 8 characters long and contain uppercase, lowercase letters and numbers
                    </small>
                </div>
                
                <button type="submit" class="button primary">Register</button>
                
                <div class="auth-links">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>