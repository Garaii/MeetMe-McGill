<?php

// db.php
// Opens the SQLite database used by the project.

function get_db() {
    $db_path = __DIR__ . "/meetme.sqlite";
    // database file is stored in the same api folder

    try {
        $db = new PDO("sqlite:" . $db_path);
        // create PDO connection to SQLite

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // show database errors clearly while developing

        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        // make query results easier to use

        return $db;

    } catch (PDOException $e) {
        send_json([
            "success" => false,
            "message" => "Database connection failed.",
            "error" => $e->getMessage()
        ], 500);
    }
}

?>