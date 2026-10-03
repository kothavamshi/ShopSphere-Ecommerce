<?php
require_once "../config/db.php";
require_once "../includes/header.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, password, role
         FROM users
         WHERE email = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {

        if (password_verify($password, $user["password"])) {

            // Prevent session fixation
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["role"] = $user["role"];

            header("Location: ../index.php");
            exit;

        } else {
            $message = "Invalid email or password.";
        }

    } else {
        $message = "Invalid email or password.";
    }
}
?>

<div class="auth-page">

    <div class="auth-card">

        <div class="auth-header">

            <p class="section-label">
                SHOPSPHERE
            </p>

            <h1>
                Welcome Back
            </h1>

            <p>
                Login to continue shopping.
            </p>

        </div>


        <?php if ($message): ?>

            <div class="form-message error">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <form method="POST" class="auth-form">

            <div class="form-group">

                <label>
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn btn-primary form-submit"
            >
                Login
            </button>

        </form>


        <p class="auth-footer">
            Don't have an account?
            <a href="register.php">
                Create one
            </a>
        </p>

    </div>

</div>


<?php
require_once "../includes/footer.php";
?>