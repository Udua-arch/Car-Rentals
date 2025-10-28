<?php

session_start();


error_reporting(E_ALL);
ini_set('display_errors', 1);


header('Content-Type: text/html; charset=utf-8');
?>

<header>
    <a href="index.php" class="logo">iKarRental</a>
    <div class="auth-buttons">
        <?php if (isset($_SESSION['user'])): ?>
            <div class="user-menu">
                <span class="user-name">
                    Welcome, <?= htmlspecialchars($_SESSION['user']['fullname']) ?>
                </span>
                <?php if ($_SESSION['user']['is_admin']): ?>
                    <a href="admin.php" class="button">Admin Dashboard</a>
                <?php else: ?>
                    <a href="profile.php" class="button">My Profile</a>
                <?php endif; ?>
                <a href="logout.php" class="button logout-button">Logout</a>
            </div>
        <?php else: ?>
            <a href="login.php" class="button">Login</a>
            <a href="register.php" class="button primary">Registration</a>
        <?php endif; ?>
    </div>



</header>