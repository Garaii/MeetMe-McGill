<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$db_user = "cs307-user";
$db_pass = "Qt92g4K6zxOvGkoeV2zU";
$db_name = "comp-307-db";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

$conn->set_charset("utf8mb4");
?>
