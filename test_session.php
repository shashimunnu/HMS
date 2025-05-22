<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "Session status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";
echo "Session save path: " . session_save_path() . "<br>";
echo "Session name: " . session_name() . "<br>";

// Test setting a session variable
$_SESSION['test'] = 'test_value';
echo "Session test value: " . ($_SESSION['test'] ?? 'not set') . "<br>";

// Display all session data
echo "All session data:<br>";
var_dump($_SESSION);
