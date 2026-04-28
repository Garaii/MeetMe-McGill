<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_owner();
// only logged-in owners can access this page

$owner_id = current_user_id();
// get current logged-in owner's ID

$group_meeting_id = $_GET["group_meeting_id"] ?? null;
// get group meeting ID from URL

if ($group_meeting_id === null) {
    die("Invalid request.");
}
// stop if no group meeting ID was provided

// check that this group meeting belongs to the current owner
$meeting_stmt = $conn->prepare("
    SELECT id, title, description
    FROM group_meetings
    WHERE id = ? AND owner_id = ?
");
$meeting_stmt->bind_param("ii", $group_meeting_id, $owner_id);
$meeting_stmt->execute();
$meeting_result = $meeting_stmt->get_result();

if ($meeting_result->num_rows !== 1) {
    die("Access denied or group meeting not found.");
}
// stop if this meeting does not belong to the logged-in owner

$meeting = $meeting_result->fetch_assoc();
// store meeting info

$meeting_stmt->close();

$options = [];
// array to store options and their vote counts

$count_stmt = $conn->prepare("
    SELECT 
        group_meeting_options.id,
        group_meeting_options.option_date,
        group_meeting_options.start_time,
        group_meeting_options.end_time,
        COUNT(group_meeting_votes.id) AS vote_count
    FROM group_meeting_options
    LEFT JOIN group_meeting_votes 
        ON group_meeting_options.id = group_meeting_votes.option_id
    WHERE group_meeting_options.group_meeting_id = ?
    GROUP BY 
        group_meeting_options.id,
        group_meeting_options.option_date,
        group_meeting_options.start_time,
        group_meeting_options.end_time
    ORDER BY group_meeting_options.option_date, group_meeting_options.start_time
");
// get each option and count how many users selected it

$count_stmt->bind_param("i", $group_meeting_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();

while ($row = $count_result->fetch_assoc()) {
    $options[] = $row;
}
// store each option row

$count_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Group Meeting Counts - MeetMe@McGill</title>
</head>
<body>
    <h1>Group Meeting Availability Counts</h1>

    <p><strong>Meeting Title:</strong> <?php echo htmlspecialchars($meeting["title"]); ?></p>

    <?php if ($meeting["description"] !== ""): ?>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($meeting["description"]); ?></p>
    <?php endif; ?>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
    <p><a href="logout.php">Logout</a></p>

    <?php if (count($options) === 0): ?>
        <p>No meeting options found for this group meeting.</p>
    <?php else: ?>
        <table border="1" cellpadding="8">
            <tr>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Number of Selections</th>
                <th>Action</th>
            </tr>

            <?php foreach ($options as $option): ?>
                <tr>
                    <td><?php echo htmlspecialchars($option["option_date"]); ?></td>
                    <td><?php echo htmlspecialchars($option["start_time"]); ?></td>
                    <td><?php echo htmlspecialchars($option["end_time"]); ?></td>
                    <td><?php echo htmlspecialchars($option["vote_count"]); ?></td>
                    <td>
                        <a href="finalize_group_meeting.php?option_id=<?php echo $option['id']; ?>">
                            Finalize This Option
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>