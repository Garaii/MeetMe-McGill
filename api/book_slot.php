<?php
require_once "bootstrap.php";
require_once "auth.php";

require_login();
// only logged-in users can book a slot

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run if form was submitted

    $user_id = current_user_id();
    // get logged-in user's ID from session

    $data = read_json();
    // read JSON data sent by React

    $slot_id = $data["slot_id"] ?? "";
    // get slot ID from form

    if ($slot_id === "") {
        // check if slot ID is missing
        $error = "Invalid booking request.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    $db = get_db();
    // connect to database

    // check that this slot exists, is active, and is not owned by the current user
    $stmt = $db->prepare("
        SELECT id, owner_id, is_active, slot_type
        FROM slots
        WHERE id = ?
    ");

    $stmt->execute([(int)$slot_id]);
    $slot = $stmt->fetch();

    if (!$slot) {
        // stop if the slot does not exist
        $error = "Slot not found.";

        send_json([
            "success" => false,
            "message" => $error
        ], 404);
    }

    if ($slot["slot_type"] === "group") {
        // group meetings use group_attendees, not normal bookings
        $error = "Group meetings cannot be booked from this page.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    if ((int)$slot["is_active"] !== 1) {
        // stop if the slot is private/inactive
        $error = "This slot is not available.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    if ((int)$slot["owner_id"] === (int)$user_id) {
        // owners should not book their own slots
        $error = "You cannot book your own slot.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    // check whether the slot is already booked
    $check = $db->prepare("
        SELECT id
        FROM bookings
        WHERE slot_id = ?
    ");

    $check->execute([(int)$slot_id]);
    $existing_booking = $check->fetch();

    if ($existing_booking) {
        // stop if another user already booked the slot
        $error = "This slot has already been booked.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    // insert new booking into database
    $insert = $db->prepare("
        INSERT INTO bookings (slot_id, user_id)
        VALUES (?, ?)
    ");

    if ($insert->execute([(int)$slot_id, $user_id])) {
        $success = "Slot booked successfully.";

        send_json([
            "success" => true,
            "message" => $success
        ]);
    } else {
        $error = "Failed to book slot.";

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
