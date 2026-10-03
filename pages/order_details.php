<?php
require_once "../config/db.php";
require_once "../includes/header.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

if (!isset($_GET["id"])) {
    header("Location: orders.php");
    exit;
}

$order_id = (int) $_GET["id"];


// Get order details
$stmt = mysqli_prepare(
    $conn,
    "SELECT orders.*,
            users.name AS customer_name
     FROM orders
     INNER JOIN users
     ON orders.user_id = users.id
     WHERE orders.id = ?
     AND orders.user_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $order_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$order_result = mysqli_stmt_get_result($stmt);

$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    die("Order not found.");
}


// Get order items
$stmt = mysqli_prepare(
    $conn,
    "SELECT order_items.quantity,
            order_items.price,
            products.name,
            products.image
     FROM order_items
     INNER JOIN products
     ON order_items.product_id = products.id
     WHERE order_items.order_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $order_id
);

mysqli_stmt_execute($stmt);

$items_result = mysqli_stmt_get_result($stmt);

?>

<h1>
    Order #<?php echo $order["id"]; ?> Details
</h1>

<p>
    Customer:
    <?php echo htmlspecialchars($order["customer_name"]); ?>
</p>

<p>
    Status:
    <?php echo htmlspecialchars($order["status"]); ?>
</p>

<p>
    Shipping Address:
    <?php echo htmlspecialchars($order["shipping_address"]); ?>
</p>

<p>
    Order Date:
    <?php echo htmlspecialchars($order["created_at"]); ?>
</p>

<hr>

<h2>Ordered Products</h2>

<?php
$calculated_total = 0;
?>

<?php while ($item = mysqli_fetch_assoc($items_result)): ?>

    <?php
    $subtotal =
        $item["price"] * $item["quantity"];

    $calculated_total += $subtotal;
    ?>

    <div>

        <?php if (!empty($item["image"])): ?>

            <img
                src="../assets/images/<?php echo htmlspecialchars($item["image"]); ?>"
                alt="<?php echo htmlspecialchars($item["name"]); ?>"
                width="120"
            >

        <?php endif; ?>

        <h3>
            <?php echo htmlspecialchars($item["name"]); ?>
        </h3>

        <p>
            Price:
            ₹<?php echo number_format($item["price"], 2); ?>
        </p>

        <p>
            Quantity:
            <?php echo $item["quantity"]; ?>
        </p>

        <p>
            Subtotal:
            ₹<?php echo number_format($subtotal, 2); ?>
        </p>

    </div>

    <hr>

<?php endwhile; ?>


<h2>
    Total:
    ₹<?php echo number_format($calculated_total, 2); ?>
</h2>

<br>

<a href="orders.php">
    ← Back to My Orders
</a>

<?php
require_once "../includes/footer.php";
?>