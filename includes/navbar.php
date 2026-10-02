<?php

/* =========================================
   NAVBAR
========================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================
   DATABASE CONNECTION
========================================= */

if (!isset($conn)) {

    include_once __DIR__ . "/../config/db.php";

}


/* =========================================
   USER SESSION
========================================= */

$user_id = 0;

if (isset($_SESSION['uid'])) {

    $user_id = (int) $_SESSION['uid'];

}
elseif (isset($_SESSION['user_id'])) {

    $user_id = (int) $_SESSION['user_id'];

}


/* =========================================
   USER COUNTS
========================================= */

$wishlist_count = 0;
$unread_count = 0;


if ($user_id > 0 && isset($conn)) {


    /* =========================================
       WISHLIST COUNT
    ========================================= */

    $wishlist_query = mysqli_query(
        $conn,
        "
        SELECT COUNT(*) AS total
        FROM wishlist
        WHERE user_id = '$user_id'
        "
    );


    if ($wishlist_query) {

        $wishlist_data = mysqli_fetch_assoc(
            $wishlist_query
        );

        $wishlist_count = (int) (
            $wishlist_data['total'] ?? 0
        );

    }


    /* =========================================
       NOTIFICATION COUNT
    ========================================= */

    $notification_query = mysqli_query(
        $conn,
        "
        SELECT COUNT(*) AS total
        FROM notifications
        WHERE user_id = '$user_id'
        AND is_read = 0
        "
    );


    if ($notification_query) {

        $notification_data = mysqli_fetch_assoc(
            $notification_query
        );

        $unread_count = (int) (
            $notification_data['total'] ?? 0
        );

    }

}

?>


<!-- =========================================
     NAVBAR
========================================= -->

<nav class="navbar">

    <div class="nav-container">


        <!-- =====================================
             LOGO
        ====================================== -->

        <a
            href="/event-crafters-new/index.php"
            class="logo"
        >

            <img
                src="/event-crafters-new/assets/images/logo.png"
                alt="Event Crafters"
            >

        </a>


        <!-- =====================================
             MAIN NAV LINKS
        ====================================== -->

        <div class="nav-links">


            <!-- HOME -->

            <a
                href="/event-crafters-new/index.php"
            >

                <i class="fas fa-house"></i>

                <span>Home</span>

            </a>


            <!-- EVENTS -->

            <a
                href="/event-crafters-new/events.php"
            >

                <i class="fas fa-calendar-days"></i>

                <span>Events</span>

            </a>


            <!-- GALLERY -->

            <a
                href="/event-crafters-new/gallery.php"
            >

                <i class="fas fa-images"></i>

                <span>Gallery</span>

            </a>


            <!-- ABOUT -->

            <a
                href="/event-crafters-new/about.php"
            >

                <i class="fas fa-circle-info"></i>

                <span>About</span>

            </a>


            <!-- CONTACT -->

            <a
                href="/event-crafters-new/contact.php"
            >

                <i class="fas fa-envelope"></i>

                <span>Contact</span>

            </a>


            <!-- CALENDAR -->

            <a
                href="/event-crafters-new/calendar.php"
            >

                <i class="fas fa-calendar-check"></i>

                <span>Calendar</span>

            </a>


            <!-- COUPONS -->

            <a
                href="/event-crafters-new/coupons.php"
            >

                <i class="fas fa-ticket"></i>

                <span>Coupons</span>

            </a>


            <?php if ($user_id > 0) { ?>


                <!-- DASHBOARD -->

                <a
                    href="/event-crafters-new/user/dashboard.php"
                >

                    <i class="fas fa-chart-line"></i>

                    <span>Dashboard</span>

                </a>


                <!-- MY BOOKINGS -->

                <a
                    href="/event-crafters-new/user/my_bookings.php"
                >

                    <i class="fas fa-calendar-check"></i>

                    <span>My Bookings</span>

                </a>


            <?php } ?>

        </div>


        <!-- =====================================
             RIGHT SIDE
        ====================================== -->

        <div class="nav-buttons">


            <?php if ($user_id > 0) { ?>


                <!-- =================================
                     WISHLIST
                ================================== -->

                <a
                    href="/event-crafters-new/user/wishlist.php"
                    class="nav-icon"
                    title="Wishlist"
                >

                    <i class="fas fa-heart"></i>


                    <?php if ($wishlist_count > 0) { ?>

                        <span class="nav-count">

                            <?php
                            echo $wishlist_count;
                            ?>

                        </span>

                    <?php } ?>

                </a>


                <!-- =================================
                     NOTIFICATIONS
                ================================== -->

                <a
                    href="/event-crafters-new/user/notifications.php"
                    class="nav-icon"
                    title="Notifications"
                >

                    <i class="fas fa-bell"></i>


                    <?php if ($unread_count > 0) { ?>

                        <span class="nav-count">

                            <?php
                            echo $unread_count;
                            ?>

                        </span>

                    <?php } ?>

                </a>


                <!-- =================================
                     PROFILE
                ================================== -->

                <a
                    href="/event-crafters-new/user/profile.php"
                    class="nav-user"
                    title="My Profile"
                >

                    <i class="fas fa-user"></i>

                </a>


                <!-- =================================
                     LOGOUT
                ================================== -->

                <a
                    href="/event-crafters-new/logout.php"
                    class="logout-btn"
                    title="Logout"
                >

                    <i class="fas fa-right-from-bracket"></i>

                    <span>Logout</span>

                </a>


            <?php } else { ?>


                <!-- =================================
                     LOGIN
                ================================== -->

                <a
                    href="/event-crafters-new/login.php"
                    class="login-btn"
                >

                    <i class="fas fa-right-to-bracket"></i>

                    <span>Login</span>

                </a>


                <!-- =================================
                     REGISTER
                ================================== -->

                <a
                    href="/event-crafters-new/register.php"
                    class="register-btn"
                >

                    <i class="fas fa-user-plus"></i>

                    <span>Register</span>

                </a>


            <?php } ?>


        </div>

    </div>

</nav>


<!-- =========================================
     JAVASCRIPT
========================================= -->

<script
    src="/event-crafters-new/assets/js/script.js">
</script>