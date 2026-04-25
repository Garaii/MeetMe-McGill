<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_owner();
// only logged-in owners can access this page

$owner_id = current_user_id();
// get current logged-in owner's ID

$slot_id = $_GET["id"] ?? null;
// get slot ID from URL

if ($slot_id === null) {
    die("Invalid request.");
}
// stop if no slot ID was provided

// check that this slot belongs to the current owner
$stmt = $conn->prepare("SELECT id FROM slots WHERE id = ? AND owner_id = ?");
$stmt->bind_param("ii", $slot_id, $owner_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Access denied.");
}
// stop if the slot does not belong to this owner

$stmt->close();

// delete the slot
$delete = $conn->prepare("DELETE FROM slots WHERE id = ?");
$delete->bind_param("i", $slot_id);

if ($delete->execute()) {
    redirect("owner_slots.php");
} else {
    die("Failed to delete slot.");
}

$delete->close();
?>