<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_owner();
// only owners can access

$owner_id = current_user_id();

// get slot ID and new status from URL
$slot_id = $_GET["id"] ?? null;
$new_status = $_GET["active"] ?? null;

if ($slot_id === null || $new_status === null) {
    die("Invalid request.");
}

// make sure new_status is 0 or 1
if ($new_status != 0 && $new_status != 1) {
    die("Invalid status.");
}

// check that this slot belongs to the current owner
$stmt = $conn->prepare("SELECT id FROM slots WHERE id = ? AND owner_id = ?");
$stmt->bind_param("ii", $slot_id, $owner_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Access denied.");
}

$stmt->close();

// update slot visibility
$update = $conn->prepare("UPDATE slots SET is_active = ? WHERE id = ?");
$update->bind_param("ii", $new_status, $slot_id);

if ($update->execute()) {
    redirect("owner_slots.php");
} else {
    die("Failed to update slot.");
}

$update->close();
?>