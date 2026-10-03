<?php
require_once "config/db.php";
require_once "includes/header.php";

// Get featured products
$result = mysqli_query(
    $conn,
    "SELECT id, name, description, price, image, stock
     FROM products
     ORDER BY id DESC
     LIMIT 4"
);
?>

<!-- HERO SECTION -->

<section class="hero">

    <div class="hero-content">

        <p class="hero-tag">
            Welcome to ShopSphere
        </p>

        <h1>
            Shop Smart.<br>
            Shop Better.
        </h1>

        <p class="hero-description">
            Discover quality products at great prices,
            all in one place.
        </p>

        <div class="hero-buttons">

            <a
                href="pages/products.php"
                class="btn btn-primary"
            >
                Explore Products
            </a>

            <?php if (!isset($_SESSION["user_id"])): ?>

                <a
                    href="pages/register.php"
                    class="btn btn-secondary"
                >
                    Create Account
                </a>

            <?php endif; ?>

        </div>

    </div>

</section>


<!-- FEATURED PRODUCTS -->

<section class="featured-section">

    <div class="section-heading">

        <p class="section-label">
            OUR COLLECTION
        </p>

        <h2>
            Featured Products
        </h2>

        <p>
            Explore some of our latest products.
        </p>

    </div>


    <div class="product-grid">

        <?php if (mysqli_num_rows($result) === 0): ?>

            <p>
                No products available yet.
            </p>

        <?php else: ?>

            <?php while ($product = mysqli_fetch_assoc($result)): ?>

                <div class="product-card">

                    <div class="product-image">

                        <?php if (!empty($product["image"])): ?>

                            <img
                                src="assets/images/<?php echo htmlspecialchars($product["image"]); ?>"
                                alt="<?php echo htmlspecialchars($product["name"]); ?>"
                            >

                        <?php else: ?>

                            <div class="no-image">
                                No Image
                            </div>

                        <?php endif; ?>

                    </div>


                    <div class="product-info">

                        <h3>
                            <?php echo htmlspecialchars($product["name"]); ?>
                        </h3>

                        <p class="product-description">
                            <?php
                            echo htmlspecialchars(
                                $product["description"]
                            );
                            ?>
                        </p>


                        <div class="product-bottom">

                            <span class="product-price">
                                ₹<?php echo number_format(
                                    $product["price"],
                                    2
                                ); ?>
                            </span>


                            <?php if ($product["stock"] > 0): ?>

                                <span class="stock available">
                                    In Stock
                                </span>

                            <?php else: ?>

                                <span class="stock unavailable">
                                    Out of Stock
                                </span>

                            <?php endif; ?>

                        </div>


                        <div class="product-actions">

                            <a
                                href="pages/product_details.php?id=<?php echo $product["id"]; ?>"
                                class="btn btn-outline"
                            >
                                View Details
                            </a>

                            <?php if ($product["stock"] > 0): ?>

                                <a
                                    href="pages/cart.php?add=<?php echo $product["id"]; ?>"
                                    class="btn btn-primary"
                                >
                                    Add to Cart
                                </a>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php endif; ?>

    </div>


    <div class="view-all">

        <a
            href="pages/products.php"
            class="btn btn-dark"
        >
            View All Products
        </a>

    </div>

</section>


<!-- FEATURES -->

<section class="features-section">

    <div class="feature">

        <div class="feature-icon">
            ✓
        </div>

        <h3>
            Quality Products
        </h3>

        <p>
            Carefully selected products for your everyday needs.
        </p>

    </div>


    <div class="feature">

        <div class="feature-icon">
            ⚡
        </div>

        <h3>
            Easy Shopping
        </h3>

        <p>
            Browse, add to cart and checkout with ease.
        </p>

    </div>


    <div class="feature">

        <div class="feature-icon">
            🔒
        </div>

        <h3>
            Secure Accounts
        </h3>

        <p>
            Your account information is securely handled.
        </p>

    </div>

</section>


<?php
require_once "includes/footer.php";
?>