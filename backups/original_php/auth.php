<?php
// auth.php

// Check if a session has not been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // start a new session
}

// Make sure the user is logged in before accessing a page
function require_login() {
    if (!isset($_SESSION['user_id'])) { // if missing -> not logged in
        header("Location: login.php"); // redirect to real login page
        exit();
    }
}

// Make sure the user is an owner (staff, prof)
function require_owner() {
    require_login(); // ensure user is logged in

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
        die("Access denied. Owners only.");
    }
}

// Function to get current user's ID
function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Function to get current user's name
function current_user_name() {
    return $_SESSION['name'] ?? null;
}

// Function to get current user's email
function current_user_email() {
    return $_SESSION['email'] ?? null;
}

// Function to get current user's role
function current_user_role() {
    return $_SESSION['role'] ?? null;
}
?>