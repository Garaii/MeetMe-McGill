<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_owner();
// only logged-in owners can access this page

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run if form was submitted

    $owner_id = current_user_id();
    // get logged-in owner's ID from session

    $slot_date = $_POST["slot_date"] ?? "";
    // get date from form

    $start_time = $_POST["start_time"] ?? "";
    // get start time from form

    $end_time = $_POST["end_time"] ?? "";
    // get end time from form

    if ($slot_date === "" || $start_time === "" || $end_time === "") {
        // check if any field is empty
        $error = "All fields are required.";

    } elseif ($end_time <= $start_time) {
        // basic check to make sure end time is after start time
        $error = "End time must be later than start time.";

    } else {
        // insert new slot into database
        $stmt = $conn->prepare("INSERT INTO slots (owner_id, slot_date, start_time, end_time, is_active) VALUES (?, ?, ?, ?, 0)");
        // is_active = 0 means private/inactive by default

        $stmt->bind_param("isss", $owner_id, $slot_date, $start_time, $end_time);
        // i = integer, s = string, s = string, s = string

        if ($stmt->execute()) {
            $success = "Booking slot created successfully. It is currently private.";
        } else {
            $error = "Failed to create slot.";
        }

        $stmt->close();
    }
}

$name = current_user_name();
$email = current_user_email();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Slot - MeetMe@McGill</title>
</head>
<body>
    <h1>Create Booking Slot</h1>

    <p><strong>Owner:</strong> <?php echo htmlspecialchars($name); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>

    <?php if ($error !== ""): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form action="create_slot.php" method="POST">
        <label>Date:</label><br>
        <input type="date" name="slot_date" required><br><br>

        <label>Start Time:</label><br>
        <input type="time" name="start_time" required><br><br>

        <label>End Time:</label><br>
        <input type="time" name="end_time" required><br><br>

        <button type="submit">Create Slot</button>
    </form>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>