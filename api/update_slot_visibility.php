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
// only owners can access

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run if form was submitted

    $owner_id = current_user_id();
    // get logged-in owner's ID from session

    $data = read_json();
    // read JSON data sent by React

    $slot_id = $data["slot_id"] ?? "";
    // get slot ID from form

    $new_status = $data["is_active"] ?? "";
    // get new status from form

    if ($slot_id === "" || $new_status === "") {
        // check if any field is empty
        $error = "Invalid request.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    // make sure new_status is 0 or 1
    if ($new_status != 0 && $new_status != 1) {
        $error = "Invalid status.";

        send_json([
            "success" => false,
            "message" => $error
        ], 400);
    }

    $db = get_db();
    // connect to database

    // update slot visibility only if it belongs to this owner
    $stmt = $db->prepare("
        UPDATE slots
        SET is_active = ?
        WHERE id = ? AND owner_id = ?
    ");

    $stmt->execute([(int)$new_status, (int)$slot_id, $owner_id]);
    // run update query

    if ($stmt->rowCount() === 1) {
        $success = "Slot visibility updated.";

        send_json([
            "success" => true,
            "message" => $success
        ]);
    } else {
        $error = "Access denied or slot not found.";

        send_json([
            "success" => false,
            "message" => $error
        ], 403);
    }
}

send_json([
    "success" => false,
    "message" => "Invalid request method."
], 405);
?>