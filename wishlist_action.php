<?php

session_start();


// =========================================
// USER LOGIN CHECK
// =========================================

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit;
}


// =========================================
// DATABASE
// =========================================

include "config/db.php";


// =========================================
// USER + EVENT ID
// =========================================

$user_id = (int)$_SESSION['user_id'];

$event_id = isset($_GET['event_id'])
          ? (int)$_GET['event_id']
          : 0;


// =========================================
// EVENT ID CHECK
// =========================================

if($event_id <= 0)
{
    header("Location: events.php");
    exit;
}


// =========================================
// CHECK IF ALREADY IN WISHLIST
// =========================================

$check = mysqli_query(
    $conn,
    "SELECT id
     FROM wishlist
     WHERE user_id='$user_id'
     AND event_id='$event_id'
     LIMIT 1"
);


// =========================================
// REMOVE FROM WISHLIST
// =========================================

if(mysqli_num_rows($check) > 0)
{
    mysqli_query(
        $conn,
        "DELETE FROM wishlist
         WHERE user_id='$user_id'
         AND event_id='$event_id'"
    );
}


// =========================================
// ADD TO WISHLIST
// =========================================

else
{
    mysqli_query(
        $conn,
        "INSERT INTO wishlist
         (user_id, event_id)
         VALUES
         ('$user_id', '$event_id')"
    );
}


// =========================================
// BACK TO EVENTS
// =========================================

header("Location: events.php");
exit;

?>