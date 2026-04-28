<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_login();
// only logged-in users can access this page

$owner_id = $_GET["owner_id"] ?? null;
// get owner ID from URL

if ($owner_id === null) {
    die("Invalid request.");
}
// stop if no owner_id was provided

// get owner information
$owner_stmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = ? AND role = 'owner'");
$owner_stmt->bind_param("i", $owner_id);
$owner_stmt->execute();
$owner_result = $owner_stmt->get_result();

if ($owner_result->num_rows !== 1) {
    die("Owner not found.");
}
// stop if owner does not exist

$owner = $owner_result->fetch_assoc();
// store owner info

$owner_stmt->close();

// get owner's active and available slots
$slots = [];

$slot_stmt = $conn->prepare("
    SELECT id, slot_date, start_time, end_time
    FROM slots
    WHERE owner_id = ? AND is_active = 1 AND id NOT IN (
        SELECT slot_id FROM bookings
    )
    ORDER BY slot_date, start_time
");
// get active slots that are not already booked

$slot_stmt->bind_param("i", $owner_id);
$slot_stmt->execute();
$slot_result = $slot_stmt->get_result();

while ($row = $slot_result->fetch_assoc()) {
    $slots[] = $row;
    // store each slot in array
}

$slot_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book a Slot - MeetMe@McGill</title>
</head>
<body>
    <h1>Available Slots</h1>

    <p><strong>Owner:</strong> <?php echo htmlspecialchars($owner["name"]); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($owner["email"]); ?></p>

    <p><a href="owners_list.php">Back to Owners List</a></p>
    <p><a href="dashboard.php">Back to Dashboard</a></p>
    <p><a href="logout.php">Logout</a></p>

    <?php if (count($slots) === 0): ?>
        <p>This owner has no available active slots right now.</p>
    <?php else: ?>
        <table border="1" cellpadding="8">
            <tr>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Action</th>
            </tr>

            <?php foreach ($slots as $slot): ?>
                <tr>
                    <td><?php echo htmlspecialchars($slot["slot_date"]); ?></td>
                    <td><?php echo htmlspecialchars($slot["start_time"]); ?></td>
                    <td><?php echo htmlspecialchars($slot["end_time"]); ?></td>
                    <td>
                        <a href="book_slot.php?slot_id=<?php echo $slot['id']; ?>">Book</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>