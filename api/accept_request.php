<?php
require_once "bootstrap.php";
require_once "auth.php";

// TODO: STILL NEED TO UPDATE THIS: WHEN ACCEPTED A BOOKING SLOT SHOULD BE CREATED, USER SHOULD SEE THE APOINTMENT, OWNER SHOULD ALSO SEE IT
require_owner();
// only owners can accept requests sent to them

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run if form was submitted

    $owner_id = current_user_id();
    // get logged-in owner's ID from session

    $data = read_json();
    // read JSON data sent by React

    $request_id = $data["request_id"] ?? "";
    // get request ID from form

    if ($request_id === "") {
        // check if request ID is missing
        $error = "Invalid request.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    $db = get_db();
    // connect to database

    // update request only if it belongs to this owner
    $stmt = $db->prepare("
        UPDATE meeting_requests
        SET status = 'accepted'
        WHERE id = ? AND owner_id = ?
    ");

    $stmt->execute([(int)$request_id, $owner_id]);
    // run update query

    if ($stmt->rowCount() === 1) {
        $success = "Meeting request accepted.";

        send_json([
            "success" => true,
            "message" => $success
        ]);
    } else {
        $error = "Request not found.";

        send_json([
            "success" => false,
            "message" => $error
        ], 404);
    }
}

send_json([
    "success" => false,
    "message" => "Invalid request method."
], 405);
?>