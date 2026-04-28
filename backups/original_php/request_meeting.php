<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_login();
// only logged-in users can access this page

$user_id = current_user_id();
// get current logged-in user's ID

$role = current_user_role();
// get current user's role

if ($role !== "user") {
    die("Only users can request meetings.");
}
// only student users can send meeting requests

$error = "";
$success = "";
$owners = [];

// get all owners
$result = $conn->query("SELECT id, name, email FROM users WHERE role = 'owner' ORDER BY name");

while ($row = $result->fetch_assoc()) {
    $owners[] = $row;
}
// store owners in array for dropdown list

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run when form is submitted

    $owner_id = $_POST["owner_id"] ?? "";
    // get selected owner ID from form

    $message = trim($_POST["message"] ?? "");
    // get message from form and remove extra spaces

    if ($owner_id === "" || $message === "") {
        $error = "All fields are required.";
    } else {
        // make sure selected owner exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'owner'");
        $stmt->bind_param("i", $owner_id);
        $stmt->execute();
        $owner_result = $stmt->get_result();

        if ($owner_result->num_rows !== 1) {
            $error = "Invalid owner selected.";
        } else {
            $insert = $conn->prepare("
                INSERT INTO meeting_requests (requester_id, owner_id, message, status)
                VALUES (?, ?, ?, 'pending')
            ");
            $insert->bind_param("iis", $user_id, $owner_id, $message);

            if ($insert->execute()) {
                $success = "Meeting request sent successfully.";
            } else {
                $error = "Failed to send meeting request.";
            }

            $insert->close();
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request a Meeting - MeetMe@McGill</title>
</head>
<body>
    <h1>Request a Meeting</h1>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
    <p><a href="logout.php">Logout</a></p>

    <?php if ($error !== ""): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form action="request_meeting.php" method="POST">
        <label>Select Owner:</label><br>
        <select name="owner_id" required>
            <option value="">-- Choose an owner --</option>
            <?php foreach ($owners as $owner): ?>
                <option value="<?php echo $owner['id']; ?>">
                    <?php echo htmlspecialchars($owner['name']) . " (" . htmlspecialchars($owner['email']) . ")"; ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Message:</label><br>
        <textarea name="message" rows="5" cols="40" required></textarea><br><br>

        <button type="submit">Send Request</button>
    </form>
</body>
</html>