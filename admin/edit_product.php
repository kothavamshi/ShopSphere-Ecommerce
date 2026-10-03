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

if (!isset($_GET["id"])) {
    header("Location: products.php");
    exit;
}

$product_id = (int) $_GET["id"];

if ($product_id <= 0) {
    header("Location: products.php");
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
 * UPDATE PRODUCT
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

        $message =
            "Invalid security token. Please try again.";

    } else {

        $name = trim($_POST["name"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $price = (float) ($_POST["price"] ?? 0);
        $stock = (int) ($_POST["stock"] ?? 0);
        $category_id = (int) ($_POST["category_id"] ?? 0);

        if (
            $name === "" ||
            $description === "" ||
            $price <= 0 ||
            $stock < 0 ||
            $category_id <= 0
        ) {

            $message =
                "Please enter valid product details.";

        } else {

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE products
                 SET category_id = ?,
                     name = ?,
                     description = ?,
                     price = ?,
                     stock = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "issdii",
                $category_id,
                $name,
                $description,
                $price,
                $stock,
                $product_id
            );

            if (mysqli_stmt_execute($stmt)) {

                $message =
                    "Product updated successfully.";

            } else {

                $message =
                    "Failed to update product.";
            }
        }
    }
}


/*
 * =====================================================
 * GET PRODUCT
 * =====================================================
 */

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM products
     WHERE id = ?
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


/*
 * =====================================================
 * GET CATEGORIES
 * =====================================================
 */

$categories = mysqli_query(
    $conn,
    "SELECT *
     FROM categories
     ORDER BY name"
);

?>

<div class="admin-page">

    <div class="admin-header">

        <div>

            <p class="section-label">
                SHOPSPHERE ADMIN
            </p>

            <h1>
                Edit Product
            </h1>

            <p>
                Update product information and inventory.
            </p>

        </div>

        <a
            href="products.php"
            class="btn btn-outline"
        >
            ← Back to Products
        </a>

    </div>


    <?php if ($message): ?>

        <div class="form-message">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <div class="admin-form-card">

        <div class="admin-section-title">

            <div>

                <h2>
                    Product Information
                </h2>

                <p>
                    Modify the selected product.
                </p>

            </div>

        </div>


        <form
            method="POST"
            class="admin-product-form"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo htmlspecialchars(
                    $_SESSION["csrf_token"]
                ); ?>"
            >


            <div class="form-group">

                <label>
                    Product Name
                </label>

                <input
                    type="text"
                    name="name"
                    value="<?php echo htmlspecialchars(
                        $product["name"]
                    ); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Description
                </label>

                <textarea
                    name="description"
                    rows="5"
                    required
                ><?php echo htmlspecialchars(
                    $product["description"]
                ); ?></textarea>

            </div>


            <div class="admin-form-row">

                <div class="form-group">

                    <label>
                        Price
                    </label>

                    <input
                        type="number"
                        name="price"
                        step="0.01"
                        min="0.01"
                        value="<?php echo htmlspecialchars(
                            $product["price"]
                        ); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Stock
                    </label>

                    <input
                        type="number"
                        name="stock"
                        min="0"
                        value="<?php echo htmlspecialchars(
                            $product["stock"]
                        ); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Category
                    </label>

                    <select
                        name="category_id"
                        required
                    >

                        <?php while (
                            $category =
                            mysqli_fetch_assoc($categories)
                        ): ?>

                            <option
                                value="<?php echo $category["id"]; ?>"
                                <?php
                                if (
                                    $category["id"]
                                    == $product["category_id"]
                                ) {
                                    echo "selected";
                                }
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

                </div>

            </div>


            <button
                type="submit"
                class="btn btn-primary"
            >
                Update Product
            </button>

        </form>

    </div>

</div>


<?php
require_once "../includes/footer.php";
?>