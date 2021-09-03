<?php
session_start();
include_once('helper/functions.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}