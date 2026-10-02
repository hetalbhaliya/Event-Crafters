<?php

session_start();

/* =====================================================
   LOGIN CHECK
===================================================== */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}


/* =====================================================
   DATABASE
===================================================== */

include "../config/db.php";


/* =====================================================
   USER ID
===================================================== */

$user_id = (int) $_SESSION['user_id'];


/* =====================================================
   BOOKING ID CHECK
===================================================== */

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: my_bookings.php");
    exit;
}

$booking_id = (int) $_GET['id'];


/* =====================================================
   GET BOOKING DETAILS
===================================================== */

$sql = "
    SELECT

        /* ==============================
           BOOKING
        ============================== */

        b.id AS booking_id,
        b.user_id,
        b.event_id,

        b.full_name,
        b.email,
        b.mobile,
        b.total_person,
        b.total_amount,
        b.status,
        b.booking_date,


        /* ==============================
           EVENT
        ============================== */

        e.event_name,
        e.category,
        e.subcategory,
        e.event_date,
        e.event_time,
        e.venue,
        e.image,
        e.price

    FROM bookings b


    /* ==============================
       EVENT JOIN
    ============================== */

    INNER JOIN events e
        ON b.event_id = e.id


    /* ==============================
       USER BOOKING ONLY
    ============================== */

    WHERE b.id = $booking_id
    AND b.user_id = $user_id

    LIMIT 1
";


$result = mysqli_query($conn, $sql);


/* =====================================================
   DATABASE ERROR
===================================================== */

if (!$result) {

    die(
        "Database Error: " .
        htmlspecialchars(mysqli_error($conn))
    );

}


/* =====================================================
   BOOKING NOT FOUND
===================================================== */

if (mysqli_num_rows($result) == 0) {

    header("Location: my_bookings.php");
    exit;

}


/* =====================================================
   FETCH BOOKING
===================================================== */

$booking = mysqli_fetch_assoc($result);


/* =====================================================
   STATUS
===================================================== */

$status = strtolower(
    trim(
        $booking['status'] ?? ''
    )
);


/* =====================================================
   SAFE VALUES
===================================================== */

$event_name = htmlspecialchars(
    $booking['event_name'] ?? ''
);

$category = htmlspecialchars(
    $booking['category'] ?? ''
);

$subcategory = htmlspecialchars(
    $booking['subcategory'] ?? ''
);

$venue = htmlspecialchars(
    $booking['venue'] ?? ''
);

$full_name = htmlspecialchars(
    $booking['full_name'] ?? ''
);

$email = htmlspecialchars(
    $booking['email'] ?? ''
);

$mobile = htmlspecialchars(
    $booking['mobile'] ?? ''
);

$image = htmlspecialchars(
    $booking['image'] ?? ''
);


/* =====================================================
   FORMAT EVENT DATE
===================================================== */

$event_date = "N/A";

if (
    !empty($booking['event_date']) &&
    strtotime($booking['event_date']) !== false
) {

    $event_date = date(
        "d M Y",
        strtotime(
            $booking['event_date']
        )
    );

}


/* =====================================================
   FORMAT EVENT TIME
===================================================== */

$event_time = "N/A";

if (
    !empty($booking['event_time']) &&
    strtotime($booking['event_time']) !== false
) {

    $event_time = date(
        "h:i A",
        strtotime(
            $booking['event_time']
        )
    );

}


/* =====================================================
   FORMAT BOOKING DATE
===================================================== */

$booking_date = "N/A";

if (
    !empty($booking['booking_date']) &&
    strtotime($booking['booking_date']) !== false
) {

    $booking_date = date(
        "d M Y",
        strtotime(
            $booking['booking_date']
        )
    );

}


/* =====================================================
   PRICE
===================================================== */

/*
   Event price is a FIXED PACKAGE price.
   It is NOT multiplied by number of persons.
*/

$event_price = (float)(
    $booking['price'] ?? 0
);

$total_amount = (float)(
    $booking['total_amount'] ?? 0
);

$total_person = (int)(
    $booking['total_person'] ?? 0
);

$booking_id_display = (int)(
    $booking['booking_id']
);


/* =====================================================
   HEADER
===================================================== */

include "../includes/header.php";

include "../includes/navbar.php";

?>


<!-- =====================================================
     CSS
===================================================== -->

<link
    rel="stylesheet"
    href="../assets/css/booking-details.css"
>


<!-- =====================================================
     BACK BUTTON
===================================================== -->

<div class="booking-details-back-wrapper">

    <a
        href="my_bookings.php"
        class="back-btn"
    >

        <i class="fas fa-arrow-left"></i>

        Back to My Bookings

    </a>

</div>


<!-- =====================================================
     MAIN SECTION
===================================================== -->

<section class="booking-details-section">

    <div class="booking-details-container">


        <!-- =================================================
             PAGE HEADING
        ================================================== -->

        <div class="booking-details-heading">

            <span>
                USER ACCOUNT
            </span>

            <h1>
                Booking Details
            </h1>

            <p>
                View complete information about your booking.
            </p>

        </div>


        <!-- =================================================
             BOOKING CARD
        ================================================== -->

        <div class="booking-details-card">


            <!-- =================================================
                 EVENT IMAGE
            ================================================== -->

            <div class="booking-details-image">

                <?php if (!empty($image)) { ?>

                    <img
                        src="../uploads/events/<?php echo $image; ?>"
                        alt="<?php echo $event_name; ?>"
                    >

                <?php } else { ?>

                    <div class="booking-details-no-image">

                        <i class="fas fa-calendar-days"></i>

                        <span>
                            No Image Available
                        </span>

                    </div>

                <?php } ?>


                <!-- =================================================
                     STATUS
                ================================================== -->

                <div
                    class="booking-details-status <?php echo htmlspecialchars($status); ?>"
                >

                    <?php

                    if ($status === "confirmed") {

                        echo '<i class="fas fa-circle-check"></i>';

                    }
                    elseif ($status === "cancelled") {

                        echo '<i class="fas fa-circle-xmark"></i>';

                    }
                    else {

                        echo '<i class="fas fa-clock"></i>';

                    }

                    ?>

                    <span>

                        <?php
                        echo htmlspecialchars(
                            $booking['status'] ?? ''
                        );
                        ?>

                    </span>

                </div>

            </div>


            <!-- =================================================
                 EVENT TITLE
            ================================================== -->

            <div class="booking-details-event">

                <span class="booking-details-category">

                    <?php
                    echo $category;
                    ?>


                    <?php if (!empty($subcategory)) { ?>

                        <small
                            style="
                                margin-left:7px;
                                color:#35D6FF;
                            "
                        >

                            /

                            <?php
                            echo $subcategory;
                            ?>

                        </small>

                    <?php } ?>

                </span>


                <h2>

                    <?php
                    echo $event_name;
                    ?>

                </h2>


                <p>

                    <i class="fas fa-location-dot"></i>

                    <?php
                    echo $venue;
                    ?>

                </p>

            </div>


            <!-- =================================================
                 EVENT INFORMATION
            ================================================== -->

            <div class="details-section-title">

                <i class="fas fa-calendar-days"></i>

                Event Information

            </div>


            <div class="details-grid">


                <!-- EVENT DATE -->

                <div class="details-item">

                    <span>
                        Event Date
                    </span>

                    <strong>

                        <?php
                        echo $event_date;
                        ?>

                    </strong>

                </div>


                <!-- EVENT TIME -->

                <div class="details-item">

                    <span>
                        Event Time
                    </span>

                    <strong>

                        <?php
                        echo $event_time;
                        ?>

                    </strong>

                </div>


                <!-- VENUE -->

                <div class="details-item">

                    <span>
                        Venue
                    </span>

                    <strong>

                        <?php
                        echo $venue;
                        ?>

                    </strong>

                </div>


                <!-- CATEGORY -->

                <div class="details-item">

                    <span>
                        Category
                    </span>

                    <strong>

                        <?php
                        echo $category;
                        ?>

                        <?php if (!empty($subcategory)) { ?>

                            <small
                                style="
                                    color:#35D6FF;
                                    margin-left:5px;
                                "
                            >

                                /

                                <?php
                                echo $subcategory;
                                ?>

                            </small>

                        <?php } ?>

                    </strong>

                </div>


            </div>


            <!-- =================================================
                 CUSTOMER INFORMATION
            ================================================== -->

            <div class="details-section-title">

                <i class="fas fa-user"></i>

                Customer Information

            </div>


            <div class="details-grid">


                <!-- NAME -->

                <div class="details-item">

                    <span>
                        Full Name
                    </span>

                    <strong>

                        <?php
                        echo $full_name;
                        ?>

                    </strong>

                </div>


                <!-- EMAIL -->

                <div class="details-item">

                    <span>
                        Email Address
                    </span>

                    <strong>

                        <?php
                        echo $email;
                        ?>

                    </strong>

                </div>


                <!-- MOBILE -->

                <div class="details-item">

                    <span>
                        Mobile Number
                    </span>

                    <strong>

                        <?php
                        echo $mobile;
                        ?>

                    </strong>

                </div>


                <!-- PERSONS -->

                <div class="details-item">

                    <span>
                        Total Persons
                    </span>

                    <strong>

                        <i class="fas fa-users"></i>

                        <?php
                        echo $total_person;
                        ?>

                        Person(s)

                    </strong>

                </div>


            </div>


            <!-- =================================================
                 BOOKING & PAYMENT
            ================================================== -->

            <div class="details-section-title">

                <i class="fas fa-receipt"></i>

                Booking & Payment

            </div>


            <div class="details-grid">


                <!-- BOOKING ID -->

                <div class="details-item">

                    <span>
                        Booking ID
                    </span>

                    <strong>

                        #<?php
                        echo $booking_id_display;
                        ?>

                    </strong>

                </div>


                <!-- BOOKING DATE -->

                <div class="details-item">

                    <span>
                        Booking Date
                    </span>

                    <strong>

                        <?php
                        echo $booking_date;
                        ?>

                    </strong>

                </div>


                <!-- EVENT PACKAGE -->

                <div class="details-item">

                    <span>
                        Event Package
                    </span>

                    <strong>

                        ₹<?php

                        echo number_format(
                            $event_price,
                            2
                        );

                        ?>

                    </strong>

                </div>


                <!-- PERSONS -->

                <div class="details-item">

                    <span>
                        Total Persons
                    </span>

                    <strong>

                        <?php
                        echo $total_person;
                        ?>

                    </strong>

                </div>


            </div>


            <!-- =================================================
                 TOTAL AMOUNT
            ================================================== -->

            <div class="booking-total-box">

                <div>

                    <span>
                        Total Amount
                    </span>

                    <small>
                        Event package, food and applicable charges
                    </small>

                </div>


                <strong>

                    ₹<?php

                    echo number_format(
                        $total_amount,
                        2
                    );

                    ?>

                </strong>

            </div>


            <!-- =================================================
                 ACTION BUTTONS
            ================================================== -->

            <div class="booking-details-actions">


                <!-- MY BOOKINGS -->

                <a
                    href="my_bookings.php"
                    class="details-back-btn"
                >

                    <i class="fas fa-arrow-left"></i>

                    My Bookings

                </a>


                <?php

                if ($status === "pending") {

                ?>


                    <!-- CANCEL -->

                    <a
                        href="cancel-booking.php?id=<?php echo $booking_id_display; ?>"
                        class="details-cancel-btn"

                        onclick="
                            return confirm(
                                'Are you sure you want to cancel this booking?'
                            );
                        "
                    >

                        <i class="fas fa-xmark"></i>

                        Cancel Booking

                    </a>


                <?php

                }

                elseif ($status === "confirmed") {

                ?>


                    <!-- CONFIRMED -->

                    <span
                        class="details-confirmed-label"
                    >

                        <i
                            class="fas fa-circle-check"
                        ></i>

                        Booking Confirmed

                    </span>


                <?php

                }

                elseif ($status === "cancelled") {

                ?>


                    <!-- CANCELLED -->

                    <span
                        class="details-cancelled-label"
                    >

                        <i
                            class="fas fa-circle-xmark"
                        ></i>

                        Booking Cancelled

                    </span>


                <?php

                }

                ?>


            </div>


        </div>

    </div>

</section>


<?php

include "../includes/footer.php";

?>