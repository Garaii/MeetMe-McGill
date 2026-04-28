<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

$error = ""; // variable to store error message if something goes wrong

// Only run registration logic if form was submitted with POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    // get name from form, or "" if missing, then remove extra spaces

    $email = strtolower(trim($_POST["email"] ?? ""));
    // get email from form + remove spaces + convert to lowercase

    $password = $_POST["password"] ?? "";
    // get password from form or "" if missing

    if ($name === "" || $email === "" || $password === "") {
        // check if any field is empty
        $error = "All fields are required.";

    } elseif (!is_mcgill_email($email)) {
        // if email is not a valid McGill email
        $error = "Only McGill emails are allowed.";

    } else { // basic validation passed

        $role = get_role_from_email($email);
        // decide if user should be "owner" or "user" based on email domain

        if ($role === null) {
            // extra safety check in case email is not valid
            $error = "Invalid email domain.";

        } else { // role is valid

            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            // prepare SQL query to check whether this email already exists

            $stmt->bind_param("s", $email);
            // bind the email value to the ? placeholder

            $stmt->execute();

            $stmt->store_result(); // store result so we can count rows

            if ($stmt->num_rows > 0) {
                // if at least one row exists, email is already registered
                $error = "An account with this email already exists.";

            } else { // email is new so we can create account

                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                // hash the password securely before storing it in database

                $insert = $conn->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
                // prepare SQL query to insert new user into users table

                $insert->bind_param("ssss", $name, $email, $password_hash, $role);
                // bind all 4 values as strings

                if ($insert->execute()) { // if insert worked successfully

                    $new_user_id = $insert->insert_id;
                    // get the ID of the newly created user

                    $_SESSION["user_id"] = $new_user_id; // save user ID in session so user is logged in
                    $_SESSION["name"] = $name;
                    $_SESSION["email"] = $email;
                    $_SESSION["role"] = $role;

                    redirect("dashboard.php"); // send user to dashboard after successful registration

                } else { // if insert fails
                    $error = "Registration failed. Please try again.";
                }

                $insert->close(); // close insert statement
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - MeetMe@McGill</title>
</head>
<body>
    <h2>Register</h2>

    <?php if ($error !== ""): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <label>Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Register</button>
    </form>

    <p><a href="login.php">Already have an account? Login</a></p>
</body>
</html>