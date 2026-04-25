<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_owner();
// only owners can access this page

$owner_id = current_user_id();
// get current logged-in owner's ID

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run when form is submitted

    $weekday = $_POST["weekday"] ?? "";
    // get selected weekday from form

    $start_time = $_POST["start_time"] ?? "";
    // get start time from form

    $end_time = $_POST["end_time"] ?? "";
    // get end time from form

    $weeks = $_POST["weeks"] ?? "";
    // get number of weeks from form

    if ($weekday === "" || $start_time === "" || $end_time === "" || $weeks === "") {
        $error = "All fields are required.";
        // check if any field is empty

    } elseif ($end_time <= $start_time) {
        $error = "End time must be later than start time.";
        // check that end time is after start time

    } elseif (!is_numeric($weeks) || $weeks <= 0) {
        $error = "Number of weeks must be greater than 0.";
        // validate number of weeks

    } else {
        $weeks = (int)$weeks;
        // convert weeks to integer

        $weekday_map = [
            "Monday" => 1,
            "Tuesday" => 2,
            "Wednesday" => 3,
            "Thursday" => 4,
            "Friday" => 5,
            "Saturday" => 6,
            "Sunday" => 7
        ];
        // map weekday names to ISO weekday numbers

        if (!isset($weekday_map[$weekday])) {
            $error = "Invalid weekday selected.";
        } else {
            $target_day = $weekday_map[$weekday];
            // get numeric day for chosen weekday

            $today = new DateTime();
            // get today's date

            $current_day = (int)$today->format("N");
            // get today's weekday number (1 = Monday, 7 = Sunday)

            $days_until_target = $target_day - $current_day;
            // calculate distance from today to chosen weekday

            if ($days_until_target < 0) {
                $days_until_target += 7;
            }
            // if chosen weekday already passed this week, move to next week

            $first_date = clone $today;
            $first_date->modify("+$days_until_target days");
            // get first occurrence of selected weekday

            $stmt = $conn->prepare("
                INSERT INTO slots (owner_id, slot_date, start_time, end_time, is_active)
                VALUES (?, ?, ?, ?, 0)
            ");
            // prepare insert query
            // is_active = 0 means private by default

            $created_count = 0;

            for ($i = 0; $i < $weeks; $i++) {
                $slot_date = clone $first_date;
                $slot_date->modify("+$i week");
                // generate each recurring weekly date

                $formatted_date = $slot_date->format("Y-m-d");
                // format date for MySQL

                $stmt->bind_param("isss", $owner_id, $formatted_date, $start_time, $end_time);
                // bind owner ID, date, start time, end time

                if ($stmt->execute()) {
                    $created_count++;
                }
            }

            $stmt->close();

            $success = "$created_count recurring office hour slot(s) created successfully. They are currently private.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Recurring Office Hours - MeetMe@McGill</title>
</head>
<body>
    <h1>Create Recurring Office Hours</h1>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
    <p><a href="owner_slots.php">View My Slots</a></p>
    <p><a href="logout.php">Logout</a></p>

    <?php if ($error !== ""): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form action="create_recurring_office_hours.php" method="POST">
        <label>Weekday:</label><br>
        <select name="weekday" required>
            <option value="">-- Select a weekday --</option>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
            <option value="Saturday">Saturday</option>
            <option value="Sunday">Sunday</option>
        </select><br><br>

        <label>Start Time:</label><br>
        <input type="time" name="start_time" required><br><br>

        <label>End Time:</label><br>
        <input type="time" name="end_time" required><br><br>

        <label>Number of Weeks:</label><br>
        <input type="number" name="weeks" min="1" required><br><br>

        <button type="submit">Create Recurring Office Hours</button>
    </form>
</body>
</html>