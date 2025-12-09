<?php
session_start();

if (isset($_SESSION['user_id'])) {
    // Already logged in
    header("Location: ../hostels.html");
    exit();
} else {
    // Not logged in
    header("Location: signup.php");
    exit();
}
?>
