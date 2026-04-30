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
// only logged-in users can request a meeting

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run if form was submitted

    $user_id = current_user_id();
    // get logged-in user's ID from session

    $data = read_json();
    // read JSON data sent by React

    $owner_id = $data["owner_id"] ?? "";
    // get selected owner ID from form

    $message = trim($data["message"] ?? "");
    // get message from form

    $title = trim($data["title"] ?? "Meeting request");
    // use default title if none was sent

    $suggested_date = $data["suggested_date"] ?? ($data["requested_date"] ?? "");
    // get suggested date from form

    $start_time = $data["start_time"] ?? ($data["requested_start_time"] ?? "");
    // get suggested start time from form

    $end_time = $data["end_time"] ?? ($data["requested_end_time"] ?? "");
    // get suggested end time from form

    if ($owner_id === "" || $message === "" || $suggested_date === "" || $start_time === "" || $end_time === "") {
        // check if any field is empty
        $error = "Owner, date, time, and message are required.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    if ((int)$owner_id === (int)$user_id) {
        // stop users from requesting a meeting with themselves
        $error = "You cannot request a meeting with yourself.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    $requested_start = $suggested_date . " " . $start_time . ":00";
    $requested_end = $suggested_date . " " . $end_time . ":00";
    // combine date and time for SQLite

    if (strtotime($requested_start) === false || strtotime($requested_end) === false) {
        // check if date or time format is invalid
        $error = "Invalid date or time.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    if (strtotime($requested_end) <= strtotime($requested_start)) {
        // basic check to make sure end time is after start time
        $error = "End time must be later than start time.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    $db = get_db();
    // connect to database

    // check that selected owner exists
    $stmt = $db->prepare("
        SELECT id
        FROM users
        WHERE id = ? AND role = 'owner'
    ");

    $stmt->execute([(int)$owner_id]);
    // run query

    $owner = $stmt->fetch();
    // get query result

    if (!$owner) {
        // stop if owner does not exist
        $error = "Selected owner was not found.";

        send_json([
            "success" => false,
            "message" => $error
        ], 404);
    }

    // insert meeting request into database
    $insert = $db->prepare("
        INSERT INTO meeting_requests
            (requester_id, owner_id, title, message, requested_start, requested_end, status)
        VALUES
            (?, ?, ?, ?, ?, ?, 'pending')
    ");

    if ($insert->execute([
        $user_id,
        (int)$owner_id,
        $title,
        $message,
        $requested_start,
        $requested_end
    ])) {
        $success = "Meeting request sent successfully.";

        send_json([
            "success" => true,
            "message" => $success
        ]);
    } else {
        $error = "Failed to send meeting request.";

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
