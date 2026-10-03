<?php
require_once "../config/db.php";
require_once "../includes/header.php";

$result = mysqli_query(
    $conn,
    "SELECT
        categories.id,
        categories.name,
        COUNT(products.id) AS product_count
     FROM categories
     LEFT JOIN products
     ON categories.id = products.category_id
     GROUP BY categories.id, categories.name
     ORDER BY categories.name"
);
?>

<div class="categories-page">

    <div class="categories-header">

        <p class="section-label">
            SHOPSPHERE
        </p>

        <h1>
            Shop by Category
        </h1>

        <p>
            Explore products across different categories.
        </p>

    </div>


    <div class="category-grid">

        <?php while (
            $category = mysqli_fetch_assoc($result)
        ): ?>

            <div class="category-card">

                <h2>
                    <?php
                    echo htmlspecialchars(
                        $category["name"]
                    );
                    ?>
                </h2>

                <p>
                    <?php
                    echo $category["product_count"];
                    ?>
                    product<?php
                    echo $category["product_count"] == 1
                        ? ""
                        : "s";
                    ?>
                    available
                </p>

                <a
                    href="products.php?category=<?php echo $category["id"]; ?>"
                >
                    View Products →
                </a>

            </div>

        <?php endwhile; ?>

    </div>

</div>


<?php
require_once "../includes/footer.php";
?>