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
 * DELETE PRODUCT
 * =====================================================
 */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_product"])
) {

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

        $product_id = (int) $_POST["delete_product"];

        if ($product_id > 0) {

            $stmt = mysqli_prepare(
                $conn,
                "DELETE FROM products
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $product_id
            );

            if (mysqli_stmt_execute($stmt)) {

                $message =
                    "Product deleted successfully.";

            } else {

                $message =
                    "Failed to delete product.";
            }

        } else {

            $message =
                "Invalid product.";
        }
    }
}


/*
 * =====================================================
 * ADD PRODUCT
 * =====================================================
 */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["add_product"])
) {

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
                "INSERT INTO products
                (
                    category_id,
                    name,
                    description,
                    price,
                    image,
                    stock
                )
                VALUES (?, ?, ?, ?, '', ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "issdi",
                $category_id,
                $name,
                $description,
                $price,
                $stock
            );

            if (mysqli_stmt_execute($stmt)) {

                $message =
                    "Product added successfully.";

            } else {

                $message =
                    "Failed to add product.";
            }
        }
    }
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


/*
 * =====================================================
 * GET PRODUCTS
 * =====================================================
 */

$products = mysqli_query(
    $conn,
    "SELECT products.*,
            categories.name AS category_name
     FROM products
     LEFT JOIN categories
     ON products.category_id = categories.id
     ORDER BY products.id DESC"
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
                Manage Products
            </h1>

            <p>
                Add, edit and manage store products.
            </p>

        </div>

        <a
            href="dashboard.php"
            class="btn btn-outline"
        >
            ← Dashboard
        </a>

    </div>


    <!-- Message -->

    <?php if ($message): ?>

        <div class="form-message">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <!-- Add Product -->

    <div class="admin-form-card">

        <div class="admin-section-title">

            <div>

                <h2>
                    Add New Product
                </h2>

                <p>
                    Add a product to your store catalog.
                </p>

            </div>

        </div>


        <form
            method="POST"
            class="admin-product-form"
        >

            <input
                type="hidden"
                name="add_product"
                value="1"
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
                    placeholder="Enter product name"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Description
                </label>

                <textarea
                    name="description"
                    rows="4"
                    placeholder="Enter product description"
                    required
                ></textarea>

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
                        placeholder="1999"
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
                        placeholder="20"
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
                + Add Product
            </button>

        </form>

    </div>


    <!-- Product List -->

    <div class="admin-section">

        <div class="admin-section-header">

            <div>

                <h2>
                    Existing Products
                </h2>

                <p>
                    Manage products currently in the store.
                </p>

            </div>

            <span>
                <?php echo mysqli_num_rows($products); ?>
                Products
            </span>

        </div>


        <div class="admin-table-wrapper">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>
                            Product
                        </th>

                        <th>
                            Category
                        </th>

                        <th>
                            Price
                        </th>

                        <th>
                            Stock
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php while (
                        $product =
                        mysqli_fetch_assoc($products)
                    ): ?>

                        <tr>

                            <td>

                                <div class="admin-product-name">

                                    <?php if (
                                        !empty(
                                            $product["image"]
                                        )
                                    ): ?>

                                        <img
                                            src="../assets/images/<?php echo htmlspecialchars(
                                                $product["image"]
                                            ); ?>"
                                            alt=""
                                        >

                                    <?php endif; ?>


                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $product["name"]
                                        );
                                        ?>
                                    </strong>

                                </div>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $product["category_name"]
                                    ?? "None"
                                );
                                ?>

                            </td>


                            <td>

                                ₹<?php
                                echo number_format(
                                    $product["price"],
                                    2
                                );
                                ?>

                            </td>


                            <td>

                                <?php if (
                                    $product["stock"] > 0
                                ): ?>

                                    <span class="stock available">

                                        <?php
                                        echo $product["stock"];
                                        ?>

                                    </span>

                                <?php else: ?>

                                    <span class="stock unavailable">
                                        Out of Stock
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <div class="admin-actions-inline">

                                    <a
                                        href="edit_product.php?id=<?php echo $product["id"]; ?>"
                                        class="table-action edit"
                                    >
                                        Edit
                                    </a>


                                    <form
                                        method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm('Delete this product?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="delete_product"
                                            value="<?php echo $product["id"]; ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?php echo htmlspecialchars(
                                                $_SESSION["csrf_token"]
                                            ); ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="table-action delete"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<?php
require_once "../includes/footer.php";
?>