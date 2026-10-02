<?php

session_start();


/* =========================================
   ADMIN LOGIN CHECK
========================================= */

if(!isset($_SESSION['admin_id']))
{
    header("Location: login.php");
    exit;
}


/* =========================================
   DATABASE
========================================= */

include "../config/db.php";


/* =========================================
   EVENT ID CHECK
========================================= */

if(!isset($_GET['id']) || !is_numeric($_GET['id']))
{
    header("Location: events.php");
    exit;
}


$event_id = (int)$_GET['id'];


/* =========================================
   GET EVENT IMAGE
========================================= */

$sql = "SELECT image
        FROM events
        WHERE id='$event_id'
        LIMIT 1";

$result = mysqli_query($conn, $sql);


if(!$result || mysqli_num_rows($result) == 0)
{
    header("Location: events.php");
    exit;
}


$event = mysqli_fetch_assoc($result);

$image = $event['image'];


/* =========================================
   DELETE EVENT
========================================= */

$delete_sql = "DELETE FROM events
               WHERE id='$event_id'";


if(mysqli_query($conn, $delete_sql))
{

    /* =====================================
       DELETE EVENT IMAGE
    ====================================== */

    if(!empty($image))
    {

        $image_path =
            "../uploads/events/" .
            $image;


        if(file_exists($image_path))
        {
            unlink($image_path);
        }

    }


    /* =====================================
       REDIRECT
    ====================================== */

    header("Location: events.php?deleted=1");
    exit;

}

else
{

    header("Location: events.php?deleted=0");
    exit;

}

?>