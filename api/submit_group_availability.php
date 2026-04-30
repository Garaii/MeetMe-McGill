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
// only logged-in users can access this page

$user_id = current_user_id();
// get current logged-in user's ID

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // load group meeting options

    $group_meeting_id = $_GET["group_meeting_id"] ?? "";
    // get group meeting ID from URL

    if ($group_meeting_id === "") {
        $error = "Invalid request.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    $db = get_db();
    // connect to database

    // get group meeting info
    $meeting_stmt = $db->prepare("
        SELECT gm.id, gm.title, gm.description, u.name AS owner_name
        FROM group_meetings gm
        JOIN users u ON gm.owner_id = u.id
        WHERE gm.id = ? AND gm.status != 'finalized'
    ");

    $meeting_stmt->execute([(int)$group_meeting_id]);
    // run query

    $meeting = $meeting_stmt->fetch();
    // get query result

    if (!$meeting) {
        $error = "Group meeting not found or already finalized.";

        send_json([
            "success" => false,
            "message" => $error
        ], 404);
    }

    // get all meeting options
    $option_stmt = $db->prepare("
        SELECT
            id,
            date(start_time) AS option_date,
            substr(time(start_time), 1, 5) AS start_time,
            substr(time(end_time), 1, 5) AS end_time
        FROM group_options
        WHERE group_id = ?
        ORDER BY start_time
    ");

    $option_stmt->execute([(int)$group_meeting_id]);
    // run query

    $options = $option_stmt->fetchAll();
    // get query result

    send_json([
        "success" => true,
        "meeting" => $meeting,
        "options" => $options
    ]);
    // send meeting and options back to React
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run when form is submitted

    $data = read_json();
    // read JSON data sent by React

    $group_meeting_id = $data["group_meeting_id"] ?? "";
    // get group meeting ID from form

    $selected_options = $data["selected_options"] ?? [];
    // get selected meeting option IDs as an array

    if ($group_meeting_id === "" || !is_array($selected_options) || count($selected_options) === 0) {
        $error = "Please select at least one option.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    $db = get_db();
    // connect to database

    // make sure meeting exists and is still open
    $meeting_stmt = $db->prepare("
        SELECT id
        FROM group_meetings
        WHERE id = ? AND status != 'finalized'
    ");

    $meeting_stmt->execute([(int)$group_meeting_id]);
    // run query

    $meeting = $meeting_stmt->fetch();
    // get query result

    if (!$meeting) {
        $error = "Group meeting not found or already finalized.";

        send_json([
            "success" => false,
            "message" => $error
        ], 404);
    }

    // remove previous votes by this user for this meeting
    $delete_stmt = $db->prepare("
        DELETE FROM group_votes
        WHERE user_id = ?
        AND option_id IN (
            SELECT id FROM group_options WHERE group_id = ?
        )
    ");

    $delete_stmt->execute([
        $user_id,
        (int)$group_meeting_id
    ]);
    // run delete query

    // insert new votes
    $vote_stmt = $db->prepare("
        INSERT OR IGNORE INTO group_votes (option_id, user_id)
        VALUES (?, ?)
    ");

    $inserted_count = 0;

    foreach ($selected_options as $option_id) {
        // check that selected option belongs to this meeting
        $check_stmt = $db->prepare("
            SELECT id
            FROM group_options
            WHERE id = ? AND group_id = ?
        ");

        $check_stmt->execute([
            (int)$option_id,
            (int)$group_meeting_id
        ]);
        // run query

        $option = $check_stmt->fetch();
        // get query result

        if ($option) {
            $vote_stmt->execute([
                (int)$option_id,
                $user_id
            ]);
            // insert vote into database

            $inserted_count++;
        }
    }

    if ($inserted_count === 0) {
        $error = "No valid options were selected.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    $success = "Your availability was submitted successfully for $inserted_count option(s).";

    send_json([
        "success" => true,
        "message" => $success
    ]);
}

send_json([
    "success" => false,
    "message" => "Invalid request method."
], 405);
?>