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

$owner_id = current_user_id();
// get current logged-in owner's ID

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // get existing group meetings for this owner

    $db = get_db();
    // connect to database

    $stmt = $db->prepare("
        SELECT id, title, description, location, status, created_at
        FROM group_meetings
        WHERE owner_id = ?
        ORDER BY created_at DESC
    ");

    $stmt->execute([$owner_id]);
    // run query

    $meetings = $stmt->fetchAll();
    // get query result

    send_json([
        "success" => true,
        "meetings" => $meetings
    ]);
    // send meetings back to React
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run when form is submitted

    $data = read_json();
    // read JSON data sent by React

    $title = trim($data["title"] ?? "");
    // get group meeting title

    $description = trim($data["description"] ?? "");
    // get optional description

    $location = trim($data["location"] ?? "");
    // get optional location

    $options = $data["options"] ?? [];
    // get all submitted options as an array

    if ($title === "") {
        $error = "Meeting title is required.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);

    } elseif (!is_array($options) || count($options) === 0) {
        $error = "At least one meeting option is required.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);

    } else {
        $valid_options = [];
        // store valid options after checking them

        foreach ($options as $option) {
            $option_date = trim($option["option_date"] ?? "");
            $start_time = trim($option["start_time"] ?? "");
            $end_time = trim($option["end_time"] ?? "");

            // skip completely empty rows
            if ($option_date === "" && $start_time === "" && $end_time === "") {
                continue;
            }

            if ($option_date === "" || $start_time === "" || $end_time === "") {
                $error = "Each meeting option must have date, start time, and end time.";
                break;
            }

            if ($end_time <= $start_time) {
                $error = "Each meeting option must have an end time later than its start time.";
                break;
            }

            $valid_options[] = [
                "start_time" => $option_date . " " . $start_time . ":00",
                "end_time" => $option_date . " " . $end_time . ":00"
            ];
            // combine date and time for SQLite
        }

        if ($error !== "") {
            send_json([
                "success" => false,
                "message" => $error
            ], 400);
        }

        if (count($valid_options) === 0) {
            $error = "Please provide at least one valid meeting option.";

            send_json([
                "success" => false,
                "message" => $error
            ], 400);
        }

        $db = get_db();
        // connect to database

        try {
            $db->beginTransaction();
            // start transaction so meeting and options are created together

            // insert group meeting first
            $meeting_stmt = $db->prepare("
                INSERT INTO group_meetings (owner_id, title, description, location)
                VALUES (?, ?, ?, ?)
            ");

            $meeting_stmt->execute([$owner_id, $title, $description, $location]);
            // run insert query

            $group_meeting_id = $db->lastInsertId();
            // get newly created group meeting ID

            // insert each meeting option
            $option_stmt = $db->prepare("
                INSERT INTO group_options (group_id, start_time, end_time)
                VALUES (?, ?, ?)
            ");

            $created_count = 0;

            foreach ($valid_options as $option) {
                $option_stmt->execute([
                    (int)$group_meeting_id,
                    $option["start_time"],
                    $option["end_time"]
                ]);

                $created_count++;
            }

            $db->commit();
            // save all database changes

            $success = "Group meeting created successfully with $created_count option(s).";

            send_json([
                "success" => true,
                "message" => $success,
                "group_meeting_id" => (int)$group_meeting_id,
                "meeting" => [
                    "id" => (int)$group_meeting_id,
                    "title" => $title,
                    "description" => $description,
                    "location" => $location,
                    "status" => "open"
                ]
            ]);

        } catch (Exception $e) {
            $db->rollBack();
            // undo database changes if something failed

            $error = "Failed to create group meeting.";

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
