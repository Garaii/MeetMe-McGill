<!-- CONTRIBUTORS AND TASK COORDINATION FOR PHP FILES
MAZEN & SARAH

MAZEN: ADAPTED FILES TO WORK WITH SQLITE, SETUP DATABASE CONNECTION AND ENDPOINTS + UPDATED QUERIES
SARAH: BUILT FILE STRUCTURE WITH SQL QUERIES AND LOCAL TESTING, SETUP AUTHENTICATION AND USER SESSION TYPES
BOTH: CREATED ERROR HANDLING, FIXED EDGE CASES, AND TESTED ENDPOINTS WITH FRONTEND
-->

<?php
require_once "bootstrap.php";
require_once "auth.php";

require_owner();
// only logged-in owners can access this page

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run if form was submitted

    $owner_id = current_user_id();
    // get logged-in owner's ID from session

    $data = read_json();
    // read JSON data sent by React

    $slot_date = $data["slot_date"] ?? "";
    // get date from form

    $start_time = $data["start_time"] ?? "";
    // get start time from form

    $end_time = $data["end_time"] ?? "";
    // get end time from form

    $title = $data["title"] ?? "Available slot";
    // use default title if none was sent

    $location = trim($data["location"] ?? "");
    // get location from form

    if ($slot_date === "" || $start_time === "" || $end_time === "") {
        // check if any field is empty
        $error = "All fields are required.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);

    } else {
        if (strlen($start_time) === 5) {
            $start_time = $start_time . ":00";
        }

        if (strlen($end_time) === 5) {
            $end_time = $end_time . ":00";
        }

        $start_datetime = $slot_date . " " . $start_time;
        $end_datetime = $slot_date . " " . $end_time;
        // combine date and time for SQLite

        if (strtotime($end_datetime) <= strtotime($start_datetime)) {
            // basic check to make sure end time is after start time
            $error = "End time must be later than start time.";

            send_json([
                "success" => false,
                "message" => $error
            ], 400);
        }

        $db = get_db();
        // connect to database

        // insert new slot into database
        $stmt = $db->prepare("
            INSERT INTO slots (owner_id, title, location, start_time, end_time, is_active)
            VALUES (?, ?, ?, ?, ?, 0)
        ");
        // is_active = 0 means private/inactive by default

        if ($stmt->execute([$owner_id, $title, $location, $start_datetime, $end_datetime])) {
            $success = "Booking slot created successfully. It is currently private.";

            send_json([
                "success" => true,
                "message" => $success
            ]);

        } else {
            $error = "Failed to create slot.";

            send_json([
                "success" => false,
                "message" => $error
            ], 500);
        }
    }
}

send_json([
    "success" => false,
    "message" => "Invalid request method."
], 405);
?>
