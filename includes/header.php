<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ShopSphere</title>

    <link rel="stylesheet" href="/shopsphere/assets/css/style.css">

</head>

<body>

<header class="navbar">

    <div class="navbar-container">

        <!-- Logo -->
        <a href="/shopsphere/" class="logo">
            ShopSphere
        </a>


        <!-- Navigation -->
        <nav class="nav-links">

            <a href="/shopsphere/">
                Home
            </a>

            <a href="/shopsphere/pages/products.php">
                Products
            </a>

            <a href="/shopsphere/pages/categories.php">
                Categories
            </a>


            <?php if (isset($_SESSION["user_id"])): ?>

                <a href="/shopsphere/pages/cart.php">
                    Cart
                </a>

                <a href="/shopsphere/pages/orders.php">
                    My Orders
                </a>

                <?php if (
                    isset($_SESSION["role"]) &&
                    $_SESSION["role"] === "admin"
                ): ?>

                    <a href="/shopsphere/admin/dashboard.php">
                        Admin
                    </a>

                <?php endif; ?>

                <a href="/shopsphere/pages/logout.php">
                    Logout
                </a>

            <?php else: ?>

                <a href="/shopsphere/pages/login.php">
                    Login
                </a>

                <a href="/shopsphere/pages/register.php">
                    Register
                </a>

            <?php endif; ?>

        </nav>

    </div>

</header>


<main class="main-content">