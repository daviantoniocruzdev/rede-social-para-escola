<?php
// Assuming your database file is named 'social_network.db' and is in the same directory as your script.
$db = new SQLite3('social_network.db');

// Check if the 'users' table exists and create it if not.
$query = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE,
            password TEXT,
            email TEXT UNIQUE)";
$db->exec($query);

// Now you can safely prepare statements assuming the 'users' table exists.
// ...

// Don't forget to handle the case when the preparation fails
$stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
if ($stmt === false) {
    throw new Exception("Unable to prepare statement: " . $db->lastErrorMsg());
}

$stmt->bindValue(':username', $_POST['username'], SQLITE3_TEXT);
// ...
?>
