<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_login();
// only logged-in users can access this page

$user_id = current_user_id();
// get current logged-in user's ID

$booking_id = $_GET["booking_id"] ?? null;
// get booking ID from URL

if ($booking_id === null) {
    die("Invalid request.");
}
// stop if no booking ID provided

// check that this booking belongs to the current user
$stmt = $conn->prepare("
    SELECT bookings.id, slots.owner_id
    FROM bookings
    INNER JOIN slots ON bookings.slot_id = slots.id
    WHERE bookings.id = ? AND bookings.user_id = ?
");
// join bookings with slots to also get owner_id

$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Access denied.");
}
// stop if booking does not belong to this user

$data = $result->fetch_assoc();
// store booking + owner info

$stmt->close();

// delete the booking
$delete = $conn->prepare("DELETE FROM bookings WHERE id = ?");
$delete->bind_param("i", $booking_id);

if ($delete->execute()) {

    // OPTIONAL: prepare mailto link (for later use)
    $owner_id = $data["owner_id"];

    // get owner email
    $owner_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $owner_stmt->bind_param("i", $owner_id);
    $owner_stmt->execute();
    $owner_result = $owner_stmt->get_result();

    if ($owner_result->num_rows === 1) {
        $owner = $owner_result->fetch_assoc();

        // NEED TO FIX THIS: NOTIFY THE OWNER ONCE BOOKING IS CANCELLED!!!
        $mailto = "mailto:" . $owner["email"] . "?subject=Booking Cancelled&body=A user has cancelled their booking.";

        // for now just redirect
        redirect("dashboard.php");
    } else {
        redirect("dashboard.php");
    }

    $owner_stmt->close();

} else {
    die("Failed to cancel booking.");
}

$delete->close();
?>