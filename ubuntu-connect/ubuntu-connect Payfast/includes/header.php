<?php
//This code starts session tracking to allow access checks across all the pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pulls the main database config object name
require_once 'authentication.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubuntu Connect</title>

<!--Importing bootstrap-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

   <!-- <link rel="stylesheet" href="<?= base_URL ?>/css/style.css"> LOCAL-->
    <link rel="stylesheet" href="<?= base_URL ?>/css/style.css">
</head>

<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        
        <a class="navbar-brand" href="connect-index.php">Ubuntu Connect</a>
         <div class="navbar-nav ms-auto">

            <a class="nav-link" href="connect-viewproducts.php">Browse</a>

            <?php if (isset($_SESSION['userID'])): ?>
                <a class="nav-link" href="connect-messages.php">Messages</a>
                <span class="badge bg-danger"><?= getUnreadMessages() ?></span>
            <?php endif;?>

            <?php if (isset($_SESSION['userID'])): ?>
                <a class="nav-link" href="connect-createproduct.php">Sell Item</a>

                <!--Admin link-->
                <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'admin'): ?>
                    <a class="nav-link text-warning" href="admin/admin-dashboard.php">Admin Panel</a>
                <?php endif; ?>

                <a class="nav-link text-danger" href="connect-logout.php">Logout (<?= htmlspecialchars($_SESSION['userName']) ?>)</a>
                <?php else: ?> <!--Guest user navigation-->
                <a class="nav-link" href="connect-login.php">Login</a>
                <a class="nav-link" href="connect-register.php">Register</a>
            <?php endif; ?>

        </div>
    </div>
</nav>
<div class="container">