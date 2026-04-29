<?php
require_once "bootstrap.php";
require_once "auth.php";

require_login();
// only logged-in users can access this page

$error = "";

$owner_id = $_GET["owner_id"] ?? "";
// get owner ID from URL

if ($owner_id === "") {
    // check if owner ID is missing
    $error = "Owner not found.";

    send_json([
        "success" => false,
        "message" => $error
    ], 400);
}

$db = get_db();
// connect to database

// get selected owner information
$owner_stmt = $db->prepare("
    SELECT id, name, email
    FROM users
    WHERE id = ? AND role = 'owner'
");

$owner_stmt->execute([(int)$owner_id]);
// run query

$owner = $owner_stmt->fetch();
// get query result

if (!$owner) {
    // stop if owner does not exist
    $error = "Owner not found.";

    send_json([
        "success" => false,
        "message" => $error
    ], 404);
}

$slots = [];
// array to store available slots

// get active and unbooked slots for this owner
$slot_stmt = $db->prepare("
    SELECT
        s.id,
        COALESCE(s.location, '') AS location,
        date(s.start_time) AS slot_date,
        substr(time(s.start_time), 1, 5) AS start_time,
        substr(time(s.end_time), 1, 5) AS end_time
    FROM slots s
    LEFT JOIN bookings b ON b.slot_id = s.id
    WHERE s.owner_id = ?
      AND s.is_active = 1
      AND b.id IS NULL
    ORDER BY s.start_time
");

$slot_stmt->execute([(int)$owner_id]);
// run query

$slots = $slot_stmt->fetchAll();
// get query result

send_json([
    "success" => true,
    "owner" => $owner,
    "slots" => $slots
]);
// send owner and slots back to React
?>
