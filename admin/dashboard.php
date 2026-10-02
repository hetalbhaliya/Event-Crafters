<?php

session_start();


/* =========================================
   ADMIN LOGIN CHECK
========================================= */

if (!isset($_SESSION['admin_id'])) {

    header("Location: index.php");
    exit;

}


/* =========================================
   DATABASE
========================================= */

include "../config/db.php";


/* =========================================
   HEADER
========================================= */

include "../includes/header.php";


/* =========================================
   ADMIN DETAILS
========================================= */

$admin_id = (int)$_SESSION['admin_id'];

$admin_name = "Admin";

if (
    isset($_SESSION['admin_name']) &&
    $_SESSION['admin_name'] != ""
) {

    $admin_name = $_SESSION['admin_name'];

}


/* =========================================
   TOTAL USERS
========================================= */

$total_users = 0;

$user_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users"
);

if ($user_query) {

    $user_data = mysqli_fetch_assoc($user_query);

    $total_users = (int)$user_data['total'];

}


/* =========================================
   TOTAL EVENTS
========================================= */

$total_events = 0;

$event_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM events"
);

if ($event_query) {

    $event_data = mysqli_fetch_assoc($event_query);

    $total_events = (int)$event_data['total'];

}


/* =========================================
   TOTAL FOOD ITEMS
========================================= */

$total_food = 0;

$food_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM food_items"
);

if ($food_query) {

    $food_data = mysqli_fetch_assoc($food_query);

    $total_food = (int)$food_data['total'];

}


/* =========================================
   ACTIVE FOOD ITEMS
========================================= */

$active_food = 0;

$active_food_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM food_items
     WHERE status = 1"
);

if ($active_food_query) {

    $active_food_data = mysqli_fetch_assoc(
        $active_food_query
    );

    $active_food = (int)$active_food_data['total'];

}


/* =========================================
   TOTAL BOOKINGS
========================================= */

$total_bookings = 0;

$booking_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM bookings"
);

if ($booking_query) {

    $booking_data = mysqli_fetch_assoc(
        $booking_query
    );

    $total_bookings = (int)$booking_data['total'];

}


/* =========================================
   PENDING BOOKINGS
========================================= */

$pending_bookings = 0;

$pending_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM bookings
     WHERE LOWER(TRIM(status)) = 'pending'"
);

if ($pending_query) {

    $pending_data = mysqli_fetch_assoc(
        $pending_query
    );

    $pending_bookings = (int)$pending_data['total'];

}


/* =========================================
   CONFIRMED BOOKINGS
========================================= */

$confirmed_bookings = 0;

$confirmed_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM bookings
     WHERE LOWER(TRIM(status)) = 'confirmed'"
);

if ($confirmed_query) {

    $confirmed_data = mysqli_fetch_assoc(
        $confirmed_query
    );

    $confirmed_bookings =
        (int)$confirmed_data['total'];

}


/* =========================================
   CANCELLED BOOKINGS
========================================= */

$cancelled_bookings = 0;

$cancelled_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM bookings
     WHERE LOWER(TRIM(status)) = 'cancelled'"
);

if ($cancelled_query) {

    $cancelled_data = mysqli_fetch_assoc(
        $cancelled_query
    );

    $cancelled_bookings =
        (int)$cancelled_data['total'];

}


/* =========================================
   TOTAL COUPONS
========================================= */

$total_coupons = 0;

$coupon_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM coupons"
);

if ($coupon_query) {

    $coupon_data = mysqli_fetch_assoc(
        $coupon_query
    );

    $total_coupons =
        (int)$coupon_data['total'];

}


/* =========================================
   ACTIVE COUPONS
========================================= */

$active_coupons = 0;

$active_coupon_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM coupons
     WHERE status = 1"
);

if ($active_coupon_query) {

    $active_coupon_data =
        mysqli_fetch_assoc(
            $active_coupon_query
        );

    $active_coupons =
        (int)$active_coupon_data['total'];

}


/* =========================================
   INACTIVE COUPONS
========================================= */

$inactive_coupons = 0;

$inactive_coupon_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM coupons
     WHERE status = 0"
);

if ($inactive_coupon_query) {

    $inactive_coupon_data =
        mysqli_fetch_assoc(
            $inactive_coupon_query
        );

    $inactive_coupons =
        (int)$inactive_coupon_data['total'];

}


/* =========================================
   TOTAL REVIEWS
========================================= */

$total_reviews = 0;

$review_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM reviews"
);

if ($review_query) {

    $review_data = mysqli_fetch_assoc(
        $review_query
    );

    $total_reviews =
        (int)$review_data['total'];

}

?>


<!-- =========================================
     ADMIN DASHBOARD CSS
========================================= -->

<link
    rel="stylesheet"
    href="../assets/css/admin-dashboard.css"
>


<!-- =========================================
     ADMIN DASHBOARD
========================================= -->

<section class="admin-dashboard">

    <div class="admin-dashboard-container">


        <!-- =====================================
             HEADING
        ====================================== -->

        <div class="admin-heading">

            <span>
                ADMIN PANEL
            </span>

            <h1>

                Welcome,

                <?php
                echo htmlspecialchars($admin_name);
                ?>

            </h1>

            <p>

                Manage your events, categories, food,
                users, bookings, coupons, gallery and
                reviews from one place.

            </p>

        </div>



        <!-- =====================================
             STATISTICS
        ====================================== -->

        <div class="admin-stats">


            <!-- USERS -->

            <div class="admin-stat-card">

                <div class="admin-stat-icon">

                    <i class="fas fa-users"></i>

                </div>

                <div>

                    <span>
                        Total Users
                    </span>

                    <strong>

                        <?php
                        echo $total_users;
                        ?>

                    </strong>

                </div>

            </div>



            <!-- EVENTS -->

            <div class="admin-stat-card">

                <div class="admin-stat-icon">

                    <i class="fas fa-calendar-days"></i>

                </div>

                <div>

                    <span>
                        Total Events
                    </span>

                    <strong>

                        <?php
                        echo $total_events;
                        ?>

                    </strong>

                </div>

            </div>



            <!-- FOOD -->

            <div class="admin-stat-card">

                <div class="admin-stat-icon">

                    <i class="fas fa-utensils"></i>

                </div>

                <div>

                    <span>
                        Total Food
                    </span>

                    <strong>

                        <?php
                        echo $total_food;
                        ?>

                    </strong>

                </div>

            </div>



            <!-- BOOKINGS -->

            <div class="admin-stat-card">

                <div class="admin-stat-icon">

                    <i class="fas fa-ticket"></i>

                </div>

                <div>

                    <span>
                        Total Bookings
                    </span>

                    <strong>

                        <?php
                        echo $total_bookings;
                        ?>

                    </strong>

                </div>

            </div>



            <!-- COUPONS -->

            <div class="admin-stat-card">

                <div class="admin-stat-icon">

                    <i class="fas fa-ticket-alt"></i>

                </div>

                <div>

                    <span>
                        Total Coupons
                    </span>

                    <strong>

                        <?php
                        echo $total_coupons;
                        ?>

                    </strong>

                </div>

            </div>



            <!-- REVIEWS -->

            <div class="admin-stat-card">

                <div class="admin-stat-icon">

                    <i class="fas fa-star"></i>

                </div>

                <div>

                    <span>
                        Total Reviews
                    </span>

                    <strong>

                        <?php
                        echo $total_reviews;
                        ?>

                    </strong>

                </div>

            </div>



            <!-- PENDING -->

            <div class="admin-stat-card">

                <div class="admin-stat-icon">

                    <i class="fas fa-clock"></i>

                </div>

                <div>

                    <span>
                        Pending Bookings
                    </span>

                    <strong>

                        <?php
                        echo $pending_bookings;
                        ?>

                    </strong>

                </div>

            </div>



            <!-- CONFIRMED -->

            <div class="admin-stat-card">

                <div class="admin-stat-icon">

                    <i class="fas fa-circle-check"></i>

                </div>

                <div>

                    <span>
                        Confirmed Bookings
                    </span>

                    <strong>

                        <?php
                        echo $confirmed_bookings;
                        ?>

                    </strong>

                </div>

            </div>


        </div>



        <!-- =====================================
             QUICK ACTIONS
        ====================================== -->

        <div class="admin-section">


            <div class="admin-section-heading">

                <span>
                    MANAGEMENT
                </span>

                <h2>
                    Quick Actions
                </h2>

            </div>



            <div class="admin-actions-grid">


                <!-- EVENTS -->

                <a
                    href="events.php"
                    class="admin-action-card"
                >

                    <i class="fas fa-calendar-days"></i>

                    <div>

                        <strong>
                            Manage Events
                        </strong>

                        <small>
                            Add, edit and delete events
                        </small>

                    </div>

                    <i class="fas fa-arrow-right"></i>

                </a>



                <!-- EVENT CATEGORIES -->

                <a
                    href="event-categories.php"
                    class="admin-action-card"
                >

                    <i class="fas fa-layer-group"></i>

                    <div>

                        <strong>
                            Event Categories
                        </strong>

                        <small>
                            Manage categories and subcategories
                        </small>

                    </div>

                    <i class="fas fa-arrow-right"></i>

                </a>



                <!-- FOOD -->

                <a
                    href="foods.php"
                    class="admin-action-card"
                >

                    <i class="fas fa-utensils"></i>

                    <div>

                        <strong>
                            Manage Food
                        </strong>

                        <small>
                            Add, edit and manage food items
                        </small>

                    </div>

                    <i class="fas fa-arrow-right"></i>

                </a>



                <!-- BOOKINGS -->

                <a
                    href="bookings.php"
                    class="admin-action-card"
                >

                    <i class="fas fa-ticket"></i>

                    <div>

                        <strong>
                            Manage Bookings
                        </strong>

                        <small>
                            View and update customer bookings
                        </small>

                    </div>

                    <i class="fas fa-arrow-right"></i>

                </a>



                <!-- USERS -->

                <a
                    href="users.php"
                    class="admin-action-card"
                >

                    <i class="fas fa-users"></i>

                    <div>

                        <strong>
                            Manage Users
                        </strong>

                        <small>
                            View registered users
                        </small>

                    </div>

                    <i class="fas fa-arrow-right"></i>

                </a>



                <!-- GALLERY -->

                <a
                    href="gallery.php"
                    class="admin-action-card"
                >

                    <i class="fas fa-images"></i>

                    <div>

                        <strong>
                            Manage Gallery
                        </strong>

                        <small>
                            Add and manage gallery images
                        </small>

                    </div>

                    <i class="fas fa-arrow-right"></i>

                </a>



                <!-- CALENDAR -->

                <a
                    href="calendar.php"
                    class="admin-action-card"
                >

                    <i class="fas fa-calendar-days"></i>

                    <div>

                        <strong>
                            Event Calendar
                        </strong>

                        <small>
                            View scheduled events by date
                        </small>

                    </div>

                    <i class="fas fa-arrow-right"></i>

                </a>



                <!-- COUPONS -->

                <a
                    href="coupons.php"
                    class="admin-action-card"
                >

                    <i class="fas fa-ticket-alt"></i>

                    <div>

                        <strong>
                            Manage Coupons
                        </strong>

                        <small>
                            Add, edit and manage promo codes
                        </small>

                    </div>

                    <i class="fas fa-arrow-right"></i>

                </a>



                <!-- REVIEWS -->

                <a
                    href="reviews.php"
                    class="admin-action-card"
                >

                    <i class="fas fa-star"></i>

                    <div>

                        <strong>
                            Manage Reviews
                        </strong>

                        <small>
                            View and manage customer reviews
                        </small>

                    </div>

                    <i class="fas fa-arrow-right"></i>

                </a>



                <!-- MESSAGES -->

                <a
                    href="messages.php"
                    class="admin-action-card"
                >

                    <i class="fas fa-envelope"></i>

                    <div>

                        <strong>
                            Messages
                        </strong>

                        <small>
                            View contact messages
                        </small>

                    </div>

                    <i class="fas fa-arrow-right"></i>

                </a>



                <!-- WEBSITE -->

                <a
                    href="../index.php"
                    class="admin-action-card"
                >

                    <i class="fas fa-globe"></i>

                    <div>

                        <strong>
                            Visit Website
                        </strong>

                        <small>
                            Open Event Crafters website
                        </small>

                    </div>

                    <i class="fas fa-arrow-right"></i>

                </a>


            </div>

        </div>



        <!-- =====================================
             FOOD OVERVIEW
        ====================================== -->

        <div class="admin-summary">


            <div class="admin-section-heading">

                <span>
                    FOOD OVERVIEW
                </span>

                <h2>
                    Food Management
                </h2>

            </div>



            <div class="booking-summary-grid">


                <!-- TOTAL FOOD -->

                <div class="summary-box">

                    <i class="fas fa-utensils"></i>

                    <strong>

                        <?php
                        echo $total_food;
                        ?>

                    </strong>

                    <span>
                        Total Food
                    </span>

                </div>



                <!-- ACTIVE FOOD -->

                <div class="summary-box">

                    <i class="fas fa-circle-check"></i>

                    <strong>

                        <?php
                        echo $active_food;
                        ?>

                    </strong>

                    <span>
                        Active Food
                    </span>

                </div>



                <!-- TOTAL BOOKINGS -->

                <div class="summary-box">

                    <i class="fas fa-ticket"></i>

                    <strong>

                        <?php
                        echo $total_bookings;
                        ?>

                    </strong>

                    <span>
                        Food Bookings
                    </span>

                </div>


            </div>

        </div>



        <!-- =====================================
             BOOKING OVERVIEW
        ====================================== -->

        <div class="admin-summary">


            <div class="admin-section-heading">

                <span>
                    BOOKING OVERVIEW
                </span>

                <h2>
                    Booking Status
                </h2>

            </div>



            <div class="booking-summary-grid">


                <!-- ALL -->

                <div class="summary-box">

                    <i class="fas fa-ticket"></i>

                    <strong>

                        <?php
                        echo $total_bookings;
                        ?>

                    </strong>

                    <span>
                        All Bookings
                    </span>

                </div>



                <!-- PENDING -->

                <div class="summary-box">

                    <i class="fas fa-hourglass-half"></i>

                    <strong>

                        <?php
                        echo $pending_bookings;
                        ?>

                    </strong>

                    <span>
                        Pending
                    </span>

                </div>



                <!-- CONFIRMED -->

                <div class="summary-box">

                    <i class="fas fa-circle-check"></i>

                    <strong>

                        <?php
                        echo $confirmed_bookings;
                        ?>

                    </strong>

                    <span>
                        Confirmed
                    </span>

                </div>



                <!-- CANCELLED -->

                <div class="summary-box">

                    <i class="fas fa-circle-xmark"></i>

                    <strong>

                        <?php
                        echo $cancelled_bookings;
                        ?>

                    </strong>

                    <span>
                        Cancelled
                    </span>

                </div>


            </div>

        </div>



        <!-- =====================================
             COUPON OVERVIEW
        ====================================== -->

        <div class="admin-summary">


            <div class="admin-section-heading">

                <span>
                    PROMO OVERVIEW
                </span>

                <h2>
                    Coupon Management
                </h2>

            </div>



            <div class="booking-summary-grid">


                <!-- TOTAL -->

                <div class="summary-box">

                    <i class="fas fa-ticket-alt"></i>

                    <strong>

                        <?php
                        echo $total_coupons;
                        ?>

                    </strong>

                    <span>
                        Total Coupons
                    </span>

                </div>



                <!-- ACTIVE -->

                <div class="summary-box">

                    <i class="fas fa-circle-check"></i>

                    <strong>

                        <?php
                        echo $active_coupons;
                        ?>

                    </strong>

                    <span>
                        Active Coupons
                    </span>

                </div>



                <!-- INACTIVE -->

                <div class="summary-box">

                    <i class="fas fa-circle-xmark"></i>

                    <strong>

                        <?php
                        echo $inactive_coupons;
                        ?>

                    </strong>

                    <span>
                        Inactive Coupons
                    </span>

                </div>



                <!-- MANAGE -->

                <div class="summary-box">

                    <a
                        href="coupons.php"
                        style="text-decoration:none;"
                    >

                        <i class="fas fa-arrow-right"></i>

                        <strong>
                            Manage
                        </strong>

                        <span>
                            Coupon Settings
                        </span>

                    </a>

                </div>


            </div>

        </div>



        <!-- =====================================
             REVIEW OVERVIEW
        ====================================== -->

        <div class="admin-summary">


            <div class="admin-section-heading">

                <span>
                    REVIEW OVERVIEW
                </span>

                <h2>
                    Review Management
                </h2>

            </div>



            <div class="booking-summary-grid">


                <!-- TOTAL REVIEWS -->

                <div class="summary-box">

                    <i class="fas fa-star"></i>

                    <strong>

                        <?php
                        echo $total_reviews;
                        ?>

                    </strong>

                    <span>
                        Total Reviews
                    </span>

                </div>



                <!-- MANAGE REVIEWS -->

                <div class="summary-box">

                    <a
                        href="reviews.php"
                        style="text-decoration:none;"
                    >

                        <i class="fas fa-arrow-right"></i>

                        <strong>
                            Manage
                        </strong>

                        <span>
                            Review Settings
                        </span>

                    </a>

                </div>


            </div>

        </div>


    </div>

</section>



<?php

include "../includes/footer.php";

?>