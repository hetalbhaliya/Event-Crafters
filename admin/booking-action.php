<?php

session_start();


/* =========================================
   ADMIN LOGIN CHECK
========================================= */

if(!isset($_SESSION['admin_id']))
{
    header("Location: index.php");
    exit;
}


/* =========================================
   DATABASE
========================================= */

include "../config/db.php";


/* =========================================
   BOOKING ID CHECK
========================================= */

if(
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
)
{
    header("Location: bookings.php");
    exit;
}

$booking_id = (int)$_GET['id'];


/* =========================================
   MESSAGE
========================================= */

$message = "";
$message_type = "";


/* =========================================
   GET BOOKING DETAILS
========================================= */

$sql = "
    SELECT
        bookings.*,
        events.event_name,
        events.category,
        events.event_date,
        events.event_time,
        events.venue

    FROM bookings

    INNER JOIN events
    ON bookings.event_id = events.id

    WHERE bookings.id = '$booking_id'

    LIMIT 1
";


$result = mysqli_query($conn, $sql);


if(
    !$result ||
    mysqli_num_rows($result) == 0
)
{
    header("Location: bookings.php");
    exit;
}


$booking = mysqli_fetch_assoc($result);


/* =========================================
   UPDATE STATUS
========================================= */

if(isset($_POST['update_status']))
{

    /* =====================================
       CHECK STATUS
    ===================================== */

    if(!isset($_POST['status']))
    {
        $message =
            "Please select a booking status.";

        $message_type = "error";
    }

    else
    {

        $status = trim($_POST['status']);


        /* =================================
           ALLOWED STATUS
        ================================= */

        $allowed_status = array(
            "Pending",
            "Confirmed",
            "Cancelled"
        );


        if(!in_array($status, $allowed_status))
        {
            $message =
                "Invalid booking status.";

            $message_type = "error";
        }

        else
        {

            /* =================================
               OLD STATUS
            ================================= */

            $old_status = $booking['status'];


            /* =================================
               ESCAPE STATUS
            ================================= */

            $status = mysqli_real_escape_string(
                $conn,
                $status
            );


            /* =================================
               UPDATE BOOKING
            ================================= */

            $update_sql = "
                UPDATE bookings
                SET status='$status'
                WHERE id='$booking_id'
            ";


            if(mysqli_query($conn, $update_sql))
            {

                /* =================================
                   EVENT NAME
                ================================= */

                $event_name =
                    mysqli_real_escape_string(
                        $conn,
                        $booking['event_name']
                    );


                /* =================================
                   CONFIRMED
                ================================= */

                if($status == "Confirmed")
                {

                    $notification_title =
                        "Booking Confirmed";

                    $notification_message =
                        "Your booking for "
                        . $event_name
                        . " has been confirmed successfully.";

                }


                /* =================================
                   CANCELLED
                ================================= */

                elseif($status == "Cancelled")
                {

                    $notification_title =
                        "Booking Cancelled";

                    $notification_message =
                        "Your booking for "
                        . $event_name
                        . " has been cancelled.";

                }


                /* =================================
                   PENDING
                ================================= */

                else
                {

                    $notification_title =
                        "Booking Status Updated";

                    $notification_message =
                        "Your booking for "
                        . $event_name
                        . " is currently pending.";

                }


                /* =================================
                   ESCAPE NOTIFICATION
                ================================= */

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


                /* =================================
                   USER ID
                ================================= */

                $user_id =
                    (int)$booking['user_id'];


                /* =================================
                   INSERT NOTIFICATION
                ================================= */

                $notification_sql = "

                    INSERT INTO notifications
                    (
                        user_id,
                        title,
                        message,
                        type,
                        is_read,
                        created_at
                    )

                    VALUES
                    (
                        '$user_id',
                        '$notification_title',
                        '$notification_message',
                        'booking',
                        0,
                        NOW()
                    )

                ";


                $notification_result =
                    mysqli_query(
                        $conn,
                        $notification_sql
                    );


                /* =================================
                   CHECK NOTIFICATION
                ================================= */

                if($notification_result)
                {

                    $message =
                        "Booking status updated and notification sent successfully.";

                    $message_type = "success";

                }

                else
                {

                    $message =
                        "Booking status updated, but notification failed.";

                    $message_type = "error";

                }


                /* =================================
                   UPDATE DISPLAY
                ================================= */

                $booking['status'] =
                    $status;

            }

            else
            {

                $message =
                    "Failed to update booking status: "
                    . mysqli_error($conn);

                $message_type = "error";

            }

        }

    }

}


/* =========================================
   HEADER
========================================= */

include "../includes/header.php";

?>

<link
    rel="stylesheet"
    href="../assets/css/admin-booking-action.css"
>


<!-- =========================================
     BACK BUTTON
========================================= -->

<a
    href="bookings.php"
    class="admin-back-btn"
>

    <i class="fas fa-arrow-left"></i>

    Back to Bookings

</a>


<!-- =========================================
     SECTION
========================================= -->

<section class="admin-booking-action-section">

    <div class="admin-booking-action-container">


        <!-- =====================================
             HEADING
        ====================================== -->

        <div class="admin-booking-action-heading">

            <span>
                ADMIN PANEL
            </span>

            <h1>
                Booking Details
            </h1>

            <p>
                Review booking information and update its status.
            </p>

        </div>


        <!-- =====================================
             MESSAGE
        ====================================== -->

        <?php if($message != "") { ?>

            <div
                class="
                    admin-booking-message
                    <?php echo $message_type; ?>
                "
            >

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php } ?>


        <!-- =====================================
             MAIN CARD
        ====================================== -->

        <div class="admin-booking-action-card">


            <!-- =================================
                 EVENT
            ================================== -->

            <div class="booking-action-event">

                <div class="booking-action-icon">

                    <i class="fas fa-calendar-check"></i>

                </div>


                <div>

                    <span>
                        EVENT
                    </span>

                    <h2>

                        <?php
                        echo htmlspecialchars(
                            $booking['event_name']
                        );
                        ?>

                    </h2>

                    <small>

                        <?php
                        echo htmlspecialchars(
                            $booking['category']
                        );
                        ?>

                    </small>

                </div>

            </div>


            <!-- =================================
                 CUSTOMER INFORMATION
            ================================== -->

            <div class="booking-action-section-title">

                <i class="fas fa-user"></i>

                Customer Information

            </div>


            <div class="booking-action-grid">


                <div class="booking-detail-item">

                    <span>
                        Full Name
                    </span>

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $booking['full_name']
                        );
                        ?>

                    </strong>

                </div>


                <div class="booking-detail-item">

                    <span>
                        Email
                    </span>

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $booking['email']
                        );
                        ?>

                    </strong>

                </div>


                <div class="booking-detail-item">

                    <span>
                        Mobile
                    </span>

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $booking['mobile']
                        );
                        ?>

                    </strong>

                </div>


                <div class="booking-detail-item">

                    <span>
                        Total Persons
                    </span>

                    <strong>

                        <i class="fas fa-users"></i>

                        <?php
                        echo (int)$booking['total_person'];
                        ?>

                    </strong>

                </div>


            </div>


            <!-- =================================
                 EVENT INFORMATION
            ================================== -->

            <div class="booking-action-section-title">

                <i class="fas fa-circle-info"></i>

                Event Information

            </div>


            <div class="booking-action-grid">


                <div class="booking-detail-item">

                    <span>
                        Event Date
                    </span>

                    <strong>

                        <?php

                        echo date(
                            "d M Y",
                            strtotime(
                                $booking['event_date']
                            )
                        );

                        ?>

                    </strong>

                </div>


                <div class="booking-detail-item">

                    <span>
                        Event Time
                    </span>

                    <strong>

                        <?php

                        echo date(
                            "h:i A",
                            strtotime(
                                $booking['event_time']
                            )
                        );

                        ?>

                    </strong>

                </div>


                <div class="booking-detail-item">

                    <span>
                        Venue
                    </span>

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $booking['venue']
                        );
                        ?>

                    </strong>

                </div>


                <div class="booking-detail-item">

                    <span>
                        Booking Date
                    </span>

                    <strong>

                        <?php

                        echo date(
                            "d M Y",
                            strtotime(
                                $booking['booking_date']
                            )
                        );

                        ?>

                    </strong>

                </div>


            </div>


            <!-- =================================
                 TOTAL AMOUNT
            ================================== -->

            <div class="booking-action-total">

                <span>
                    Total Amount
                </span>

                <strong>

                    ₹<?php

                    echo number_format(
                        (float)$booking['total_amount']
                    );

                    ?>

                </strong>

            </div>


            <!-- =================================
                 STATUS UPDATE
            ================================== -->

            <div class="booking-status-update">


                <div class="booking-action-section-title">

                    <i class="fas fa-pen-to-square"></i>

                    Update Booking Status

                </div>


                <form method="POST">


                    <div class="status-options">


                        <!-- =========================
                             PENDING
                        ========================== -->

                        <label
                            class="
                                status-option
                                pending-option
                            "
                        >

                            <input
                                type="radio"
                                name="status"
                                value="Pending"

                                <?php

                                if(
                                    strtolower(
                                        $booking['status']
                                    ) == "pending"
                                )
                                {
                                    echo "checked";
                                }

                                ?>

                            >

                            <span>

                                <i class="fas fa-clock"></i>

                                Pending

                            </span>

                        </label>


                        <!-- =========================
                             CONFIRMED
                        ========================== -->

                        <label
                            class="
                                status-option
                                confirmed-option
                            "
                        >

                            <input
                                type="radio"
                                name="status"
                                value="Confirmed"

                                <?php

                                if(
                                    strtolower(
                                        $booking['status']
                                    ) == "confirmed"
                                )
                                {
                                    echo "checked";
                                }

                                ?>

                            >

                            <span>

                                <i class="fas fa-circle-check"></i>

                                Confirmed

                            </span>

                        </label>


                        <!-- =========================
                             CANCELLED
                        ========================== -->

                        <label
                            class="
                                status-option
                                cancelled-option
                            "
                        >

                            <input
                                type="radio"
                                name="status"
                                value="Cancelled"

                                <?php

                                if(
                                    strtolower(
                                        $booking['status']
                                    ) == "cancelled"
                                )
                                {
                                    echo "checked";
                                }

                                ?>

                            >

                            <span>

                                <i class="fas fa-circle-xmark"></i>

                                Cancelled

                            </span>

                        </label>


                    </div>


                    <!-- =================================
                         UPDATE BUTTON
                    ================================== -->

                    <button
                        type="submit"
                        name="update_status"
                        class="update-status-btn"
                    >

                        <i class="fas fa-save"></i>

                        Update Status

                    </button>


                </form>


            </div>


        </div>


    </div>

</section>


<?php

include "../includes/footer.php";

?>