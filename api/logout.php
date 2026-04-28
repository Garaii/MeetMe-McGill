<?php

require_once __DIR__ . "/bootstrap.php";
// bootstrap.php starts the session

$_SESSION = [];
// remove all session variables

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
// remove session cookie if it exists

session_destroy();
// destroy the session completely

send_json([
    "success" => true,
    "message" => "Logged out successfully."
]);
// tell React that logout worked

?>