<?php
require_once "../config/db.php";
require_once "../includes/header.php";

$search = "";
$category_id = "";


/*
 * Get search value
 */
if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}


/*
 * Get category value
 *
 * Accept both:
 * products.php?category=2
 * products.php?category_id=2
 *
 * This keeps the page compatible with category links.
 */
if (isset($_GET["category"])) {

    $category_id = (int) $_GET["category"];

} elseif (isset($_GET["category_id"])) {

    $category_id = (int) $_GET["category_id"];
}


/*
 * Get all categories
 */
$categories = mysqli_query(
    $conn,
    "SELECT id, name
     FROM categories
     ORDER BY name"
);


/*
 * Build product query
 */
$sql = "SELECT
            products.*,
            categories.name AS category_name
        FROM products
        LEFT JOIN categories
        ON products.category_id = categories.id
        WHERE 1=1";


$params = [];
$types = "";


/*
 * Search filter
 */
if ($search !== "") {

    $sql .= " AND (
        products.name LIKE ?
        OR products.description LIKE ?
    )";

    $search_term = "%" . $search . "%";

    $params[] = $search_term;
    $params[] = $search_term;

    $types .= "ss";
}


/*
 * Category filter
 */
if ($category_id > 0) {

    $sql .= " AND products.category_id = ?";

    $params[] = $category_id;

    $types .= "i";
}


/*
 * Latest products first
 */
$sql .= " ORDER BY products.id DESC";


/*
 * Prepare query
 */
$stmt = mysqli_prepare(
    $conn,
    $sql
);


/*
 * Bind parameters if required
 */
if (!empty($params)) {

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );
}


/*
 * Execute query
 */
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>


<!-- =========================
     PRODUCTS HEADER
========================= -->

<div class="page-header">

    <p class="section-label">
        SHOPSPHERE STORE
    </p>

    <h1>
        Explore Products
    </h1>

    <p>
        Find the products you need from our collection.
    </p>

</div>


<!-- =========================
     SEARCH & FILTER
========================= -->

<div class="products-filter">

    <form method="GET">

        <input
            type="text"
            name="search"
            placeholder="Search products..."
            value="<?php echo htmlspecialchars($search); ?>"
        >


        <select name="category_id">

            <option value="0">
                All Categories
            </option>


            <?php while (
                $category = mysqli_fetch_assoc($categories)
            ): ?>

                <option
                    value="<?php echo $category["id"]; ?>"
                    <?php
                    echo (
                        $category_id == $category["id"]
                    )
                        ? "selected"
                        : "";
                    ?>
                >

                    <?php
                    echo htmlspecialchars(
                        $category["name"]
                    );
                    ?>

                </option>

            <?php endwhile; ?>

        </select>


        <button
            type="submit"
            class="btn btn-primary"
        >
            Search
        </button>


        <?php if (
            $search !== "" ||
            $category_id > 0
        ): ?>

            <a
                href="products.php"
                class="btn btn-outline"
            >
                Clear
            </a>

        <?php endif; ?>

    </form>

</div>


<!-- =========================
     PRODUCTS
========================= -->

<?php if (
    mysqli_num_rows($result) === 0
): ?>

    <div class="empty-state">

        <h2>
            No Products Found
        </h2>

        <p>
            Try another search or category.
        </p>

        <a
            href="products.php"
            class="btn btn-primary"
        >
            View All Products
        </a>

    </div>


<?php else: ?>

    <div class="product-grid">

        <?php while (
            $product = mysqli_fetch_assoc($result)
        ): ?>

            <div class="product-card">


                <!-- =========================
                     PRODUCT IMAGE
                ========================== -->

                <div class="product-card-image">

                    <?php if (
                        !empty($product["image"])
                    ): ?>

                        <img
                            src="../assets/images/<?php
                            echo htmlspecialchars(
                                $product["image"]
                            );
                            ?>"
                            alt="<?php
                            echo htmlspecialchars(
                                $product["name"]
                            );
                            ?>"
                        >

                    <?php else: ?>

                        <div class="no-image">
                            No Image
                        </div>

                    <?php endif; ?>

                </div>


                <!-- =========================
                     PRODUCT CONTENT
                ========================== -->

                <div class="product-card-content">


                    <!-- Category -->

                    <span class="category-badge">

                        <?php
                        echo htmlspecialchars(
                            $product["category_name"]
                                ?? "Uncategorized"
                        );
                        ?>

                    </span>


                    <!-- Product Name -->

                    <h3>

                        <?php
                        echo htmlspecialchars(
                            $product["name"]
                        );
                        ?>

                    </h3>


                    <!-- Description -->

                    <p class="product-description">

                        <?php
                        echo htmlspecialchars(
                            $product["description"]
                        );
                        ?>

                    </p>


                    <!-- Price + Stock -->

                    <div class="product-bottom">

                        <span class="product-price">

                            ₹<?php
                            echo number_format(
                                $product["price"],
                                2
                            );
                            ?>

                        </span>


                        <?php if (
                            $product["stock"] > 0
                        ): ?>

                            <span class="stock available">

                                <?php
                                echo $product["stock"];
                                ?>
                                left

                            </span>

                        <?php else: ?>

                            <span class="stock unavailable">

                                Out of Stock

                            </span>

                        <?php endif; ?>

                    </div>


                    <!-- =========================
                         PRODUCT ACTIONS
                    ========================== -->

                    <div class="product-card-actions">


                        <a
                            href="product_details.php?id=<?php
                            echo $product["id"];
                            ?>"
                            class="btn btn-outline"
                        >
                            View Details
                        </a>


                        <?php if (
                            $product["stock"] > 0
                        ): ?>

                            <a
                                href="cart.php?add=<?php
                                echo $product["id"];
                                ?>"
                                class="btn btn-primary"
                            >
                                Add to Cart
                            </a>

                        <?php endif; ?>


                    </div>

                </div>

            </div>

        <?php endwhile; ?>

    </div>

<?php endif; ?>


<?php
require_once "../includes/footer.php";
?>