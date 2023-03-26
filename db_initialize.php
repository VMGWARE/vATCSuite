<?php

$success = false;

echo "Datbase Initialization Script\n";
echo "=============================\n";

// Check if called via browser
echo "Checking: Script called via browser...\n";
if (isset($_SERVER['HTTP_HOST'])) {
    echo "This script cannot be called via browser.\n";
    exit();
} else {
    echo "Passed: Script called via command line.\n";
}

// Check if constants file exists
echo "Checking: Constants file exists...\n";
if (!file_exists('./includes/constants.php')) {
    echo "Constants file does not exist. This is required to connect to the database.\n";
    exit();
} else {
    echo "Passed: Constants file exists.\n";
}

// Ensure constants file is readable
echo "Checking: Constants file is readable...\n";
if (!is_readable('./includes/constants.php')) {
    echo "Constants file is not readable. Please check the file permissions.\n";
    exit();
} else {
    echo "Passed: Constants file is readable.\n";
}

// Validate PHP version is 8.0 or higher
echo "Checking: PHP version...\n";
if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    echo "Failed: PHP version is too low. Please upgrade to PHP 8.0 or higher.\n";
    exit();
} else {
    echo "Passed: PHP version is 8.1 or higher.\n";
}

// Validate mysqli extension is installed
echo "Checking: mysqli extension...\n";
if (!extension_loaded('mysqli')) {
    echo "Failed: mysqli extension is not installed. Please install the mysqli extension.\n";
    exit();
} else {
    echo "Passed: mysqli extension is installed.\n";
}

require_once './includes/constants.php';

echo "Checking: Database connection...\n";
try {
    $mysqli = new mysqli(HOST, USERNAME, PASSWORD, DATABASE);
} catch (Exception $e) {
    if ($e->getCode() == 1049) {
        echo "Warning: Database does not exist. Attempting to create database...\n";
        $mysqli = new mysqli(HOST, USERNAME, PASSWORD);
        $sql = 'CREATE DATABASE ' . DATABASE;
        if ($mysqli->query($sql)) {
            echo "Passed: Creating database.\n";
        } else {
            echo "Failed: Unable to create database: (" . $mysqli->errno . ") " . $mysqli->error;
            exit();
        }
    } elseif ($e->getCode() == 1045) {
        echo "Failed: Invalid username or password.\n";
        exit();
    } elseif ($e->getCode() == 2002) {
        echo "Failed: Invalid host.\n";
        exit();
    } else {
        echo "Failed: Database connection: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        exit();
    }
}

if ($mysqli->connect_errno) {
    echo "Failed: Database connection: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit();
}

echo "Passed: Database connection.\n";

echo "Checking: Database tables...\n";
// Check that no tables exist
$sql = 'SHOW TABLES';
if ($result = $mysqli->query($sql)) {
    if ($result->num_rows > 0) {
        echo "Failed: Database tables already exist.\n";
        exit();
    } else {
        echo "Passed: Database tables.\n";
    }
} else {
    echo "Failed: Database tables: (" . $mysqli->errno . ") " . $mysqli->error;
    exit();
}

echo "Checking: Creating database tables...\n";
$sql = file_get_contents('./redbbqhz_atis_generator.sql');
if ($mysqli->multi_query($sql)) {
    echo "Passed: Creating database tables.\n";
    $success = true;
} else {
    echo "Failed: Creating database tables: (" . $mysqli->errno . ") " . $mysqli->error;
}

echo "Done!\n";
exit();
