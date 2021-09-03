<?php
error_reporting(0);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_log', './error.log');
error_reporting(E_ALL);

$conn = new PDO("mysql:host=localhost;dbname=auth", "root", "");