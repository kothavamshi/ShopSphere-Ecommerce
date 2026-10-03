<?php
require_once "../config/db.php";
require_once "../includes/header.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, total_amount, status, shipping_address, created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
?>

<h1>My Orders</h1>

<?php if (mysqli_num_rows($result) === 0): ?>

    <p>You have not placed any orders yet.</p>

<?php else: ?>

    <?php while ($order = mysqli_fetch_assoc($result)): ?>

        <div>

            <h2>
                Order #<?php echo $order["id"]; ?>
            </h2>

            <p>
                Total:
                ₹<?php echo number_format($order["total_amount"], 2); ?>
            </p>

            <p>
                Status:
                <?php echo htmlspecialchars($order["status"]); ?>
            </p>

            <p>
                Address:
                <?php echo htmlspecialchars($order["shipping_address"]); ?>
            </p>

            <p>
                Date:
                <?php echo htmlspecialchars($order["created_at"]); ?>
            </p>
            <p>
    <a href="order_details.php?id=<?php echo $order["id"]; ?>">
        View Order Details
    </a>
</p>

        </div>

        <hr>

    <?php endwhile; ?>

<?php endif; ?>

<?php
require_once "../includes/footer.php";
?>