<?php
require_once "bootstrap.php";
require_once "auth.php";

require_login();
// only logged-in users can access this page

$owners = [];
// array to store owners with available slots

$db = get_db();
// connect to database

$stmt = $db->prepare("
    SELECT DISTINCT
        u.id,
        u.name,
        u.email
    FROM users u
    JOIN slots s ON s.owner_id = u.id
    LEFT JOIN bookings b ON b.slot_id = s.id
    WHERE u.role = 'owner'
      AND s.is_active = 1
      AND b.id IS NULL
    ORDER BY u.name
");
// get owners who have at least one active unbooked slot

$stmt->execute();
// run query

$owners = $stmt->fetchAll();
// get query result

send_json([
    "success" => true,
    "owners" => $owners
]);
// send owners back to React
?>