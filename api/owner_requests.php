<?php
require_once "bootstrap.php";
require_once "auth.php";

require_owner();
// only owners can view meeting requests sent to them

$owner_id = current_user_id();
// get logged-in owner's ID from session

$db = get_db();
// connect to database

// get pending meeting requests for this owner
$stmt = $db->prepare("
    SELECT
        mr.id,
        mr.title,
        mr.message,
        mr.status,
        mr.created_at,
        date(mr.requested_start) AS requested_date,
        substr(time(mr.requested_start), 1, 5) AS requested_start,
        substr(time(mr.requested_end), 1, 5) AS requested_end,
        u.name AS requester_name,
        u.email AS requester_email
    FROM meeting_requests mr
    JOIN users u ON mr.requester_id = u.id
    WHERE mr.owner_id = ? AND mr.status = 'pending'
    ORDER BY mr.created_at DESC
");

$stmt->execute([$owner_id]);
// run query

$requests = $stmt->fetchAll();
// get query result

send_json([
    "success" => true,
    "requests" => $requests
]);
// send requests back to React
?>