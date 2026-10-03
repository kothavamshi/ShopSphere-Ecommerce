<?php
require_once "../config/db.php";
require_once "../includes/header.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];


// =====================================================
// ADD PRODUCT TO CART
// =====================================================

if (isset($_GET["add"])) {

    $product_id = (int) $_GET["add"];

    $product_stmt = mysqli_prepare(
        $conn,
        "SELECT stock
         FROM products
         WHERE id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $product_stmt,
        "i",
        $product_id
    );

    mysqli_stmt_execute($product_stmt);

    $product_result = mysqli_stmt_get_result($product_stmt);
    $product = mysqli_fetch_assoc($product_result);

    if ($product) {

        $stock = (int) $product["stock"];

        $check = mysqli_prepare(
            $conn,
            "SELECT id, quantity
             FROM cart
             WHERE user_id = ? AND product_id = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $check,
            "ii",
            $user_id,
            $product_id
        );

        mysqli_stmt_execute($check);

        $result = mysqli_stmt_get_result($check);

        if ($item = mysqli_fetch_assoc($result)) {

            if ($item["quantity"] < $stock) {

                $new_quantity = $item["quantity"] + 1;

                $update = mysqli_prepare(
                    $conn,
                    "UPDATE cart
                     SET quantity = ?
                     WHERE id = ? AND user_id = ?"
                );

                mysqli_stmt_bind_param(
                    $update,
                    "iii",
                    $new_quantity,
                    $item["id"],
                    $user_id
                );

                mysqli_stmt_execute($update);
            }

        } else {

            if ($stock > 0) {

                $insert = mysqli_prepare(
                    $conn,
                    "INSERT INTO cart
                    (user_id, product_id, quantity)
                    VALUES (?, ?, 1)"
                );

                mysqli_stmt_bind_param(
                    $insert,
                    "ii",
                    $user_id,
                    $product_id
                );

                mysqli_stmt_execute($insert);
            }
        }
    }

    header("Location: cart.php");
    exit;
}


// =====================================================
// INCREASE QUANTITY
// =====================================================

if (isset($_GET["increase"])) {

    $cart_id = (int) $_GET["increase"];

    $check = mysqli_prepare(
        $conn,
        "SELECT cart.quantity, products.stock
         FROM cart
         INNER JOIN products
         ON cart.product_id = products.id
         WHERE cart.id = ? AND cart.user_id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $check,
        "ii",
        $cart_id,
        $user_id
    );

    mysqli_stmt_execute($check);

    $result = mysqli_stmt_get_result($check);

    if ($item = mysqli_fetch_assoc($result)) {

        if ($item["quantity"] < $item["stock"]) {

            $update = mysqli_prepare(
                $conn,
                "UPDATE cart
                 SET quantity = quantity + 1
                 WHERE id = ? AND user_id = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "ii",
                $cart_id,
                $user_id
            );

            mysqli_stmt_execute($update);
        }
    }

    header("Location: cart.php");
    exit;
}


// =====================================================
// DECREASE QUANTITY
// =====================================================

if (isset($_GET["decrease"])) {

    $cart_id = (int) $_GET["decrease"];

    $check = mysqli_prepare(
        $conn,
        "SELECT quantity
         FROM cart
         WHERE id = ? AND user_id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $check,
        "ii",
        $cart_id,
        $user_id
    );

    mysqli_stmt_execute($check);

    $result = mysqli_stmt_get_result($check);

    if ($item = mysqli_fetch_assoc($result)) {

        if ($item["quantity"] > 1) {

            $update = mysqli_prepare(
                $conn,
                "UPDATE cart
                 SET quantity = quantity - 1
                 WHERE id = ? AND user_id = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "ii",
                $cart_id,
                $user_id
            );

            mysqli_stmt_execute($update);

        } else {

            $delete = mysqli_prepare(
                $conn,
                "DELETE FROM cart
                 WHERE id = ? AND user_id = ?"
            );

            mysqli_stmt_bind_param(
                $delete,
                "ii",
                $cart_id,
                $user_id
            );

            mysqli_stmt_execute($delete);
        }
    }

    header("Location: cart.php");
    exit;
}


// =====================================================
// REMOVE PRODUCT
// =====================================================

if (isset($_GET["remove"])) {

    $cart_id = (int) $_GET["remove"];

    $delete = mysqli_prepare(
        $conn,
        "DELETE FROM cart
         WHERE id = ? AND user_id = ?"
    );

    mysqli_stmt_bind_param(
        $delete,
        "ii",
        $cart_id,
        $user_id
    );

    mysqli_stmt_execute($delete);

    header("Location: cart.php");
    exit;
}


// =====================================================
// GET CART ITEMS
// =====================================================

$stmt = mysqli_prepare(
    $conn,
    "SELECT cart.id AS cart_id,
            products.name,
            products.price,
            products.image,
            products.stock,
            cart.quantity
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

?>

<div class="cart-page">

    <div class="page-header">

        <p class="section-label">
            SHOPSPHERE
        </p>

        <h1>
            Your Shopping Cart
        </h1>

        <p>
            Review your products before checkout.
        </p>

    </div>


    <?php if (mysqli_num_rows($result) === 0): ?>

        <div class="empty-cart">

            <div class="empty-cart-icon">
                🛒
            </div>

            <h2>
                Your cart is empty
            </h2>

            <p>
                Looks like you haven't added anything yet.
            </p>

            <a
                href="products.php"
                class="btn btn-primary"
            >
                Start Shopping
            </a>

        </div>

    <?php else: ?>

        <div class="cart-layout">

            <!-- CART ITEMS -->

            <div class="cart-items">

                <?php while ($item = mysqli_fetch_assoc($result)): ?>

                    <?php
                    $subtotal =
                        $item["price"] * $item["quantity"];

                    $total += $subtotal;
                    ?>

                    <div class="cart-item">

                        <!-- Image -->

                        <div class="cart-item-image">

                            <?php if (!empty($item["image"])): ?>

                                <img
                                    src="../assets/images/<?php echo htmlspecialchars($item["image"]); ?>"
                                    alt="<?php echo htmlspecialchars($item["name"]); ?>"
                                >

                            <?php endif; ?>

                        </div>


                        <!-- Details -->

                        <div class="cart-item-details">

                            <h2>
                                <?php echo htmlspecialchars($item["name"]); ?>
                            </h2>

                            <p class="cart-item-price">
                                ₹<?php echo number_format(
                                    $item["price"],
                                    2
                                ); ?>
                            </p>

                            <p class="cart-stock">
                                <?php echo $item["stock"]; ?>
                                available
                            </p>

                        </div>


                        <!-- Quantity -->

                        <div class="cart-quantity">

                            <span class="quantity-label">
                                Quantity
                            </span>

                            <div class="quantity-controls">

                                <a
                                    href="cart.php?decrease=<?php echo $item["cart_id"]; ?>"
                                    class="quantity-btn"
                                >
                                    −
                                </a>

                                <strong>
                                    <?php echo $item["quantity"]; ?>
                                </strong>

                                <?php if (
                                    $item["quantity"] <
                                    $item["stock"]
                                ): ?>

                                    <a
                                        href="cart.php?increase=<?php echo $item["cart_id"]; ?>"
                                        class="quantity-btn"
                                    >
                                        +
                                    </a>

                                <?php else: ?>

                                    <span class="quantity-btn disabled">
                                        +
                                    </span>

                                <?php endif; ?>

                            </div>

                        </div>


                        <!-- Subtotal -->

                        <div class="cart-item-total">

                            <span>
                                Subtotal
                            </span>

                            <strong>
                                ₹<?php echo number_format(
                                    $subtotal,
                                    2
                                ); ?>
                            </strong>

                        </div>


                        <!-- Remove -->

                        <a
                            href="cart.php?remove=<?php echo $item["cart_id"]; ?>"
                            class="remove-item"
                            onclick="return confirm('Remove this product from cart?');"
                        >
                            Remove
                        </a>

                    </div>

                <?php endwhile; ?>

            </div>


            <!-- ORDER SUMMARY -->

            <div class="cart-summary">

                <h2>
                    Order Summary
                </h2>

                <div class="summary-row">

                    <span>
                        Items
                    </span>

                    <span>
                        <?php echo mysqli_num_rows($result); ?>
                    </span>

                </div>

                <div class="summary-row">

                    <span>
                        Subtotal
                    </span>

                    <span>
                        ₹<?php echo number_format(
                            $total,
                            2
                        ); ?>
                    </span>

                </div>

                <div class="summary-divider"></div>

                <div class="summary-total">

                    <span>
                        Total
                    </span>

                    <strong>
                        ₹<?php echo number_format(
                            $total,
                            2
                        ); ?>
                    </strong>

                </div>

                <a
                    href="checkout.php"
                    class="btn btn-primary checkout-btn"
                >
                    Proceed to Checkout
                </a>

                <a
                    href="products.php"
                    class="continue-shopping"
                >
                    ← Continue Shopping
                </a>

            </div>

        </div>

    <?php endif; ?>

</div>


<?php
require_once "../includes/footer.php";
?>