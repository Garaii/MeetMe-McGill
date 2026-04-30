<!-- CONTRIBUTORS AND TASK COORDINATION FOR PHP FILES
MAZEN & SARAH

MAZEN: ADAPTED FILES TO WORK WITH SQLITE, SETUP DATABASE CONNECTION AND ENDPOINTS + UPDATED QUERIES
SARAH: BUILT FILE STRUCTURE WITH SQL QUERIES AND LOCAL TESTING, SETUP AUTHENTICATION AND USER SESSION TYPES
BOTH: CREATED ERROR HANDLING, FIXED EDGE CASES, AND TESTED ENDPOINTS WITH FRONTEND
-->

<?php
require_once "bootstrap.php";
require_once "auth.php";

require_owner();
// only logged-in owners can access this page

$owner_id = current_user_id();
// get current logged-in owner's ID

$slots = [];
// array to store owner's slots

$db = get_db();
// connect to database

$stmt = $db->prepare("
    SELECT
        s.id,
        s.title,
        CASE
            WHEN s.slot_type = 'group' THEN 'group'
            ELSE 'manual'
        END AS slot_type,
        COALESCE(s.location, '') AS location,
        date(s.start_time) AS slot_date,
        substr(time(s.start_time), 1, 5) AS start_time,
        substr(time(s.end_time), 1, 5) AS end_time,
        s.is_active,
        u.name AS booked_by,
        u.email AS booked_by_email
    FROM slots s
    LEFT JOIN bookings b ON b.slot_id = s.id
    LEFT JOIN users u ON u.id = b.user_id
    WHERE s.owner_id = ?
    ORDER BY s.start_time
");
// get all slots created by this owner, ordered by date and time

$stmt->execute([$owner_id]);
// run query

$slots = $stmt->fetchAll();
// get query result

send_json([
    "success" => true,
    "slots" => $slots
]);
// send slots back to React
?>
