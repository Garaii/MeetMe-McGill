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
// only logged-in owners can accept meeting requests

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run if request was submitted

    $owner_id = current_user_id();
    // get logged-in owner's ID from session

    $data = read_json();
    // read JSON data sent by React

    $request_id = $data["request_id"] ?? "";
    // get meeting request ID from React

    if ($request_id === "") {
        // check if request ID is missing
        $error = "Invalid request.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    $db = get_db();
    // connect to database

    // get the meeting request and make sure it belongs to this owner
    $stmt = $db->prepare("
        SELECT
            id,
            requester_id,
            owner_id,
            title,
            message,
            requested_start,
            requested_end,
            status
        FROM meeting_requests
        WHERE id = ? AND owner_id = ?
    ");

    $stmt->execute([
        (int)$request_id,
        $owner_id
    ]);
    // run query

    $request = $stmt->fetch();
    // get query result

    if (!$request) {
        // stop if request was not found or does not belong to this owner
        $error = "Meeting request not found.";

        send_json([
            "success" => false,
            "message" => $error
        ], 404);
    }

    if ($request["status"] !== "pending") {
        // stop if request was already accepted or declined
        $error = "This request has already been handled.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    try {
        $db->beginTransaction();
        // start transaction so all database changes happen together

        // create an appointment slot from the requested time
        $slot_stmt = $db->prepare("
            INSERT INTO slots
                (owner_id, title, description, start_time, end_time, is_active, slot_type)
            VALUES
                (?, ?, ?, ?, ?, 0, 'request')
        ");

        $slot_stmt->execute([
            $owner_id,
            $request["title"],
            $request["message"],
            $request["requested_start"],
            $request["requested_end"]
        ]);
        // insert slot into database

        $slot_id = $db->lastInsertId();
        // get newly created slot ID

        // book the new slot for the student who requested the meeting
        $booking_stmt = $db->prepare("
            INSERT INTO bookings
                (slot_id, user_id, status)
            VALUES
                (?, ?, 'booked')
        ");

        $booking_stmt->execute([
            (int)$slot_id,
            (int)$request["requester_id"]
        ]);
        // insert booking into database

        // update the original meeting request status
        $update_stmt = $db->prepare("
            UPDATE meeting_requests
            SET status = 'accepted'
            WHERE id = ? AND owner_id = ?
        ");

        $update_stmt->execute([
            (int)$request_id,
            $owner_id
        ]);
        // mark request as accepted

        $db->commit();
        // save all database changes

        $success = "Meeting request accepted and appointment created.";

        send_json([
            "success" => true,
            "message" => $success,
            "slot_id" => (int)$slot_id
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        // undo database changes if something failed

        $error = "Failed to accept meeting request.";

        send_json([
            "success" => false,
            "message" => $error,
            "error" => $e->getMessage()
        ], 500);
    }
}

send_json([
    "success" => false,
    "message" => "Invalid request method."
], 405);
?>