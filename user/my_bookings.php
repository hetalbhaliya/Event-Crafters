

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


/* =========================================
   USER ID
========================================= */

$user_id = (int)$_SESSION['user_id'];


/* =========================================
   UNREAD NOTIFICATION COUNT
========================================= */

$notification_sql = "
    SELECT COUNT(*) AS unread_count
    FROM notifications
    WHERE user_id = '$user_id'
    AND is_read = 0
";

$notification_result = mysqli_query(
    $conn,
    $notification_sql
);

$unread_count = 0;

if ($notification_result) {

    $notification_row = mysqli_fetch_assoc(
        $notification_result
    );

    $unread_count = (int)(
        $notification_row['unread_count'] ?? 0
    );

}


/* =========================================
   STATUS FILTER
========================================= */

$status_filter = '';

$requested_status = '';

if (isset($_GET['status'])) {

    $requested_status = trim($_GET['status']);

}


/*
    Only these three statuses are allowed.
    This prevents invalid values.
*/

if (
    $requested_status === 'Pending' ||
    $requested_status === 'Confirmed' ||
    $requested_status === 'Cancelled'
) {

    $status_filter = "
        AND LOWER(TRIM(bookings.status))
        = LOWER('$requested_status')
    ";

}


/* =========================================
   GET USER BOOKINGS
========================================= */

$sql = "

    SELECT

        bookings.id AS booking_id,
        bookings.user_id,
        bookings.event_id,
        bookings.total_person,
        bookings.total_amount,
        bookings.status,
        bookings.booking_date,

        events.event_name,
        events.category,
        events.subcategory,
        events.event_date,
        events.event_time,
        events.venue,
        events.image,
        events.price

    FROM bookings

    INNER JOIN events
        ON bookings.event_id = events.id

    WHERE bookings.user_id = '$user_id'

    $status_filter

    ORDER BY bookings.id DESC

";


$result = mysqli_query(
    $conn,
    $sql
);


/* =========================================
   DATABASE ERROR
========================================= */

if (!$result) {

    die(
        "Database Error: " .
        htmlspecialchars(
            mysqli_error($conn)
        )
    );

}


/* =========================================
   HEADER
========================================= */

include "../includes/header.php";

include "../includes/navbar.php";

?>
<a href="javascript:history.back()" class="back-btn">
    <i class="fas fa-arrow-left"></i>
    Back
</a>


<!-- =========================================
     MY BOOKINGS CSS
========================================= -->

<link
    rel="stylesheet"
    href="../assets/css/my-bookings.css"
>


<!-- =========================================
     MAIN SECTION
========================================= -->

<section class="bookings-section">

    <div class="bookings-container">


        <!-- =====================================
             PAGE HEADER
        ====================================== -->

        <div class="bookings-heading">

            <div>

                <span class="booking-heading-label">
                    USER ACCOUNT
                </span>


                <h1>
                    My Bookings
                </h1>


                <p>

                    <?php

                    if ($requested_status === 'Pending') {

                        echo "View your pending event bookings.";

                    }
                    elseif ($requested_status === 'Confirmed') {

                        echo "View your confirmed event bookings.";

                    }
                    elseif ($requested_status === 'Cancelled') {

                        echo "View your cancelled event bookings.";

                    }
                    else {

                        echo "View and manage your event bookings.";

                    }

                    ?>

                </p>

            </div>


            <!-- =================================
                 HEADER BUTTONS
            ================================== -->

            <div class="booking-heading-actions">


                <!-- Notifications -->

                <a
                    href="notifications.php"
                    class="notification-booking-btn"
                >

                    <i class="fas fa-bell"></i>

                    Notifications


                    <?php

                    if ($unread_count > 0) {

                    ?>

                        <span class="notification-count">

                            <?php
                            echo $unread_count;
                            ?>

                        </span>

                    <?php

                    }

                    ?>

                </a>


                <!-- Explore Events -->

                <a
                    href="../events.php"
                    class="explore-events-btn"
                >

                    <i class="fas fa-calendar-plus"></i>

                    Explore Events

                </a>

            </div>

        </div>


        <!-- =====================================
             FILTER INFORMATION
        ====================================== -->

        <?php

        if (
            $requested_status === 'Pending' ||
            $requested_status === 'Confirmed' ||
            $requested_status === 'Cancelled'
        ) {

        ?>

            <div
                style="
                    display:flex;
                    align-items:center;
                    justify-content:space-between;
                    gap:15px;
                    margin:0 0 25px 0;
                    padding:14px 18px;
                    background:rgba(53,214,255,.06);
                    border:1px solid rgba(53,214,255,.15);
                    border-radius:12px;
                "
            >

                <div
                    style="
                        color:#bfc8df;
                        font-family:'Poppins',sans-serif;
                        font-size:14px;
                    "
                >

                    Showing

                    <strong
                        style="
                            color:#35D6FF;
                            margin-left:5px;
                        "
                    >
                        <?php
                        echo htmlspecialchars($requested_status);
                        ?>
                    </strong>

                    bookings only.

                </div>


                <a
                    href="my_bookings.php"
                    style="
                        color:#35D6FF;
                        text-decoration:none;
                        font-weight:600;
                        font-size:14px;
                        white-space:nowrap;
                    "
                >

                    <i class="fas fa-list"></i>

                    View All

                </a>

            </div>

        <?php

        }

        ?>


        <!-- =====================================
             NOTIFICATION ALERT
        ====================================== -->

        <?php

        if ($unread_count > 0) {

        ?>

            <div class="booking-notification-alert">


                <div class="booking-notification-icon">

                    <i class="fas fa-bell"></i>

                </div>


                <div>

                    <strong>

                        You have

                        <?php
                        echo $unread_count;
                        ?>

                        new notification<?php

                        echo (
                            $unread_count > 1
                        )
                            ? "s"
                            : "";

                        ?>

                    </strong>


                    <p>

                        Check your booking updates
                        and event notifications.

                    </p>

                </div>


                <a
                    href="notifications.php"
                    class="booking-notification-link"
                >

                    <i class="fas fa-bell"></i>

                    Notifications

                </a>

            </div>

        <?php

        }


        /* =====================================
           BOOKINGS
        ====================================== */

        if (mysqli_num_rows($result) > 0) {

            while (
                $row = mysqli_fetch_assoc($result)
            ) {


                /* =============================
                   BASIC DATA
                ============================= */

                $booking_id =
                    (int)$row['booking_id'];


                $status =
                    strtolower(
                        trim(
                            $row['status'] ?? ''
                        )
                    );

        ?>


                <!-- =================================
                     BOOKING CARD
                ================================== -->

                <div class="booking-card">


                    <!-- =================================
                         EVENT IMAGE
                    ================================== -->

                    <div class="booking-image">


                        <?php

                        if (
                            !empty(
                                $row['image']
                            )
                        ) {

                        ?>

                            <img
                                src="../uploads/events/<?php
                                echo htmlspecialchars(
                                    $row['image']
                                );
                                ?>"
                                alt="<?php
                                echo htmlspecialchars(
                                    $row['event_name']
                                );
                                ?>"
                            >

                        <?php

                        }
                        else {

                        ?>

                            <div class="booking-no-image">

                                <i
                                    class="fas fa-calendar-days"
                                ></i>

                            </div>

                        <?php

                        }


                        /* =============================
                           STATUS
                        ============================= */

                        if (
                            $status === "confirmed"
                        ) {

                        ?>

                            <span
                                class="booking-status confirmed"
                            >

                                <i
                                    class="fas fa-circle-check"
                                ></i>

                                Confirmed

                            </span>

                        <?php

                        }
                        elseif (
                            $status === "cancelled"
                        ) {

                        ?>

                            <span
                                class="booking-status cancelled"
                            >

                                <i
                                    class="fas fa-circle-xmark"
                                ></i>

                                Cancelled

                            </span>

                        <?php

                        }
                        else {

                        ?>

                            <span
                                class="booking-status pending"
                            >

                                <i
                                    class="fas fa-clock"
                                ></i>

                                Pending

                            </span>

                        <?php

                        }

                        ?>


                    </div>


                    <!-- =================================
                         BOOKING DETAILS
                    ================================== -->

                    <div class="booking-details">


                        <!-- =================================
                             TITLE
                        ================================== -->

                        <div class="booking-title">


                            <span class="booking-category">

                                <?php

                                echo htmlspecialchars(
                                    $row['category'] ?? ''
                                );

                                ?>

                                <?php

                                if (
                                    !empty(
                                        $row['subcategory']
                                    )
                                ) {

                                ?>

                                    <small
                                        style="
                                            margin-left:7px;
                                            color:#35D6FF;
                                        "
                                    >

                                        /

                                        <?php

                                        echo htmlspecialchars(
                                            $row['subcategory']
                                        );

                                        ?>

                                    </small>

                                <?php

                                }

                                ?>

                            </span>


                            <h2>

                                <?php

                                echo htmlspecialchars(
                                    $row['event_name']
                                );

                                ?>

                            </h2>

                        </div>


                        <!-- =================================
                             EVENT INFO
                        ================================== -->

                        <div class="booking-info">


                            <!-- DATE -->

                            <p>

                                <i
                                    class="fas fa-calendar-alt"
                                ></i>

                                <?php

                                if (
                                    !empty(
                                        $row['event_date']
                                    )
                                ) {

                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $row['event_date']
                                        )
                                    );

                                }
                                else {

                                    echo "N/A";

                                }

                                ?>

                            </p>


                            <!-- TIME -->

                            <p>

                                <i
                                    class="fas fa-clock"
                                ></i>

                                <?php

                                if (
                                    !empty(
                                        $row['event_time']
                                    )
                                ) {

                                    echo date(
                                        "h:i A",
                                        strtotime(
                                            $row['event_time']
                                        )
                                    );

                                }
                                else {

                                    echo "N/A";

                                }

                                ?>

                            </p>


                            <!-- VENUE -->

                            <p>

                                <i
                                    class="fas fa-location-dot"
                                ></i>

                                <?php

                                echo htmlspecialchars(
                                    $row['venue'] ?? ''
                                );

                                ?>

                            </p>


                            <!-- PERSONS -->

                            <p>

                                <i
                                    class="fas fa-users"
                                ></i>

                                <?php

                                echo (int)(
                                    $row['total_person'] ?? 0
                                );

                                ?>

                                Person(s)

                            </p>


                        </div>


                        <!-- =================================
                             BOTTOM AREA
                        ================================== -->

                        <div class="booking-bottom">


                            <!-- Booking Date -->

                            <div class="booking-date">

                                <small>
                                    Booking Date
                                </small>


                                <strong>

                                    <?php

                                    if (
                                        !empty(
                                            $row['booking_date']
                                        )
                                    ) {

                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $row['booking_date']
                                            )
                                        );

                                    }
                                    else {

                                        echo "N/A";

                                    }

                                    ?>

                                </strong>

                            </div>


                            <!-- Total Amount -->

                            <div class="booking-price">

                                <small>
                                    Total Amount
                                </small>


                                <strong>

                                    ₹<?php

                                    echo number_format(
                                        (float)(
                                            $row['total_amount'] ?? 0
                                        ),
                                        2
                                    );

                                    ?>

                                </strong>

                            </div>


                            <!-- =================================
                                 ACTIONS
                            ================================== -->

                            <div class="booking-action">


                                <!-- =================================
                                     PENDING
                                ================================== -->

                                <?php

                                if (
                                    $status === "pending"
                                ) {

                                ?>

                                    <span
                                        class="pending-label"
                                    >

                                        <i
                                            class="fas fa-clock"
                                        ></i>

                                        Booking Pending

                                    </span>


                                    <a
                                        href="edit-booking.php?id=<?php
                                        echo $booking_id;
                                        ?>"
                                        class="booking-edit-btn"
                                    >

                                        <i
                                            class="fas fa-pen"
                                        ></i>

                                        Edit Booking

                                    </a>


                                    <a
                                        href="cancel-booking.php?id=<?php
                                        echo $booking_id;
                                        ?>"
                                        class="cancel-booking-btn"
                                        onclick="
                                            return confirm(
                                                'Are you sure you want to cancel this booking?'
                                            );
                                        "
                                    >

                                        <i
                                            class="fas fa-xmark"
                                        ></i>

                                        Cancel Booking

                                    </a>


                                    <a
                                        href="booking-details.php?id=<?php
                                        echo $booking_id;
                                        ?>"
                                        class="view-booking-btn"
                                    >

                                        <i
                                            class="fas fa-eye"
                                        ></i>

                                        View Details

                                    </a>


                                    <a
                                        href="receipt.php?id=<?php
                                        echo $booking_id;
                                        ?>"
                                        class="view-receipt-btn"
                                    >

                                        <i
                                            class="fas fa-receipt"
                                        ></i>

                                        View Receipt

                                    </a>


                                <?php

                                }


                                /* =================================
                                   CONFIRMED
                                ================================== */

                                elseif (
                                    $status === "confirmed"
                                ) {

                                ?>

                                    <span
                                        class="confirmed-label"
                                    >

                                        <i
                                            class="fas fa-circle-check"
                                        ></i>

                                        Booking Confirmed

                                    </span>


                                    <a
                                        href="edit-booking.php?id=<?php
                                        echo $booking_id;
                                        ?>"
                                        class="booking-edit-btn"
                                    >

                                        <i
                                            class="fas fa-pen"
                                        ></i>

                                        Edit Booking

                                    </a>


                                    <a
                                        href="booking-details.php?id=<?php
                                        echo $booking_id;
                                        ?>"
                                        class="view-booking-btn"
                                    >

                                        <i
                                            class="fas fa-eye"
                                        ></i>

                                        View Details

                                    </a>


                                    <a
                                        href="receipt.php?id=<?php
                                        echo $booking_id;
                                        ?>"
                                        class="view-receipt-btn"
                                    >

                                        <i
                                            class="fas fa-receipt"
                                        ></i>

                                        View Receipt

                                    </a>


                                    <a
                                        href="review.php?booking_id=<?php
                                        echo $booking_id;
                                        ?>"
                                        class="review-btn"
                                    >

                                        <i
                                            class="fas fa-star"
                                        ></i>

                                        Review Event

                                    </a>


                                <?php

                                }


                                /* =================================
                                   CANCELLED
                                ================================== */

                                elseif (
                                    $status === "cancelled"
                                ) {

                                ?>

                                    <span
                                        class="cancelled-label"
                                    >

                                        <i
                                            class="fas fa-circle-xmark"
                                        ></i>

                                        Booking Cancelled

                                    </span>


                                    <a
                                        href="booking-details.php?id=<?php
                                        echo $booking_id;
                                        ?>"
                                        class="view-booking-btn"
                                    >

                                        <i
                                            class="fas fa-eye"
                                        ></i>

                                        View Details

                                    </a>


                                    <a
                                        href="receipt.php?id=<?php
                                        echo $booking_id;
                                        ?>"
                                        class="view-receipt-btn"
                                    >

                                        <i
                                            class="fas fa-receipt"
                                        ></i>

                                        View Receipt

                                    </a>

                                <?php

                                }

                                ?>


                            </div>


                        </div>


                    </div>


                </div>


        <?php

            }

        }


        /* =====================================
           NO BOOKINGS
        ====================================== */

        else {

        ?>

            <div class="no-bookings">


                <div class="no-bookings-icon">

                    <i
                        class="fas fa-calendar-xmark"
                    ></i>

                </div>


                <h2>

                    <?php

                    if (
                        $requested_status === 'Pending'
                    ) {

                        echo "No Pending Bookings";

                    }
                    elseif (
                        $requested_status === 'Confirmed'
                    ) {

                        echo "No Confirmed Bookings";

                    }
                    elseif (
                        $requested_status === 'Cancelled'
                    ) {

                        echo "No Cancelled Bookings";

                    }
                    else {

                        echo "No Bookings Yet";

                    }

                    ?>

                </h2>


                <p>

                    <?php

                    if (
                        $requested_status === 'Pending'
                    ) {

                        echo "You currently have no pending event bookings.";

                    }
                    elseif (
                        $requested_status === 'Confirmed'
                    ) {

                        echo "You currently have no confirmed event bookings.";

                    }
                    elseif (
                        $requested_status === 'Cancelled'
                    ) {

                        echo "You currently have no cancelled event bookings.";

                    }
                    else {

                        echo "You have not booked any events yet.";

                    }

                    ?>

                </p>


                <?php

                if (
                    $requested_status !== ''
                ) {

                ?>

                    <a
                        href="my_bookings.php"
                        class="explore-btn"
                    >

                        <i
                            class="fas fa-list"
                        ></i>

                        View All Bookings

                        <i
                            class="fas fa-arrow-right"
                        ></i>

                    </a>

                <?php

                }
                else {

                ?>

                    <a
                        href="../events.php"
                        class="explore-btn"
                    >

                        <i
                            class="fas fa-calendar-days"
                        ></i>

                        Explore Events

                        <i
                            class="fas fa-arrow-right"
                        ></i>

                    </a>

                <?php

                }

                ?>


            </div>

        <?php

        }

        ?>


    </div>

</section>


<?php

include "../includes/footer.php";

?>