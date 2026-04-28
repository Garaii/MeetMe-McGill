<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

// STILL NEED TO UPDATE THIS: WHEN ACCEPTED A BOOKING SLOT SHOULD BE CREATED, USER SHOULD SEE THE APOINTMENT, OWNER SHOULD ALSO SEE IT
require_owner();
// only logged-in owners can access this page

$owner_id = current_user_id();
// get current logged-in owner's ID

$request_id = $_GET["request_id"] ?? null;
// get request ID from URL

if ($request_id === null) {
    die("Invalid request.");
}
// stop if no request ID was provided

// check that this request belongs to the current owner and is still pending
$stmt = $conn->prepare("
    SELECT id
    FROM meeting_requests
    WHERE id = ? AND owner_id = ? AND status = 'pending'
");
$stmt->bind_param("ii", $request_id, $owner_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Access denied or request already processed.");
}
// stop if request does not belong to this owner or is not pending

$stmt->close();

// update request status to accepted
$update = $conn->prepare("
    UPDATE meeting_requests
    SET status = 'accepted'
    WHERE id = ?
");
$update->bind_param("i", $request_id);

if ($update->execute()) {
    redirect("owner_requests.php");
} else {
    die("Failed to accept request.");
}

$update->close();
?>