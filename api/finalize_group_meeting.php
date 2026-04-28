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
            gm.title
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

    // insert final chosen option into slots table
    $insert = $db->prepare("
        INSERT INTO slots (owner_id, title, start_time, end_time, is_active, slot_type)
        VALUES (?, ?, ?, ?, 1, 'group')
    ");
    // is_active = 1 means the final meeting is active/public

    if ($insert->execute([
        $owner_id,
        $option["title"],
        $option["start_time"],
        $option["end_time"]
    ])) {
        $slot_id = $db->lastInsertId();
        // get newly created slot ID

        // mark the group meeting as finalized
        $update = $db->prepare("
            UPDATE group_meetings
            SET status = 'finalized', finalized_option_id = ?
            WHERE id = ? AND owner_id = ?
        ");

        $update->execute([
            (int)$option_id,
            (int)$option["group_meeting_id"],
            $owner_id
        ]);
        // run update query

        $success = "Group meeting finalized and slot created.";

        send_json([
            "success" => true,
            "message" => $success,
            "slot_id" => (int)$slot_id
        ]);
    } else {
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