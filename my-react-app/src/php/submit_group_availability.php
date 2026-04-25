<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_login();
// only logged-in users can access this page

$user_id = current_user_id();
// get current logged-in user's ID

$group_meeting_id = $_GET["group_meeting_id"] ?? ($_POST["group_meeting_id"] ?? null);
// get group meeting ID from URL or POST

if ($group_meeting_id === null) {
    die("Invalid request.");
}
// stop if no group meeting ID was provided

// get group meeting info
$meeting_stmt = $conn->prepare("
    SELECT group_meetings.id, group_meetings.title, group_meetings.description, users.name AS owner_name
    FROM group_meetings
    INNER JOIN users ON group_meetings.owner_id = users.id
    WHERE group_meetings.id = ?
");
$meeting_stmt->bind_param("i", $group_meeting_id);
$meeting_stmt->execute();
$meeting_result = $meeting_stmt->get_result();

if ($meeting_result->num_rows !== 1) {
    die("Group meeting not found.");
}
// stop if meeting does not exist

$meeting = $meeting_result->fetch_assoc();
// store meeting info

$meeting_stmt->close();

$options = [];
// array to store all meeting options

$option_stmt = $conn->prepare("
    SELECT id, option_date, start_time, end_time
    FROM group_meeting_options
    WHERE group_meeting_id = ?
    ORDER BY option_date, start_time
");
$option_stmt->bind_param("i", $group_meeting_id);
$option_stmt->execute();
$option_result = $option_stmt->get_result();

while ($row = $option_result->fetch_assoc()) {
    $options[] = $row;
}
// store each option

$option_stmt->close();

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run when form is submitted

    $selected_options = $_POST["selected_options"] ?? [];
    // get selected meeting option IDs as an array

    if (count($selected_options) === 0) {
        $error = "Please select at least one option.";
    } else {
        // remove previous votes by this user for this meeting
        $delete_stmt = $conn->prepare("
            DELETE group_meeting_votes
            FROM group_meeting_votes
            INNER JOIN group_meeting_options ON group_meeting_votes.option_id = group_meeting_options.id
            WHERE group_meeting_votes.user_id = ? AND group_meeting_options.group_meeting_id = ?
        ");
        $delete_stmt->bind_param("ii", $user_id, $group_meeting_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        // insert new votes
        $vote_stmt = $conn->prepare("
            INSERT INTO group_meeting_votes (option_id, user_id)
            VALUES (?, ?)
        ");

        $inserted_count = 0;

        foreach ($selected_options as $option_id) {
            $option_id = (int)$option_id;
            $vote_stmt->bind_param("ii", $option_id, $user_id);

            if ($vote_stmt->execute()) {
                $inserted_count++;
            }
        }

        $vote_stmt->close();

        $success = "Your availability was submitted successfully for $inserted_count option(s).";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Availability - MeetMe@McGill</title>
</head>
<body>
    <h1>Submit Availability</h1>

    <p><strong>Meeting:</strong> <?php echo htmlspecialchars($meeting["title"]); ?></p>
    <p><strong>Owner:</strong> <?php echo htmlspecialchars($meeting["owner_name"]); ?></p>

    <?php if ($meeting["description"] !== ""): ?>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($meeting["description"]); ?></p>
    <?php endif; ?>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
    <p><a href="logout.php">Logout</a></p>

    <?php if ($error !== ""): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <?php if (count($options) === 0): ?>
        <p>No meeting options are available for this group meeting.</p>
    <?php else: ?>
        <form action="submit_group_availability.php" method="POST">
            <input type="hidden" name="group_meeting_id" value="<?php echo htmlspecialchars($group_meeting_id); ?>">

            <table border="1" cellpadding="8">
                <tr>
                    <th>Select</th>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                </tr>

                <?php foreach ($options as $option): ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="selected_options[]" value="<?php echo $option['id']; ?>">
                        </td>
                        <td><?php echo htmlspecialchars($option["option_date"]); ?></td>
                        <td><?php echo htmlspecialchars($option["start_time"]); ?></td>
                        <td><?php echo htmlspecialchars($option["end_time"]); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <br>

            <button type="submit">Submit Availability</button>
        </form>
    <?php endif; ?>
</body>
</html>