<?php
require_once "bootstrap.php";
require_once "auth.php";

require_owner();
// only owners can access this page

$owner_id = current_user_id();
// get current logged-in owner's ID

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // only run when form is submitted

    $data = read_json();
    // read JSON data sent by React

    $weekday = $data["weekday"] ?? "";
    // get selected weekday from form

    $start_time = $data["start_time"] ?? "";
    // get start time from form

    $end_time = $data["end_time"] ?? "";
    // get end time from form

    $weeks = $data["weeks"] ?? "";
    // get number of weeks from form

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
        $error = "Number of weeks must be between 0 and 52.";
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
        // map weekday names to ISO weekday numbers

        if (!isset($weekday_map[$weekday])) {
            $error = "Invalid weekday selected.";

            send_json([
                "success" => false,
                "message" => $error
            ], 400);

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

            $db = get_db();
            // connect to database

            $stmt = $db->prepare("
                INSERT INTO slots (owner_id, title, start_time, end_time, is_active, slot_type)
                VALUES (?, ?, ?, ?, 0, 'recurring')
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

                if ($stmt->execute([
                    $owner_id,
                    $title,
                    $start_datetime,
                    $end_datetime
                ])) {
                    $created_count++;
                }
            }

            $success = "$created_count recurring office hour slot(s) created successfully. They are currently private.";

            send_json([
                "success" => true,
                "message" => $success
            ]);
        }
    }
}

send_json([
    "success" => false,
    "message" => "Invalid request method."
], 405);
?>
