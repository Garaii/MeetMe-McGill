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
// only owners can decline requests sent to them

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run if form was submitted

    $owner_id = current_user_id();
    // get logged-in owner's ID from session

    $data = read_json();
    // read JSON data sent by React

    $request_id = $data["request_id"] ?? "";
    // get request ID from form

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

    // update request only if it belongs to this owner and is still pending
    $stmt = $db->prepare("
        UPDATE meeting_requests
        SET status = 'declined'
        WHERE id = ? AND owner_id = ? AND status = 'pending'
    ");

    $stmt->execute([(int)$request_id, $owner_id]);
    // run update query

    if ($stmt->rowCount() === 1) {
        $success = "Meeting request declined.";

        send_json([
            "success" => true,
            "message" => $success
        ]);
    } else {
        $error = "Request not found or already handled.";

        send_json([
            "success" => false,
            "message" => $error
        ], 404);
    }
}

send_json([
    "success" => false,
    "message" => "Invalid request method."
], 405);
?>