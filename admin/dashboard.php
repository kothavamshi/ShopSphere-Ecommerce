<?php
require_once "../config/db.php";
require_once "../includes/header.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

// Count products
$product_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM products"
);
$product_count = mysqli_fetch_assoc($product_result)["total"];

// Count users
$user_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users"
);
$user_count = mysqli_fetch_assoc($user_result)["total"];

// Count orders
$order_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM orders"
);
$order_count = mysqli_fetch_assoc($order_result)["total"];

// Calculate total sales
$sales_result = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders"
);
$total_sales = mysqli_fetch_assoc($sales_result)["total"];
?>

<h1>Admin Dashboard</h1>

<p>
    Welcome, <?php echo htmlspecialchars($_SESSION["user_name"]); ?>!
</p>

<hr>

<h2>Dashboard Overview</h2>

<div>
    <h3>Products</h3>
    <p><?php echo $product_count; ?></p>
</div>

<div>
    <h3>Users</h3>
    <p><?php echo $user_count; ?></p>
</div>

<div>
    <h3>Orders</h3>
    <p><?php echo $order_count; ?></p>
</div>

<div>
    <h3>Total Sales</h3>
    <p>₹<?php echo number_format($total_sales, 2); ?></p>
</div>

<hr>

<h2>Admin Actions</h2>

<p>
    <a href="products.php">Manage Products</a>
</p>

<p>
    <a href="orders.php">Manage Orders</a>
</p>

<p>
    <a href="../pages/logout.php">Logout</a>
</p>

<?php
require_once "../includes/footer.php";
?>