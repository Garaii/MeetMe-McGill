<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_owner();
// only logged-in owners can access this page

$owner_id = current_user_id();
// get current logged-in owner's ID

$requests = [];
// array to store meeting requests

$stmt = $conn->prepare("
    SELECT meeting_requests.id,
           meeting_requests.message,
           meeting_requests.status,
           meeting_requests.created_at,
           users.name AS requester_name,
           users.email AS requester_email
    FROM meeting_requests
    INNER JOIN users ON meeting_requests.requester_id = users.id
    WHERE meeting_requests.owner_id = ?
    ORDER BY meeting_requests.created_at DESC
");
// get all requests sent to this owner, newest first

$stmt->bind_param("i", $owner_id);
// bind owner ID to query

$stmt->execute();
// run query

$result = $stmt->get_result();
// get query result

while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
    // store each request in array
}

$stmt->close();
// close statement
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Meeting Requests - MeetMe@McGill</title>
</head>
<body>
    <h1>Meeting Requests</h1>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
    <p><a href="logout.php">Logout</a></p>

    <?php if (count($requests) === 0): ?>
        <p>You have no meeting requests.</p>
    <?php else: ?>
        <table border="1" cellpadding="8">
            <tr>
                <th>Requester</th>
                <th>Email</th>
                <th>Message</th>
                <th>Status</th>
                <th>Requested At</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?php echo htmlspecialchars($request["requester_name"]); ?></td>
                    <td><?php echo htmlspecialchars($request["requester_email"]); ?></td>
                    <td><?php echo htmlspecialchars($request["message"]); ?></td>
                    <td><?php echo htmlspecialchars($request["status"]); ?></td>
                    <td><?php echo htmlspecialchars($request["created_at"]); ?></td>
                    <td>
                        <?php if ($request["status"] === "pending"): ?>
                            <a href="accept_request.php?request_id=<?php echo $request['id']; ?>">Accept</a>
                            |
                            <a href="decline_request.php?request_id=<?php echo $request['id']; ?>">Decline</a>
                        <?php else: ?>
                            No actions available
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>