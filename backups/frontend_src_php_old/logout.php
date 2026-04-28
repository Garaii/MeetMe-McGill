<?php
require_once "auth.php";
// auth.php to make sure session is started

$_SESSION = [];
// remove all session variables (clear user data)

session_destroy();
// destroy the session completely (user is logged out)

header("Location: login.php");
// redirect user to login page after logout

exit();
// stop script execution after redirect
?>