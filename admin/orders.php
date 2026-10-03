<?php
require_once "../config/db.php";
require_once "../includes/header.php";

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: login.php");
    exit;
}

$message = "";


/*
 * =====================================================
 * CSRF TOKEN
 * =====================================================
 */

if (empty($_SESSION["csrf_token"])) {

    $_SESSION["csrf_token"] = bin2hex(
        random_bytes(32)
    );
}


/*
 * =====================================================
 * UPDATE ORDER STATUS
 * =====================================================
 */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $csrf_token = $_POST["csrf_token"] ?? "";

    if (
        !hash_equals(
            $_SESSION["csrf_token"],
            $csrf_token
        )
    ) {

        $message = "Invalid security token. Please try again.";

    } else {

        $order_id = (int) ($_POST["order_id"] ?? 0);
        $status = $_POST["status"] ?? "";

        $allowed_statuses = [
            "pending",
            "confirmed",
            "shipped",
            "delivered",
            "cancelled"
        ];

        if (
            $order_id > 0 &&
            in_array(
                $status,
                $allowed_statuses,
                true
            )
        ) {

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE orders
                 SET status = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $status,
                $order_id
            );

            if (mysqli_stmt_execute($stmt)) {

                $message =
                    "Order status updated successfully.";

            } else {

                $message =
                    "Failed to update order status.";
            }

        } else {

            $message =
                "Invalid order status.";
        }
    }
}


/*
 * =====================================================
 * GET ORDERS
 * =====================================================
 */

$result = mysqli_query(
    $conn,
    "SELECT orders.*,
            users.name AS customer_name,
            users.email AS customer_email
     FROM orders
     INNER JOIN users
     ON orders.user_id = users.id
     ORDER BY orders.created_at DESC"
);

?>

<div class="admin-page">

    <!-- Header -->

    <div class="admin-header">

        <div>

            <p class="section-label">
                SHOPSPHERE ADMIN
            </p>

            <h1>
                Manage Orders
            </h1>

            <p>
                View customer orders and update their status.
            </p>

        </div>

        <a
            href="dashboard.php"
            class="btn btn-outline"
        >
            ← Dashboard
        </a>

    </div>


    <?php if ($message): ?>

        <div class="form-message">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <!-- Orders -->

    <div class="admin-section">

        <div class="admin-section-header">

            <div>

                <h2>
                    Customer Orders
                </h2>

                <p>
                    Manage order processing and delivery status.
                </p>

            </div>

            <span>
                <?php echo mysqli_num_rows($result); ?>
                Orders
            </span>

        </div>


        <div class="admin-orders-list">

            <?php if (mysqli_num_rows($result) === 0): ?>

                <div class="admin-empty">
                    No orders found.
                </div>

            <?php else: ?>

                <?php while (
                    $order =
                    mysqli_fetch_assoc($result)
                ): ?>

                    <div class="admin-order-card">

                        <!-- Order Header -->

                        <div class="admin-order-header">

                            <div>

                                <span class="order-label">
                                    ORDER
                                </span>

                                <h2>
                                    #<?php echo $order["id"]; ?>
                                </h2>

                            </div>


                            <span
                                class="order-status status-<?php echo strtolower(
                                    $order["status"]
                                ); ?>"
                            >
                                <?php echo ucfirst(
                                    htmlspecialchars(
                                        $order["status"]
                                    )
                                ); ?>
                            </span>

                        </div>


                        <!-- Order Information -->

                        <div class="admin-order-info">

                            <div>

                                <span>
                                    Customer
                                </span>

                                <strong>
                                    <?php echo htmlspecialchars(
                                        $order["customer_name"]
                                    ); ?>
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Email
                                </span>

                                <strong>
                                    <?php echo htmlspecialchars(
                                        $order["customer_email"]
                                    ); ?>
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Total
                                </span>

                                <strong class="order-total">

                                    ₹<?php echo number_format(
                                        $order["total_amount"],
                                        2
                                    ); ?>

                                </strong>

                            </div>


                            <div>

                                <span>
                                    Date
                                </span>

                                <strong>
                                    <?php echo htmlspecialchars(
                                        $order["created_at"]
                                    ); ?>
                                </strong>

                            </div>

                        </div>


                        <!-- Shipping Address -->

                        <div class="admin-shipping">

                            <span>
                                Shipping Address
                            </span>

                            <p>
                                <?php echo htmlspecialchars(
                                    $order["shipping_address"]
                                ); ?>
                            </p>

                        </div>


                        <!-- Actions -->

                        <div class="admin-order-footer">

                            <a
                                href="order_details.php?id=<?php echo $order["id"]; ?>"
                                class="btn btn-outline"
                            >
                                View Details
                            </a>


                            <form
                                method="POST"
                                class="status-form"
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?php echo htmlspecialchars(
                                        $_SESSION["csrf_token"]
                                    ); ?>"
                                >

                                <input
                                    type="hidden"
                                    name="order_id"
                                    value="<?php echo $order["id"]; ?>"
                                >


                                <select name="status">

                                    <?php
                                    $statuses = [
                                        "pending",
                                        "confirmed",
                                        "shipped",
                                        "delivered",
                                        "cancelled"
                                    ];
                                    ?>

                                    <?php foreach (
                                        $statuses as $status
                                    ): ?>

                                        <option
                                            value="<?php echo $status; ?>"
                                            <?php
                                            if (
                                                $order["status"]
                                                === $status
                                            ) {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            <?php echo ucfirst(
                                                $status
                                            ); ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>


                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Update Status
                                </button>

                            </form>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php endif; ?>

        </div>

    </div>

</div>


<?php
require_once "../includes/footer.php";
?>