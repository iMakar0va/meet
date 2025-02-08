<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../lk.php');
    exit;
} else {
    header('Location: ../auth.php');
    exit;
}
