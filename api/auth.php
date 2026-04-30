<?php
/* CONTRIBUTORS AND TASK COORDINATION FOR PHP FILES
MAZEN & SARAH

MAZEN: ADAPTED FILES TO WORK WITH SQLITE, SETUP DATABASE CONNECTION AND ENDPOINTS + UPDATED QUERIES
SARAH: BUILT FILE STRUCTURE WITH SQL QUERIES AND LOCAL TESTING, SETUP AUTHENTICATION AND USER SESSION TYPES
BOTH: CREATED ERROR HANDLING, FIXED EDGE CASES, AND TESTED ENDPOINTS WITH FRONTEND
*/

// auth.php
// Checks whether the current browser session has a logged-in user.

require_once __DIR__ . "/bootstrap.php";

// Function to require a logged-in user
function require_login() {
    if (!isset($_SESSION["user_id"])) {
        send_json([
            "success" => false,
            "message" => "You must be logged in."
        ], 401);
    }
}

// Function to require owner access
function require_owner() {
    require_login();

    if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "owner") {
        send_json([
            "success" => false,
            "message" => "Access denied. Owners only."
        ], 403);
    }
}

// Function to get current user's ID
function current_user_id() {
    return $_SESSION["user_id"] ?? null;
}

// Function to get current user's name
function current_user_name() {
    return $_SESSION["name"] ?? null;
}

// Function to get current user's email
function current_user_email() {
    return $_SESSION["email"] ?? null;
}

// Function to get current user's role
function current_user_role() {
    return $_SESSION["role"] ?? null;
}

// If this file is opened directly by React, return the session status
if (basename($_SERVER["SCRIPT_NAME"]) === "auth.php") {
    if (!isset($_SESSION["user_id"])) {
        send_json([
            "success" => true,
            "logged_in" => false,
            "user" => null
        ]);
    }

    send_json([
        "success" => true,
        "logged_in" => true,
        "user" => [
            "id" => (int)$_SESSION["user_id"],
            "name" => $_SESSION["name"] ?? "",
            "email" => $_SESSION["email"] ?? "",
            "role" => $_SESSION["role"] ?? ""
        ]
    ]);
}

?>