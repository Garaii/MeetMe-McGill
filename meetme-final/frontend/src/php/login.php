<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

$error = ""; // variable to store error message if login fails

// Only run login logic if form was submitted with POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = strtolower(trim($_POST["email"] ?? ""));
    // get email from form, remove spaces, convert to lowercase

    $password = $_POST["password"] ?? "";
    // get password from form, or "" if missing

    if ($email === "" || $password === "") {
        // check if email or password is empty
        $error = "Email and password are required.";

    } else {
        // prepare SQL query to find user by email
        $stmt = $conn->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ?");

        $stmt->bind_param("s", $email);
        // bind email to the query

        $stmt->execute();
        // run query

        $result = $stmt->get_result();
        // get query result

        if ($result->num_rows === 1) {
            // if exactly one user was found

            $user = $result->fetch_assoc();
            // get user data as an associative array

            if (password_verify($password, $user["password_hash"])) {
                // check if entered password matches hashed password in database

                $_SESSION["user_id"] = $user["id"];
                // save user ID in session

                $_SESSION["name"] = $user["name"];
                // save user name in session

                $_SESSION["email"] = $user["email"];
                // save user email in session

                $_SESSION["role"] = $user["role"];
                // save user role in session

                redirect("dashboard.php");
                // send user to dashboard after successful login

            } else {
                // password is incorrect
                $error = "Invalid email or password.";
            }

        } else {
            // no matching user found
            $error = "Invalid email or password.";
        }

        $stmt->close();
        // close SQL statement
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - MeetMe@McGill</title>
</head>
<body>
    <h2>Login</h2>

    <?php if ($error !== ""): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>

    <p><a href="register.php">Do not have an account? Register</a></p>
</body>
</html>
