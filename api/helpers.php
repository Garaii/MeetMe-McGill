<?php

// send data back to React as JSON
function send_json($data, $status_code = 200) {
    http_response_code($status_code);
    header("Content-Type: application/json");
    echo json_encode($data);
    exit();
}

// read JSON data sent by React
function read_json() {
    $raw_data = file_get_contents("php://input");
    // React sends the data in the request body

    if ($raw_data === false || trim($raw_data) === "") {
        return [];
    }

    $data = json_decode($raw_data, true);
    // convert JSON string into PHP array

    if (!is_array($data)) {
        send_json([
            "success" => false,
            "message" => "Invalid JSON data."
        ], 400);
    }

    return $data;
}

// check if an email is a valid mcgill email
function is_mcgill_email($email) {
    $email = strtolower(trim((string)$email));
    // lowercase = consistent comparison

    // Check if email ends with @mcgill.ca or @mail.mcgill.ca
    return preg_match('/@mcgill\.ca$/', $email) || preg_match('/@mail\.mcgill\.ca$/', $email);
}

// determine if person is owner or user based on their email
function get_role_from_email($email) {
    $email = strtolower(trim((string)$email));

    if (preg_match('/@mcgill\.ca$/', $email)) {
        return "owner";
        // prof or staff
    }

    if (preg_match('/@mail\.mcgill\.ca$/', $email)) {
        return "user";
        // student
    }

    return null;
    // if it's not a valid McGill domain
}

// check if email belongs to an owner
function is_owner($email) {
    return preg_match('/@mcgill\.ca$/', strtolower(trim((string)$email)));
}

// check if email belongs to a student user
function is_user($email) {
    return preg_match('/@mail\.mcgill\.ca$/', strtolower(trim((string)$email)));
}

// clean user input
function clean_input($value) {
    return trim((string)$value);
}

// clean user input before showing it in HTML
function clean_output($value) {
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, "UTF-8");
}

?>