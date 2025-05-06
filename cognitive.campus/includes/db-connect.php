<?php
// Start the session
session_start();

// Begin output buffering
ob_start();

require 'vendor/autoload.php';
require __DIR__ . "/config.php";

// Create a new MySQLi object to connect to the database
$db = new mysqli(DB_HOST,DB_USER,DB_PASS,DB);

// Check for a connection error and stop the script if one occurs
if ($db->connect_error) {
    die("Connection Failed: " . $db->connect_error); // Display error message and terminate the script
}

// Set the default timezone to Pakistan Standard Time
date_default_timezone_set('Asia/Karachi');
