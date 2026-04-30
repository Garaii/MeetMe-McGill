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
