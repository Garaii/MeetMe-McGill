<?php

// check if an email is a valid mcgill email
function is_mcgill_email($email) {
    $email = strtolower(trim((string)$email)); // lowercase = consistent comparison

    // Check if email ends with @mcgill.ca or @mail.mcgill.ca
    return preg_match('/@mcgill\.ca$/', $email) || preg_match('/@mail\.mcgill\.ca$/', $email);
}

// determine if person is owner or user based on their email
function get_role_from_email($email) {
    $email = strtolower(trim((string)$email));

    if (preg_match('/@mcgill\.ca$/', $email)) {
        return "owner"; // prof or staff
    }

    if (preg_match('/@mail\.mcgill\.ca$/', $email)) {
        return "user"; // student
    }

    return null; // if it's not a valid McGill domain
}

// check if email belongs to an owner
function is_owner($email) {
    return preg_match('/@mcgill\.ca$/', strtolower(trim((string)$email)));
}

// check if email belongs to a student user
function is_user($email) {
    return preg_match('/@mail\.mcgill\.ca$/', strtolower(trim((string)$email)));
}

// redirect user to another page
function redirect($url) {
    header("Location: $url"); // send HTTP header to browser to go to another page
    exit(); // once redirected, stop script
}

// clean user input (remove spaces, convert special chars to safe HTML)
function clean_input($value) {
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8'); // better for security
}

?>