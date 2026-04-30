<!-- CONTRIBUTORS AND TASK COORDINATION FOR PHP FILES
MAZEN & SARAH

MAZEN: ADAPTED FILES TO WORK WITH SQLITE, SETUP DATABASE CONNECTION AND ENDPOINTS + UPDATED QUERIES
SARAH: BUILT FILE STRUCTURE WITH SQL QUERIES AND LOCAL TESTING, SETUP AUTHENTICATION AND USER SESSION TYPES
BOTH: CREATED ERROR HANDLING, FIXED EDGE CASES, AND TESTED ENDPOINTS WITH FRONTEND
-->

<?php
require_once "bootstrap.php";
require_once "auth.php";

require_login();
// only logged-in users can access this page

$user_id = current_user_id();
// get current logged-in user's ID

$user_bookings = [];
// array to store current user's bookings

$db = get_db();
// connect to database

$stmt = $db->prepare("
    SELECT
        b.id AS booking_id,
        s.title,
        CASE
            WHEN s.slot_type = 'group' THEN 'group'
            ELSE 'manual'
        END AS slot_type,
        date(s.start_time) AS slot_date,
        substr(time(s.start_time), 1, 5) AS start_time,
        substr(time(s.end_time), 1, 5) AS end_time,
        COALESCE(s.location, '') AS location,
        owner.name AS owner_name,
        owner.email AS owner_email,
        1 AS can_cancel
    FROM bookings b
    JOIN slots s ON b.slot_id = s.id
    JOIN users owner ON s.owner_id = owner.id
    WHERE b.user_id = ?
    ORDER BY s.start_time
");
// get all normal bookings made by this user

$stmt->execute([$user_id]);
// run query

$user_bookings = $stmt->fetchAll();
// get query result

$group_stmt = $db->prepare("
    SELECT
        'group-' || ga.id AS booking_id,
        s.title,
        'group' AS slot_type,
        date(s.start_time) AS slot_date,
        substr(time(s.start_time), 1, 5) AS start_time,
        substr(time(s.end_time), 1, 5) AS end_time,
        COALESCE(s.location, '') AS location,
        owner.name AS owner_name,
        owner.email AS owner_email,
        0 AS can_cancel
    FROM group_attendees ga
    JOIN slots s ON ga.slot_id = s.id
    JOIN users owner ON s.owner_id = owner.id
    WHERE ga.user_id = ?
    ORDER BY s.start_time
");
// get finalized group meetings where this user is an attendee

$group_stmt->execute([$user_id]);
// run query

$group_bookings = $group_stmt->fetchAll();
// get query result

$user_bookings = array_merge($user_bookings, $group_bookings);
// combine normal bookings and group appointments

send_json([
    "success" => true,
    "user_bookings" => $user_bookings
]);
// send bookings back to React
?>
