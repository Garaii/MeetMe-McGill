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
            WHEN s.title IS NOT NULL s.title !='' THEN 'group'
            ELSE 'manual'
        END AS slot_type
        date(s.start_time) AS slot_date,
        substr(time(s.start_time), 1, 5) AS start_time,
        substr(time(s.end_time), 1, 5) AS end_time,
        owner.name AS owner_name,
        owner.email AS owner_email
    FROM bookings b
    JOIN slots s ON b.slot_id = s.id
    JOIN users owner ON s.owner_id = owner.id
    WHERE b.user_id = ?
    ORDER BY s.start_time
");
// get all bookings made by this user

$stmt->execute([$user_id]);
// run query

$user_bookings = $stmt->fetchAll();
// get query result

send_json([
    "success" => true,
    "user_bookings" => $user_bookings
]);
// send bookings back to React
?>