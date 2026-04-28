<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_owner();
// only logged-in owners can access this page

$owner_id = current_user_id();
// get current logged-in owner's ID

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run when form is submitted

    $title = trim($_POST["title"] ?? "");
    // get group meeting title

    $description = trim($_POST["description"] ?? "");
    // get optional description

    $dates = $_POST["option_date"] ?? [];
    // get all submitted dates as an array

    $start_times = $_POST["start_time"] ?? [];
    // get all submitted start times as an array

    $end_times = $_POST["end_time"] ?? [];
    // get all submitted end times as an array

    if ($title === "") {
        $error = "Meeting title is required.";
    } elseif (count($dates) === 0) {
        $error = "At least one meeting option is required.";
    } else {
        $valid_options = [];
        // store valid options after checking them

        for ($i = 0; $i < count($dates); $i++) {
            $option_date = trim($dates[$i] ?? "");
            $start_time = trim($start_times[$i] ?? "");
            $end_time = trim($end_times[$i] ?? "");

            // skip completely empty rows
            if ($option_date === "" && $start_time === "" && $end_time === "") {
                continue;
            }

            if ($option_date === "" || $start_time === "" || $end_time === "") {
                $error = "Each meeting option must have date, start time, and end time.";
                break;
            }

            if ($end_time <= $start_time) {
                $error = "Each meeting option must have an end time later than its start time.";
                break;
            }

            $valid_options[] = [
                "option_date" => $option_date,
                "start_time" => $start_time,
                "end_time" => $end_time
            ];
        }

        if ($error === "" && count($valid_options) === 0) {
            $error = "Please provide at least one valid meeting option.";
        }

        if ($error === "") {
            // insert group meeting first
            $meeting_stmt = $conn->prepare("
                INSERT INTO group_meetings (owner_id, title, description)
                VALUES (?, ?, ?)
            ");
            $meeting_stmt->bind_param("iss", $owner_id, $title, $description);

            if ($meeting_stmt->execute()) {
                $group_meeting_id = $meeting_stmt->insert_id;
                // get newly created group meeting ID

                $meeting_stmt->close();

                // insert each meeting option
                $option_stmt = $conn->prepare("
                    INSERT INTO group_meeting_options (group_meeting_id, option_date, start_time, end_time)
                    VALUES (?, ?, ?, ?)
                ");

                $created_count = 0;

                foreach ($valid_options as $option) {
                    $option_stmt->bind_param(
                        "isss",
                        $group_meeting_id,
                        $option["option_date"],
                        $option["start_time"],
                        $option["end_time"]
                    );

                    if ($option_stmt->execute()) {
                        $created_count++;
                    }
                }

                $option_stmt->close();

                $success = "Group meeting created successfully with $created_count option(s).";
            } else {
                $error = "Failed to create group meeting.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Group Meeting - MeetMe@McGill</title>
</head>
<body>
    <h1>Create Group Meeting</h1>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
    <p><a href="logout.php">Logout</a></p>

    <?php if ($error !== ""): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form action="create_group_meeting.php" method="POST">
        <label>Meeting Title:</label><br>
        <input type="text" name="title" required><br><br>

        <label>Description (optional):</label><br>
        <textarea name="description" rows="4" cols="50"></textarea><br><br>

        <h3>Meeting Options</h3>

        <div>
            <label>Date:</label>
            <input type="date" name="option_date[]">

            <label>Start Time:</label>
            <input type="time" name="start_time[]">

            <label>End Time:</label>
            <input type="time" name="end_time[]">
        </div>
        <br>

        <div>
            <label>Date:</label>
            <input type="date" name="option_date[]">

            <label>Start Time:</label>
            <input type="time" name="start_time[]">

            <label>End Time:</label>
            <input type="time" name="end_time[]">
        </div>
        <br>

        <div>
            <label>Date:</label>
            <input type="date" name="option_date[]">

            <label>Start Time:</label>
            <input type="time" name="start_time[]">

            <label>End Time:</label>
            <input type="time" name="end_time[]">
        </div>
        <br>

        <div>
            <label>Date:</label>
            <input type="date" name="option_date[]">

            <label>Start Time:</label>
            <input type="time" name="start_time[]">

            <label>End Time:</label>
            <input type="time" name="end_time[]">
        </div>
        <br>

        <button type="submit">Create Group Meeting</button>
    </form>
</body>
</html>