<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

$user_id = (int)$_SESSION['user_id'];

$notification_id = isset($_GET['id'])
                 ? (int)$_GET['id']
                 : 0;

if($notification_id > 0)
{
    mysqli_query(
        $conn,
        "UPDATE notifications
         SET is_read = 1
         WHERE id = '$notification_id'
         AND user_id = '$user_id'"
    );
}

header("Location: notifications.php");
exit;

?>