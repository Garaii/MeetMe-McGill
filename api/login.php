<?php
/* CONTRIBUTORS AND TASK COORDINATION FOR PHP FILES
MAZEN & SARAH

MAZEN: ADAPTED FILES TO WORK WITH SQLITE, SETUP DATABASE CONNECTION AND ENDPOINTS + UPDATED QUERIES
SARAH: BUILT FILE STRUCTURE WITH SQL QUERIES AND LOCAL TESTING, SETUP AUTHENTICATION AND USER SESSION TYPES
BOTH: CREATED ERROR HANDLING, FIXED EDGE CASES, AND TESTED ENDPOINTS WITH FRONTEND
*/

// login.php
// Logs a user in and returns JSON for the React app.

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require_once __DIR__ . "/bootstrap.php";

$error = ""; // variable to store error message if login fails

// Only run login logic if React sent a POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_json([
        "success" => false,
        "message" => "Login only accepts POST requests."
    ], 405);
}

// React sends JSON, so we read the JSON body instead of using $_POST.
$data = read_json();

$email = strtolower(trim($data["email"] ?? ""));
// get email from request, remove spaces, convert to lowercase

$password = $data["password"] ?? "";
// get password, or "" if missing

if ($email === "" || $password === "") {
    // check if email or password is empty
    $error = "Email and password are required.";

    send_json([
        "success" => false,
        "message" => $error
    ], 400);
}

try {
    $db = get_db();

    // prepare SQL query to find user by email
    $stmt = $db->prepare("
        SELECT id, name, email, password_hash, role
        FROM users
        WHERE email = ?
    ");

    // run query with the email value
    $stmt->execute([$email]);

    // get user data as an associative array
    $user = $stmt->fetch();

    if ($user) {
        // if a user was found

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

            send_json([
                "success" => true,
                "message" => "Logged in successfully.",
                "user" => [
                    "id" => (int) $user["id"],
                    "name" => $user["name"],
                    "email" => $user["email"],
                    "role" => $user["role"]
                ]
            ]);
        } else {
            // password is incorrect
            $error = "Invalid email or password.";
        }
    } else {
        // no matching user found
        $error = "Invalid email or password.";
    }

    send_json([
        "success" => false,
        "message" => $error
    ], 401);

} catch (Exception $e) {
    send_json([
        "success" => false,
        "message" => "Login failed.",
        "error" => $e->getMessage()
    ], 500);
}
?>