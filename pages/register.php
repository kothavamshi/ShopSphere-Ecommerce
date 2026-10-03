<?php
require_once "../config/db.php";
require_once "../includes/header.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $phone = trim($_POST["phone"]);

    /*
     * Basic validation
     */
    if ($name === "" || $email === "" || $password === "") {

        $message = "Please fill all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters long.";

    } else {

        /*
         * Check whether email already exists
         */
        $check = mysqli_prepare(
            $conn,
            "SELECT id
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $check,
            "s",
            $email
        );

        mysqli_stmt_execute($check);

        $result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($result) > 0) {

            $message = "Email already registered.";

        } else {

            /*
             * Hash password before storing it
             */
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            /*
             * Insert new customer
             */
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users
                (name, email, password, phone, role)
                VALUES (?, ?, ?, ?, 'customer')"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $name,
                $email,
                $hashed_password,
                $phone
            );

            if (mysqli_stmt_execute($stmt)) {

                $message = "Registration successful. You can now login.";

            } else {

                $message = "Registration failed. Please try again.";
            }
        }
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
                Create Account
            </h1>

            <p>
                Register to start shopping.
            </p>

        </div>


        <?php if ($message): ?>

            <div class="form-message
                <?php
                echo strpos($message, "successful") !== false
                    ? "success"
                    : "error";
                ?>"
            >
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <form method="POST" class="auth-form">

            <div class="form-group">

                <label>
                    Full Name
                </label>

                <input
                    type="text"
                    name="name"
                    placeholder="Enter your name"
                    required
                >

            </div>


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
                    Phone Number
                </label>

                <input
                    type="text"
                    name="phone"
                    placeholder="Enter your phone number"
                >

            </div>


            <div class="form-group">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Minimum 6 characters"
                    minlength="6"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn btn-primary form-submit"
            >
                Create Account
            </button>

        </form>


        <p class="auth-footer">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </p>

    </div>

</div>


<?php
require_once "../includes/footer.php";
?>