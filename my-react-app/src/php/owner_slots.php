<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_owner();
// only logged-in owners can access this page

$owner_id = current_user_id();
// get current logged-in owner's ID

$name = current_user_name();
// get current owner's name

$email = current_user_email();
// get current owner's email

$slots = [];
// array to store owner's slots

$stmt = $conn->prepare("SELECT id, slot_date, start_time, end_time, is_active FROM slots WHERE owner_id = ? ORDER BY slot_date, start_time");
 // get all slots created by this owner, ordered by date and time

$stmt->bind_param("i", $owner_id);
// bind owner ID to the query

$stmt->execute();
// run query

$result = $stmt->get_result();
// get query result

while ($row = $result->fetch_assoc()) {
    $slots[] = $row;
    // store each slot row inside the slots array
}

$stmt->close();
// close statement
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Slots - MeetMe@McGill</title>
</head>
<body>
    <h1>My Booking Slots</h1>

    <p><strong>Owner:</strong> <?php echo htmlspecialchars($name); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>

    <p><a href="create_slot.php">Create New Slot</a></p>
    <p><a href="dashboard.php">Back to Dashboard</a></p>
    <p><a href="logout.php">Logout</a></p>

    <?php if (count($slots) === 0): ?>
        <p>You have not created any booking slots yet.</p>
    <?php else: ?>
        <table border="1" cellpadding="8">
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Status</th>
            </tr>

            <?php foreach ($slots as $slot): ?>
                <tr>
                    <td><?php echo htmlspecialchars($slot["id"]); ?></td>
                    <td><?php echo htmlspecialchars($slot["slot_date"]); ?></td>
                    <td><?php echo htmlspecialchars($slot["start_time"]); ?></td>
                    <td><?php echo htmlspecialchars($slot["end_time"]); ?></td>
                    <td>
                        <?php
                        if ($slot["is_active"]) {
                            echo "Active";
                        } else {
                            echo "Private";
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>