<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_owner();
// only logged-in owners can access this page

$owner_id = current_user_id();
// get current logged-in owner's ID

$option_id = $_GET["option_id"] ?? null;
// get selected option ID from URL

if ($option_id === null) {
    die("Invalid request.");
}
// stop if no option ID was provided

// check that this option belongs to a group meeting owned by the current owner
$stmt = $conn->prepare("
    SELECT 
        group_meeting_options.id,
        group_meeting_options.option_date,
        group_meeting_options.start_time,
        group_meeting_options.end_time,
        group_meetings.id AS group_meeting_id,
        group_meetings.owner_id,
        group_meetings.title
    FROM group_meeting_options
    INNER JOIN group_meetings 
        ON group_meeting_options.group_meeting_id = group_meetings.id
    WHERE group_meeting_options.id = ? AND group_meetings.owner_id = ?
");
$stmt->bind_param("ii", $option_id, $owner_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Access denied or option not found.");
}
// stop if option does not belong to current owner

$option = $result->fetch_assoc();
// store selected option info

$stmt->close();

// insert final chosen option into slots table
$insert = $conn->prepare("
    INSERT INTO slots (owner_id, slot_date, start_time, end_time, is_active)
    VALUES (?, ?, ?, ?, 1)
");
// is_active = 1 means the final meeting is active/public

$insert->bind_param(
    "isss",
    $owner_id,
    $option["option_date"],
    $option["start_time"],
    $option["end_time"]
);

if ($insert->execute()) {
    // optionally, you could later mark the group meeting as finalized in the DB
    redirect("dashboard.php");
} else {
    die("Failed to finalize group meeting.");
}

$insert->close();
?>