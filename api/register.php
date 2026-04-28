<?php

require_once __DIR__ . "/bootstrap.php";

$error = "";
// variable to store error message if something goes wrong

// Only run registration logic if React sent a POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_json([
        "success" => false,
        "message" => "Register only accepts POST requests."
    ], 405);
}

// React sends JSON, so we read JSON instead of using $_POST
$data = read_json();

$first_name = trim($data["first_name"] ?? "");
// get first name from request

$last_name = trim($data["last_name"] ?? "");
// get last name from request

$name = trim($first_name . " " . $last_name);
// combine first and last name

$mcgill_id = trim($data["mcgill_id"] ?? "");
// get McGill ID from request

$email = strtolower(trim($data["email"] ?? ""));
// get email from request + remove spaces + convert to lowercase

$password = $data["password"] ?? "";
// get password from request or "" if missing

$confirm_password = $data["confirm_password"] ?? "";
// get confirmed password from request

if ($name === "" || $email === "" || $password === "") {
    // check if any required field is empty
    $error = "Name, email, and password are required.";

    send_json([
        "success" => false,
        "message" => $error
    ], 400);

} elseif ($password !== $confirm_password) {
    // check if passwords match
    $error = "The two passwords do not match.";

    send_json([
        "success" => false,
        "message" => $error
    ], 400);

} elseif (!is_mcgill_email($email)) {
    // if email is not a valid McGill email
    $error = "Only McGill emails are allowed.";

    send_json([
        "success" => false,
        "message" => $error
    ], 400);

} else {
    // basic validation passed

    $role = get_role_from_email($email);
    // decide if user should be owner or user based on email domain

    if ($role === null) {
        // extra safety check in case email is not valid
        $error = "Invalid email domain.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    try {
        $db = get_db();

        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        // prepare SQL query to check whether this email already exists

        $stmt->execute([$email]);
        // run query with email value

        $existing_user = $stmt->fetch();
        // get result if one exists

        if ($existing_user) {
            // if at least one row exists, email is already registered
            $error = "An account with this email already exists.";

            send_json([
                "success" => false,
                "message" => $error
            ], 409);

        } else {
            // email is new so we can create account

            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            // hash the password securely before storing it in database

            $insert = $db->prepare("
                INSERT INTO users (name, email, password_hash, role)
                VALUES (?, ?, ?, ?)
            ");
            // prepare SQL query to insert new user into users table

            $insert->execute([$name, $email, $password_hash, $role]);
            // run insert query

            $new_user_id = $db->lastInsertId();
            // get the ID of the newly created user

            $_SESSION["user_id"] = $new_user_id;
            // save user ID in session so user is logged in

            $_SESSION["name"] = $name;
            $_SESSION["email"] = $email;
            $_SESSION["role"] = $role;

            send_json([
                "success" => true,
                "message" => "Account created successfully.",
                "user" => [
                    "id" => (int)$new_user_id,
                    "name" => $name,
                    "email" => $email,
                    "role" => $role
                ]
            ]);
        }

    } catch (Exception $e) {
        send_json([
            "success" => false,
            "message" => "Registration failed. Please try again.",
            "error" => $e->getMessage()
        ], 500);
    }
}

?>