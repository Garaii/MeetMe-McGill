<?php
require_once "db.php";
require_once "helpers.php";
require_once "auth.php";

require_login();
// only logged-in users can access this page

$owners = [];
// array to store owners with active slots

$sql = "
    SELECT DISTINCT users.id, users.name, users.email
    FROM users
    INNER JOIN slots ON users.id = slots.owner_id
    WHERE users.role = 'owner' AND slots.is_active = 1
    ORDER BY users.name
";
// get all owners who have at least one active slot

$result = $conn->query($sql);
// run query

while ($row = $result->fetch_assoc()) {
    $owners[] = $row;
    // store each owner in the array
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Owners - MeetMe@McGill</title>
</head>
<body>
    <h1>Available Owners</h1>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
    <p><a href="logout.php">Logout</a></p>

    <?php if (count($owners) === 0): ?>
        <p>No owners currently have active booking slots.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($owners as $owner): ?>
                <li>
                    <strong><?php echo htmlspecialchars($owner["name"]); ?></strong>
                    (<?php echo htmlspecialchars($owner["email"]); ?>)
                    -
                    <a href="owner_booking_page.php?owner_id=<?php echo $owner['id']; ?>">
                        View available slots
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>