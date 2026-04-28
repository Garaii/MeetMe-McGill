<?php
require_once "bootstrap.php";
require_once "auth.php";

require_owner();
// only logged-in owners can access this page

$owner_id = current_user_id();
// get current logged-in owner's ID

$group_meeting_id = $_GET["group_meeting_id"] ?? "";
// get group meeting ID from URL

if ($group_meeting_id === "") {
    send_json([
        "success" => false,
        "message" => "Invalid request."
    ], 400);
}
// stop if no group meeting ID was provided

$db = get_db();
// connect to database

// check that this group meeting belongs to the current owner
$meeting_stmt = $db->prepare("
    SELECT id, title, description, status
    FROM group_meetings
    WHERE id = ? AND owner_id = ?
");

$meeting_stmt->execute([
    (int)$group_meeting_id,
    $owner_id
]);
// run query

$meeting = $meeting_stmt->fetch();
// get query result

if (!$meeting) {
    send_json([
        "success" => false,
        "message" => "Access denied or group meeting not found."
    ], 404);
}
// stop if this meeting does not belong to the logged-in owner

// get each option and count how many users selected it
$count_stmt = $db->prepare("
    SELECT
        go.id,
        date(go.start_time) AS option_date,
        substr(time(go.start_time), 1, 5) AS start_time,
        substr(time(go.end_time), 1, 5) AS end_time,
        COUNT(gv.id) AS vote_count
    FROM group_options go
    LEFT JOIN group_votes gv ON go.id = gv.option_id
    WHERE go.group_id = ?
    GROUP BY go.id, go.start_time, go.end_time
    ORDER BY go.start_time
");

$count_stmt->execute([(int)$group_meeting_id]);
// run query

$options = $count_stmt->fetchAll();
// get query result

send_json([
    "success" => true,
    "meeting" => $meeting,
    "options" => $options
]);
// send vote counts back to React
?>