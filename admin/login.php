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

        if (
            $user["role"] === "admin" &&
            password_verify($password, $user["password"])
        ) {

            // Prevent session fixation
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["role"] = $user["role"];

            header("Location: dashboard.php");
            exit;

        } else {

            $message = "Invalid admin credentials.";
        }

    } else {

        $message = "Invalid admin credentials.";
    }
}
?>

<h1>Admin Login</h1>

<?php if ($message): ?>

    <p><?php echo htmlspecialchars($message); ?></p>

<?php endif; ?>

<form method="POST">

    <label>Email:</label><br>

    <input
        type="email"
        name="email"
        required
    >

    <br><br>

    <label>Password:</label><br>

    <input
        type="password"
        name="password"
        required
    >

    <br><br>

    <button type="submit">
        Admin Login
    </button>

</form>

<?php
require_once "../includes/footer.php";
?>