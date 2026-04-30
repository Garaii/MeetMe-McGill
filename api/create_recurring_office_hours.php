<?php
/* CONTRIBUTORS AND TASK COORDINATION FOR PHP FILES
MAZEN & SARAH

MAZEN: ADAPTED FILES TO WORK WITH SQLITE, SETUP DATABASE CONNECTION AND ENDPOINTS + UPDATED QUERIES
SARAH: BUILT FILE STRUCTURE WITH SQL QUERIES AND LOCAL TESTING, SETUP AUTHENTICATION AND USER SESSION TYPES
BOTH: CREATED ERROR HANDLING, FIXED EDGE CASES, AND TESTED ENDPOINTS WITH FRONTEND
*/

require_once "bootstrap.php";
require_once "auth.php";

require_owner();
// only owners can access this page

$owner_id = current_user_id();
// get current logged-in owner's ID

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // get recurring batches for this owner

    $db = get_db();
    // connect to database

    $stmt = $db->prepare("
        SELECT
            rb.id,
            rb.title,
            rb.weeks,
            rb.location,
            rb.is_active,
            rb.created_at,
            COUNT(s.id) AS slot_count,
            SUM(CASE WHEN b.id IS NOT NULL THEN 1 ELSE 0 END) AS booked_count
        FROM recurring_batches rb
        LEFT JOIN slots s ON s.recurring_batch_id = rb.id
        LEFT JOIN bookings b ON b.slot_id = s.id
        WHERE rb.owner_id = ?
        GROUP BY rb.id
        ORDER BY rb.created_at DESC
    ");

    $stmt->execute([$owner_id]);
    // run query

    $batches = $stmt->fetchAll();
    // get query result

    send_json([
        "success" => true,
        "batches" => $batches
    ]);
    // send batches back to React
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run when form is submitted

    $data = read_json();
    // read JSON data sent by React

    $action = $data["action"] ?? "create";
    // get action from form

    $db = get_db();
    // connect to database

    if ($action === "toggle_batch") {
        $batch_id = $data["batch_id"] ?? "";
        $is_active = $data["is_active"] ?? "";

        if ($batch_id === "" || $is_active === "") {
            $error = "Invalid request.";

            send_json([
                "success" => false,
                "message" => $error
            ], 400);
        }

        // update only unbooked slots in this batch
        $stmt = $db->prepare("
            UPDATE slots
            SET is_active = ?
            WHERE recurring_batch_id = ?
              AND owner_id = ?
              AND id NOT IN (SELECT slot_id FROM bookings)
        ");

        $stmt->execute([(int)$is_active, (int)$batch_id, $owner_id]);
        // run update query

        $batch_stmt = $db->prepare("
            UPDATE recurring_batches
            SET is_active = ?
            WHERE id = ? AND owner_id = ?
        ");

        $batch_stmt->execute([(int)$is_active, (int)$batch_id, $owner_id]);
        // update batch row

        $success = "Recurring batch visibility updated.";

        send_json([
            "success" => true,
            "message" => $success
        ]);
    }

    if ($action === "delete_batch") {
        $batch_id = $data["batch_id"] ?? "";

        if ($batch_id === "") {
            $error = "Invalid request.";

            send_json([
                "success" => false,
                "message" => $error
            ], 400);
        }

        // check if any slot in this batch is booked
        $check = $db->prepare("
            SELECT b.id
            FROM bookings b
            JOIN slots s ON b.slot_id = s.id
            WHERE s.recurring_batch_id = ? AND s.owner_id = ?
            LIMIT 1
        ");

        $check->execute([(int)$batch_id, $owner_id]);
        $booking = $check->fetch();

        if ($booking) {
            $error = "Cannot delete a recurring batch with booked slots.";

            send_json([
                "success" => false,
                "message" => $error
            ], 400);
        }

        // delete unbooked slots in this batch
        $delete_slots = $db->prepare("
            DELETE FROM slots
            WHERE recurring_batch_id = ? AND owner_id = ?
        ");

        $delete_slots->execute([(int)$batch_id, $owner_id]);
        // run delete query

        // delete the batch row
        $delete_batch = $db->prepare("
            DELETE FROM recurring_batches
            WHERE id = ? AND owner_id = ?
        ");

        $delete_batch->execute([(int)$batch_id, $owner_id]);
        // run delete query

        $success = "Recurring batch deleted.";

        send_json([
            "success" => true,
            "message" => $success
        ]);
    }

    $weekday = $data["weekday"] ?? "";
    // get selected weekday from form

    $start_time = $data["start_time"] ?? "";
    // get start time from form

    $end_time = $data["end_time"] ?? "";
    // get end time from form

    $weeks = $data["weeks"] ?? "";
    // get number of weeks from form

    $location = trim($data["location"] ?? "");
    // get location from form

    if ($weekday === "" || $start_time === "" || $end_time === "" || $weeks === "") {
        $error = "All fields are required.";
        // check if any field is empty

        send_json([
            "success" => false,
            "message" => $error
        ], 400);

    } elseif ($end_time <= $start_time) {
        $error = "End time must be later than start time.";
        // check that end time is after start time

        send_json([
            "success" => false,
            "message" => $error
        ], 400);

    } elseif (!is_numeric($weeks) || $weeks <= 0 || $weeks > 52) {
        $error = "Number of weeks must be between 1 and 52.";
        // validate number of weeks

        send_json([
            "success" => false,
            "message" => $error
        ], 400);

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
        // map weekday names to weekday numbers

        if (!isset($weekday_map[$weekday])) {
            $error = "Invalid weekday selected.";

            send_json([
                "success" => false,
                "message" => $error
            ], 400);

        } else {
            try {
                $db->beginTransaction();
                // start transaction so batch and slots are created together

                $batch_title = "Recurring office hours";

                $batch_stmt = $db->prepare("
                    INSERT INTO recurring_batches (owner_id, title, weeks, location, is_active)
                    VALUES (?, ?, ?, ?, 0)
                ");

                $batch_stmt->execute([$owner_id, $batch_title, $weeks, $location]);
                // create recurring batch

                $batch_id = $db->lastInsertId();
                // get batch ID

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

                $stmt = $db->prepare("
                    INSERT INTO slots
                        (owner_id, title, location, start_time, end_time, is_active, slot_type, recurring_batch_id)
                    VALUES
                        (?, ?, ?, ?, ?, 0, 'recurring', ?)
                ");
                // prepare insert query
                // is_active = 0 means private by default

                $created_count = 0;
                $title = "Recurring office hours";

                for ($i = 0; $i < $weeks; $i++) {
                    $slot_date = clone $first_date;
                    $slot_date->modify("+$i week");
                    // generate each recurring weekly date

                    $formatted_date = $slot_date->format("Y-m-d");
                    // format date for SQLite

                    $start_datetime = $formatted_date . " " . $start_time . ":00";
                    $end_datetime = $formatted_date . " " . $end_time . ":00";
                    // combine date and time for SQLite

                    $stmt->execute([
                        $owner_id,
                        $title,
                        $location,
                        $start_datetime,
                        $end_datetime,
                        (int)$batch_id
                    ]);
                    // insert slot into database

                    $created_count++;
                }

                $db->commit();
                // save all database changes

                $success = "$created_count recurring office hour slot(s) created successfully. They are currently private.";

                send_json([
                    "success" => true,
                    "message" => $success,
                    "batch_id" => (int)$batch_id
                ]);

            } catch (Exception $e) {
                $db->rollBack();
                // undo database changes if something failed

                $error = "Failed to create recurring office hours.";

                send_json([
                    "success" => false,
                    "message" => $error
                ], 500);
            }
        }
    }
}

send_json([
    "success" => false,
    "message" => "Invalid request method."
], 405);
?>
