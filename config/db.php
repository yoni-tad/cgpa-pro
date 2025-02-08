<?php

require_once '../env.php';
loadEnv(__DIR__ . '/../.env');

// Database configuration
$host = getenv('HOST');
$user = getenv('USERNAME');
$pass = getenv('PASSWORD');
$dbname = getenv('NAME');

$con = mysqli_connect($host, $user, $pass, $dbname);

if(!$con) {
    die('Database connection error: '. mysqli_connect_error());
}