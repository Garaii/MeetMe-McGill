<?php
/* CONTRIBUTORS AND TASK COORDINATION FOR PHP FILES
MAZEN & SARAH

MAZEN: ADAPTED FILES TO WORK WITH SQLITE, SETUP DATABASE CONNECTION AND ENDPOINTS + UPDATED QUERIES
SARAH: BUILT FILE STRUCTURE WITH SQL QUERIES AND LOCAL TESTING, SETUP AUTHENTICATION AND USER SESSION TYPES
BOTH: CREATED ERROR HANDLING, FIXED EDGE CASES, AND TESTED ENDPOINTS WITH FRONTEND
*/

require_once "bootstrap.php";
require_once "auth.php";

require_owner();
// only logged-in owners can access this page

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run if form was submitted

    $owner_id = current_user_id();
    // get current logged-in owner's ID

    $data = read_json();
    // read JSON data sent by React

    $slot_id = $data["slot_id"] ?? "";
    // get slot ID from form

    if ($slot_id === "") {
        // check if any field is empty
        $error = "Invalid request.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    $db = get_db();
    // connect to database

    // check that this slot belongs to the current owner
    $stmt = $db->prepare("
        SELECT id, slot_type
        FROM slots
        WHERE id = ? AND owner_id = ?
    ");

    $stmt->execute([(int)$slot_id, $owner_id]);
    $slot = $stmt->fetch();

    if (!$slot) {
        // stop if the slot does not belong to this owner
        $error = "Access denied.";

        send_json([
            "success" => false,
            "message" => $error
        ], 403);
    }

    // check whether this slot is already booked
    $check = $db->prepare("
        SELECT bookings.id, users.email AS booked_user_email
        FROM bookings
        INNER JOIN users ON bookings.user_id = users.id
        WHERE bookings.slot_id = ?
    ");

    $check->execute([(int)$slot_id]);
    $booking = $check->fetch();

    if ($booking) {
        // delete booking then send email
        $deleteBooking = $db->prepare("DELETE FROM bookings WHERE slot_id = ?");
        $deleteBooking->execute([(int)$slot_id]);
    }

    // delete group attendees if this is a group slot
    $delete_attendees = $db->prepare("
        DELETE FROM group_attendees
        WHERE slot_id = ?
    ");

    $delete_attendees->execute([(int)$slot_id]);
    // run delete query

    // delete the slot
    $delete = $db->prepare("
        DELETE FROM slots
        WHERE id = ? AND owner_id = ?
    ");

    if ($delete->execute([(int)$slot_id, $owner_id])) {
        $success = "Slot deleted successfully.";

        send_json([
            "success" => true,
            "message" => $success,
            "booked_user_email" => $booking ? $booking["booked_user_email"] : null
        ]);
    } else {
        $error = "Failed to delete slot.";

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
