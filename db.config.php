<?php

session_start();

if (!isset($aux)) $aux = false;

if (isset($aux) && !$aux) {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header('Location: index.php');
        exit;
    }
}

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'zoomout';

global $con;
    $con = new mysqli($host, $user, $password, $dbname);

    if ($con->connect_error) {
        die('Connection failed: ' . $con->connect_error);
    }
?>
