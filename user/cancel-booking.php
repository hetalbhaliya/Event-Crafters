<?php

session_start();

/* =========================================
   LOGIN CHECK
========================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}


/* =========================================
   DATABASE
========================================= */

include "../config/db.php";

$user_id = (int) $_SESSION['user_id'];


/* =========================================
   BOOKING ID CHECK
========================================= */

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    header("Location: my_bookings.php");
    exit;
}

$booking_id = (int) $_GET['id'];


/* =========================================
   GET BOOKING
========================================= */

$sql = "
    SELECT
        bookings.id,
        bookings.user_id,
        bookings.event_id,
        bookings.status,
        events.event_name

    FROM bookings

    INNER JOIN events
        ON bookings.event_id = events.id

    WHERE bookings.id = '$booking_id'
    AND bookings.user_id = '$user_id'

    LIMIT 1
";


$result = mysqli_query($conn, $sql);


if (!$result) {

    die(
        "Database Error: " .
        htmlspecialchars(mysqli_error($conn))
    );

}


if (mysqli_num_rows($result) == 0) {

    header("Location: my_bookings.php");
    exit;

}


$booking = mysqli_fetch_assoc($result);


/* =========================================
   CHECK CURRENT STATUS
========================================= */

$current_status = strtolower(
    trim($booking['status'])
);


if (
    $current_status === "cancelled"
) {

    header("Location: my_bookings.php");
    exit;

}


if (
    $current_status === "confirmed"
) {

    header("Location: my_bookings.php");
    exit;

}


/* =========================================
   CANCEL BOOKING
========================================= */

$update_sql = "
    UPDATE bookings
    SET status = 'Cancelled'
    WHERE id = '$booking_id'
    AND user_id = '$user_id'
    AND status = 'Pending'
    LIMIT 1
";


if (mysqli_query($conn, $update_sql)) {


    /* =====================================
       NOTIFICATION
    ====================================== */

    $event_name = mysqli_real_escape_string(
        $conn,
        $booking['event_name']
    );


    $notification_title =
        "Booking Cancelled";


    $notification_message =
        "Your booking for " .
        $event_name .
        " has been cancelled successfully.";


    $notification_title =
        mysqli_real_escape_string(
            $conn,
            $notification_title
        );


    $notification_message =
        mysqli_real_escape_string(
            $conn,
            $notification_message
        );


    $notification_sql = "
        INSERT INTO notifications
        (
            user_id,
            type,
            title,
            message,
            is_read,
            created_at
        )
        VALUES
        (
            '$user_id',
            'booking',
            '$notification_title',
            '$notification_message',
            0,
            NOW()
        )
    ";


    mysqli_query(
        $conn,
        $notification_sql
    );


    /* =====================================
       BACK TO BOOKINGS
    ====================================== */

    header(
        "Location: my_bookings.php"
    );

    exit;

}
else {

    die(
        "Unable to cancel booking. " .
        htmlspecialchars(
            mysqli_error($conn)
        )
    );

}

?>