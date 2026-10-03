<?php
require_once "../config/db.php";
require_once "../includes/header.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$message = "";


// =====================================================
// PLACE ORDER
// =====================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $shipping_address = trim($_POST["shipping_address"]);

    if ($shipping_address === "") {

        $message = "Please enter your shipping address.";

    } else {

        // Get cart items with current stock
        $stmt = mysqli_prepare(
            $conn,
            "SELECT cart.product_id,
                    cart.quantity,
                    products.name,
                    products.price,
                    products.stock
             FROM cart
             INNER JOIN products
             ON cart.product_id = products.id
             WHERE cart.user_id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $user_id
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $total = 0;
        $items = [];
        $stock_error = "";

        while ($item = mysqli_fetch_assoc($result)) {

            // Check stock
            if ($item["quantity"] > $item["stock"]) {

                $stock_error =
                    "Not enough stock available for: " .
                    $item["name"];

                break;
            }

            $subtotal =
                $item["price"] * $item["quantity"];

            $total += $subtotal;

            $items[] = $item;
        }


        // Check if cart is empty
        if (empty($items) && $stock_error === "") {

            $message = "Your cart is empty.";

        // Stock problem
        } elseif ($stock_error !== "") {

            $message = $stock_error;

        } else {

            // Start database transaction
            mysqli_begin_transaction($conn);

            try {

                // Create order
                $order = mysqli_prepare(
                    $conn,
                    "INSERT INTO orders
                    (user_id, total_amount, status, shipping_address)
                    VALUES (?, ?, 'pending', ?)"
                );

                mysqli_stmt_bind_param(
                    $order,
                    "ids",
                    $user_id,
                    $total,
                    $shipping_address
                );

                if (!mysqli_stmt_execute($order)) {
                    throw new Exception("Failed to create order.");
                }

                $order_id = mysqli_insert_id($conn);


                // Insert order items and reduce stock
                foreach ($items as $item) {

                    // Insert order item
                    $order_item = mysqli_prepare(
                        $conn,
                        "INSERT INTO order_items
                        (order_id, product_id, quantity, price)
                        VALUES (?, ?, ?, ?)"
                    );

                    mysqli_stmt_bind_param(
                        $order_item,
                        "iiid",
                        $order_id,
                        $item["product_id"],
                        $item["quantity"],
                        $item["price"]
                    );

                    if (!mysqli_stmt_execute($order_item)) {
                        throw new Exception(
                            "Failed to save order item."
                        );
                    }


                    // Reduce product stock
                    $stock_update = mysqli_prepare(
                        $conn,
                        "UPDATE products
                         SET stock = stock - ?
                         WHERE id = ?
                         AND stock >= ?"
                    );

                    mysqli_stmt_bind_param(
                        $stock_update,
                        "iii",
                        $item["quantity"],
                        $item["product_id"],
                        $item["quantity"]
                    );

                    if (!mysqli_stmt_execute($stock_update)) {
                        throw new Exception(
                            "Failed to update stock."
                        );
                    }

                    // Make sure stock was actually reduced
                    if (mysqli_stmt_affected_rows($stock_update) === 0) {
                        throw new Exception(
                            "Stock changed. Please try again."
                        );
                    }
                }


                // Clear cart
                $clear = mysqli_prepare(
                    $conn,
                    "DELETE FROM cart
                     WHERE user_id = ?"
                );

                mysqli_stmt_bind_param(
                    $clear,
                    "i",
                    $user_id
                );

                if (!mysqli_stmt_execute($clear)) {
                    throw new Exception(
                        "Failed to clear cart."
                    );
                }


                // Everything successful
                mysqli_commit($conn);

                header("Location: orders.php");
                exit;

            } catch (Exception $e) {

                // Undo all database changes
                mysqli_rollback($conn);

                $message =
                    "Order could not be placed. Please try again.";
            }
        }
    }
}

?>

<h1>Checkout</h1>


<?php if ($message): ?>

    <p>
        <?php echo htmlspecialchars($message); ?>
    </p>

<?php endif; ?>


<form method="POST">

    <label>
        Shipping Address:
    </label>

    <br>

    <textarea
        name="shipping_address"
        rows="5"
        cols="40"
        placeholder="Enter your complete shipping address"
        required
    ></textarea>

    <br><br>

    <button type="submit">
        Place Order
    </button>

</form>


<br>

<a href="cart.php">
    ← Back to Cart
</a>


<?php
require_once "../includes/footer.php";
?>