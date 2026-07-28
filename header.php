<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATLAS HUB - Campus Space Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Header Navigation -->
    <header class="navbar">
        <a href="index.php" class="logo">
            ATLAS <span>HUB</span>
        </a>

        <div class="user-info">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                <span class="badge"><?php echo strtoupper(htmlspecialchars($_SESSION['user_role'])); ?></span>
                
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin.php" class="btn btn-sm btn-secondary">Admin Panel</a>
                    <a href="index.php" class="btn btn-sm btn-secondary">Floor Status</a>
                <?php endif; ?>

                <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-sm btn-secondary">Login</a>
                <a href="register.php" class="btn btn-sm">Register</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="container">
