<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_login();
// only logged-in users can access this page

$user_id = current_user_id();
// get current logged-in user's ID

$slot_id = $_GET["slot_id"] ?? null;
// get slot ID from URL

if ($slot_id === null) {
    die("Invalid request.");
}
// stop if no slot ID was provided

// check that slot exists, is active, and is not already booked
$stmt = $conn->prepare("
    SELECT slots.id, slots.owner_id
    FROM slots
    WHERE slots.id = ? AND slots.is_active = 1
    AND slots.id NOT IN (
        SELECT slot_id FROM bookings
    )
");
// prepare query to find a valid available slot

$stmt->bind_param("i", $slot_id);
// bind slot ID to query

$stmt->execute();
// run query

$result = $stmt->get_result();
// get query result

if ($result->num_rows !== 1) {
    die("This slot is not available.");
}
// stop if slot does not exist, is not active, or is already booked

$slot = $result->fetch_assoc();
// store slot information

$stmt->close();
// close statement

// prevent owner from booking their own slot
if ($slot["owner_id"] == $user_id) {
    die("You cannot book your own slot.");
}
// stop if owner tries to book their own slot

// create booking
$insert = $conn->prepare("INSERT INTO bookings (slot_id, user_id) VALUES (?, ?)");
// prepare insert query for bookings table

$insert->bind_param("ii", $slot_id, $user_id);
// bind slot ID and user ID

if ($insert->execute()) {
    redirect("dashboard.php");
    // go back to dashboard after successful booking
} else {
    die("Failed to book slot.");
}
// show error if insert fails

$insert->close();
// close insert statement
?>