<?php
/* CONTRIBUTORS AND TASK COORDINATION FOR PHP FILES
MAZEN & SARAH

MAZEN: ADAPTED FILES TO WORK WITH SQLITE, SETUP DATABASE CONNECTION AND ENDPOINTS + UPDATED QUERIES
SARAH: BUILT FILE STRUCTURE WITH SQL QUERIES AND LOCAL TESTING, SETUP AUTHENTICATION AND USER SESSION TYPES
BOTH: CREATED ERROR HANDLING, FIXED EDGE CASES, AND TESTED ENDPOINTS WITH FRONTEND
*/

require_once __DIR__ . "/bootstrap.php";

try {
    $db = get_db();

    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL CHECK (role IN ('owner', 'user')),
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS slots (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            owner_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            description TEXT,
            location TEXT,
            start_time TEXT NOT NULL,
            end_time TEXT NOT NULL,
            is_active INTEGER NOT NULL DEFAULT 0,
            slot_type TEXT NOT NULL DEFAULT 'regular',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            recurring_batch_id INTEGER,
            FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS bookings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slot_id INTEGER NOT NULL UNIQUE,
            user_id INTEGER NOT NULL,
            status TEXT NOT NULL DEFAULT 'booked',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (slot_id) REFERENCES slots(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS meeting_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            requester_id INTEGER NOT NULL,
            owner_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            message TEXT,
            requested_start TEXT NOT NULL,
            requested_end TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'pending',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS group_meetings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            owner_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            description TEXT,
            location TEXT,
            status TEXT NOT NULL DEFAULT 'open',
            finalized_option_id INTEGER,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS group_options (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            group_id INTEGER NOT NULL,
            start_time TEXT NOT NULL,
            end_time TEXT NOT NULL,
            FOREIGN KEY (group_id) REFERENCES group_meetings(id) ON DELETE CASCADE
        );
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS group_votes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            option_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(option_id, user_id),
            FOREIGN KEY (option_id) REFERENCES group_options(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS recurring_batches (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            owner_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            weeks INTEGER NOT NULL,
            location TEXT,
            is_active INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS group_attendees (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            group_id INTEGER NOT NULL,
            slot_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(group_id, user_id),
            FOREIGN KEY (group_id) REFERENCES group_meetings(id) ON DELETE CASCADE,
            FOREIGN KEY (slot_id) REFERENCES slots(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");

    send_json([
        "success" => true,
        "message" => "Database is ready.",
        "database" => "api/meetme.sqlite"
    ]);
} catch (Exception $e) {
    send_json([
        "success" => false,
        "message" => "Database setup failed.",
        "error" => $e->getMessage()
    ], 500);
}
?>
