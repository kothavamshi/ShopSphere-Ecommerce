<?php
require_once "../config/db.php";
require_once "../includes/header.php";

if (!isset($_GET["id"])) {
    header("Location: products.php");
    exit;
}

$product_id = (int) $_GET["id"];

$stmt = mysqli_prepare(
    $conn,
    "SELECT products.*,
            categories.name AS category_name
     FROM products
     LEFT JOIN categories
     ON products.category_id = categories.id
     WHERE products.id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $product_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$product = mysqli_fetch_assoc($result);

if (!$product) {
    die("Product not found.");
}
?>

<div class="product-details">

    <!-- Product Image -->

    <div class="product-details-image">

        <?php if (!empty($product["image"])): ?>

            <img
                src="../assets/images/<?php echo htmlspecialchars($product["image"]); ?>"
                alt="<?php echo htmlspecialchars($product["name"]); ?>"
            >

        <?php else: ?>

            <div class="no-image">
                No Image Available
            </div>

        <?php endif; ?>

    </div>


    <!-- Product Information -->

    <div class="product-details-info">

        <span class="category-badge">

            <?php
            echo htmlspecialchars(
                $product["category_name"] ?? "Uncategorized"
            );
            ?>

        </span>


        <h1>
            <?php echo htmlspecialchars($product["name"]); ?>
        </h1>


        <p class="details-description">

            <?php
            echo htmlspecialchars(
                $product["description"]
            );
            ?>

        </p>


        <div class="details-price">

            ₹<?php echo number_format(
                $product["price"],
                2
            ); ?>

        </div>


        <?php if ($product["stock"] > 0): ?>

            <p class="stock available">

                ✓
                <?php echo $product["stock"]; ?>
                units available

            </p>

        <?php else: ?>

            <p class="stock unavailable">
                Out of Stock
            </p>

        <?php endif; ?>


        <div class="details-actions">

            <?php if ($product["stock"] > 0): ?>

                <a
                    href="cart.php?add=<?php echo $product["id"]; ?>"
                    class="btn btn-primary"
                >
                    Add to Cart
                </a>

            <?php endif; ?>


            <a
                href="products.php"
                class="btn btn-outline"
            >
                ← Back to Products
            </a>

        </div>

    </div>

</div>


<!-- Product Information -->

<div class="product-info-box">

    <h2>
        Product Information
    </h2>

    <div class="info-row">

        <span>
            Category
        </span>

        <strong>
            <?php
            echo htmlspecialchars(
                $product["category_name"] ?? "Uncategorized"
            );
            ?>
        </strong>

    </div>


    <div class="info-row">

        <span>
            Availability
        </span>

        <strong>

            <?php if ($product["stock"] > 0): ?>

                In Stock

            <?php else: ?>

                Out of Stock

            <?php endif; ?>

        </strong>

    </div>


    <div class="info-row">

        <span>
            Product ID
        </span>

        <strong>
            #<?php echo $product["id"]; ?>
        </strong>

    </div>

</div>


<?php
require_once "../includes/footer.php";
?>