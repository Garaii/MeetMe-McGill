<?php
/* CONTRIBUTORS AND TASK COORDINATION FOR PHP FILES
MAZEN & SARAH

MAZEN: ADAPTED FILES TO WORK WITH SQLITE, SETUP DATABASE CONNECTION AND ENDPOINTS + UPDATED QUERIES
SARAH: BUILT FILE STRUCTURE WITH SQL QUERIES AND LOCAL TESTING, SETUP AUTHENTICATION AND USER SESSION TYPES
BOTH: CREATED ERROR HANDLING, FIXED EDGE CASES, AND TESTED ENDPOINTS WITH FRONTEND
*/

require_once "bootstrap.php";
require_once "auth.php";

require_login();
// only logged-in users can cancel their own bookings

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run if form was submitted

    $user_id = current_user_id();
    // get current logged-in user's ID

    $data = read_json();
    // read JSON data sent by React

    $booking_id = $data["booking_id"] ?? "";
    // get booking ID from form

    if ($booking_id === "") {
        // check if booking ID is missing
        $error = "Invalid cancel request.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    $db = get_db();
    // connect to database

    // check that this booking belongs to the current user
    $stmt = $db->prepare("
        SELECT
            b.id,
            owner.email AS owner_email
        FROM bookings b
        JOIN slots s ON b.slot_id = s.id
        JOIN users owner ON s.owner_id = owner.id
        WHERE b.id = ? AND b.user_id = ?
    ");

    $stmt->execute([(int)$booking_id, $user_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        // stop if booking does not belong to this user
        $error = "Booking not found.";

        send_json([
            "success" => false,
            "message" => $error
        ], 404);
    }

    $owner_email = $booking["owner_email"];
    // save owner email before deleting booking

    // delete the booking
    $delete = $db->prepare("
        DELETE FROM bookings
        WHERE id = ? AND user_id = ?
    ");

    if ($delete->execute([(int)$booking_id, $user_id])) {
        $success = "Booking cancelled successfully.";

        send_json([
            "success" => true,
            "message" => $success,
            "owner_email" => $owner_email
        ]);
    } else {
        $error = "Failed to cancel booking.";

        send_json([
            "success" => false,
            "message" => $error
        ], 500);
    }
}

send_json([
    "success" => false,
    "message" => "Invalid request method."
], 405);
?>