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

    $option_id = $data["option_id"] ?? "";
    // get selected option from form

    if ($option_id === "") {
        $error = "Invalid request.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }
    // stop if no option ID was provided

    $db = get_db();
    // connect to database

    // check that this option belongs to a group meeting owned by the current owner
    $stmt = $db->prepare("
        SELECT
            go.id,
            go.start_time,
            go.end_time,
            gm.id AS group_meeting_id,
            gm.owner_id,
            gm.title,
            gm.description,
            gm.location,
            gm.status
        FROM group_options go
        JOIN group_meetings gm ON go.group_id = gm.id
        WHERE go.id = ? AND gm.owner_id = ?
    ");

    $stmt->execute([
        (int)$option_id,
        $owner_id
    ]);
    // run query

    $option = $stmt->fetch();
    // get query result

    if (!$option) {
        $error = "Access denied or option not found.";

        send_json([
            "success" => false,
            "message" => $error
        ], 404);
    }
    // stop if option does not belong to current owner

    if ($option["status"] === "finalized") {
        // do not finalize the same group meeting twice
        $error = "This group meeting has already been finalized.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    try {
        $db->beginTransaction();
        // start transaction so everything happens together

        // insert final chosen option into slots table
        $insert = $db->prepare("
            INSERT INTO slots (owner_id, title, description, location, start_time, end_time, is_active, slot_type)
            VALUES (?, ?, ?, ?, ?, ?, 1, 'group')
        ");
        // is_active = 1 means the final meeting is active/public

        $insert->execute([
            $owner_id,
            $option["title"],
            $option["description"],
            $option["location"],
            $option["start_time"],
            $option["end_time"]
        ]);
        // run insert query

        $slot_id = $db->lastInsertId();
        // get newly created slot ID

        // add everyone who voted for this option as an attendee
        $attendee_stmt = $db->prepare("
            INSERT OR IGNORE INTO group_attendees (group_id, slot_id, user_id)
            SELECT ?, ?, user_id
            FROM group_votes
            WHERE option_id = ?
        ");

        $attendee_stmt->execute([
            (int)$option["group_meeting_id"],
            (int)$slot_id,
            (int)$option_id
        ]);
        // run attendee insert query

        // mark the group meeting as finalized
        $update = $db->prepare("
            UPDATE group_meetings
            SET status = 'finalized', finalized_option_id = ?
            WHERE id = ? AND owner_id = ? AND status != 'finalized'
        ");

        $update->execute([
            (int)$option_id,
            (int)$option["group_meeting_id"],
            $owner_id
        ]);
        // run update query

        if ($update->rowCount() !== 1) {
            $db->rollBack();

            $error = "This group meeting has already been finalized.";

            send_json([
                "success" => false,
                "message" => $error
            ], 400);
        }

        $db->commit();
        // save all database changes

        $success = "Group meeting finalized and attendees added.";

        send_json([
            "success" => true,
            "message" => $success,
            "slot_id" => (int)$slot_id
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        // undo database changes if something failed

        $error = "Failed to finalize group meeting.";

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
