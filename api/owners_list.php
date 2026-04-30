<!-- CONTRIBUTORS AND TASK COORDINATION FOR PHP FILES
MAZEN & SARAH

MAZEN: ADAPTED FILES TO WORK WITH SQLITE, SETUP DATABASE CONNECTION AND ENDPOINTS + UPDATED QUERIES
SARAH: BUILT FILE STRUCTURE WITH SQL QUERIES AND LOCAL TESTING, SETUP AUTHENTICATION AND USER SESSION TYPES
BOTH: CREATED ERROR HANDLING, FIXED EDGE CASES, AND TESTED ENDPOINTS WITH FRONTEND
-->

<?php
require_once "bootstrap.php";
require_once "auth.php";

require_login();
// only logged-in users can see the list of owners

$user_id = current_user_id();
// get current logged-in user's ID

$owners = [];
// array to store owners

$db = get_db();
// connect to database

// get all users who can receive bookings or meeting requests
$stmt = $db->prepare("
    SELECT
        id,
        name,
        email
    FROM users
    WHERE role = 'owner'
      AND id != ?
    ORDER BY name
");

$stmt->execute([$user_id]);
// run query

$owners = $stmt->fetchAll();
// get query result

send_json([
    "success" => true,
    "owners" => $owners
]);
// send owners back to React
?>
