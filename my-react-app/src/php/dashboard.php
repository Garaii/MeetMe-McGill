<?php
require_once "db.php";
require_once "auth.php";
require_once "helpers.php";

require_login();
// make sure user is logged in before accessing dashboard

$user_id = current_user_id();
// get current user's ID

$name = current_user_name();
// get current user's name from session

$email = current_user_email();
// get current user's email from session

$role = current_user_role();
// get current user's role from session

$owner_slots = [];
$user_bookings = [];

// If the logged-in person is an owner, load their slots
if ($role === "owner") {
    $stmt = $conn->prepare("
        SELECT slots.id, slots.slot_date, slots.start_time, slots.end_time, slots.is_active, users.name AS booked_by
        FROM slots
        LEFT JOIN bookings ON slots.id = bookings.slot_id
        LEFT JOIN users ON bookings.user_id = users.id
        WHERE slots.owner_id = ?
        ORDER BY slots.slot_date, slots.start_time
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $owner_slots[] = $row;
    }

    $stmt->close();
}

// If the logged-in person is a user, load their bookings
if ($role === "user") {
    $stmt = $conn->prepare("
        SELECT bookings.id AS booking_id, slots.slot_date, slots.start_time, slots.end_time, users.name AS owner_name, users.email AS owner_email
        FROM bookings
        INNER JOIN slots ON bookings.slot_id = slots.id
        INNER JOIN users ON slots.owner_id = users.id
        WHERE bookings.user_id = ?
        ORDER BY slots.slot_date, slots.start_time
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $user_bookings[] = $row;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - MeetMe@McGill</title>
</head>
<body>
    <h1>Welcome to MeetMe@McGill</h1>

    <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    <p><strong>Role:</strong> <?php echo htmlspecialchars($role); ?></p>

    <?php if ($role === "owner"): ?>
        <h2>Owner Dashboard</h2>

        <ul>
            <li><a href="create_slot.php">Create a booking slot</a></li>
            <li><a href="owner_slots.php">View my slots</a></li>
            <li><a href="owner_requests.php">View meeting requests</a></li>
        </ul>

        <h3>My Slots</h3>

        <?php if (count($owner_slots) === 0): ?>
            <p>You have not created any slots yet.</p>
        <?php else: ?>
            <table border="1" cellpadding="8">
                <tr>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Status</th>
                    <th>Booked By</th>
                </tr>

                <?php foreach ($owner_slots as $slot): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($slot["slot_date"]); ?></td>
                        <td><?php echo htmlspecialchars($slot["start_time"]); ?></td>
                        <td><?php echo htmlspecialchars($slot["end_time"]); ?></td>
                        <td><?php echo $slot["is_active"] ? "Active" : "Private"; ?></td>
                        <td><?php echo htmlspecialchars($slot["booked_by"] ?? "Not booked"); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

    <?php else: ?>
        <h2>User Dashboard</h2>

        <ul>
            <li><a href="owners_list.php">Browse available owners</a></li>
            <li><a href="request_meeting.php">Request a meeting</a></li>
        </ul>

        <h3>My Booked Appointments</h3>

        <?php if (count($user_bookings) === 0): ?>
            <p>You have no booked appointments.</p>
        <?php else: ?>
            <table border="1" cellpadding="8">
                <tr>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Owner</th>
                    <th>Email Owner</th>
                    <th>Action</th>
                </tr>

                <?php foreach ($user_bookings as $booking): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking["slot_date"]); ?></td>
                        <td><?php echo htmlspecialchars($booking["start_time"]); ?></td>
                        <td><?php echo htmlspecialchars($booking["end_time"]); ?></td>
                        <td><?php echo htmlspecialchars($booking["owner_name"]); ?></td>
                        <td>
                            <a href="mailto:<?php echo htmlspecialchars($booking["owner_email"]); ?>">
                                Email Owner
                            </a>
                        </td>
                        <td>
                            <a href="cancel_booking.php?booking_id=<?php echo $booking['booking_id']; ?>">
                                Cancel Booking
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    <?php endif; ?>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>