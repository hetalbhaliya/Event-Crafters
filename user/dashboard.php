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
   USER DATA
========================================= */

$user_id = (int)$_SESSION['user_id'];

$user_name = isset($_SESSION['user_name'])
    ? $_SESSION['user_name']
    : "User";


/* =========================================
   TOTAL BOOKINGS
========================================= */

$total_bookings = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE user_id = '$user_id'
";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $total_bookings = (int)($row['total'] ?? 0);
}


/* =========================================
   PENDING BOOKINGS
========================================= */

$pending_bookings = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE user_id = '$user_id'
    AND LOWER(TRIM(status)) = 'pending'
";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $pending_bookings = (int)($row['total'] ?? 0);
}


/* =========================================
   CONFIRMED BOOKINGS
========================================= */

$confirmed_bookings = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE user_id = '$user_id'
    AND LOWER(TRIM(status)) = 'confirmed'
";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $confirmed_bookings = (int)($row['total'] ?? 0);
}


/* =========================================
   UNREAD NOTIFICATIONS
========================================= */

$unread_notifications = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = '$user_id'
    AND is_read = 0
";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $unread_notifications = (int)($row['total'] ?? 0);
}


/* =========================================
   HEADER
========================================= */

include "../includes/header.php";
include "../includes/navbar.php";

?>

<link
    rel="stylesheet"
    href="../assets/css/dashboard.css"
>


<!-- =========================================
     BACK BUTTON
========================================= -->

<a
    href="javascript:history.back()"
    class="back-btn"
>

    <i class="fas fa-arrow-left"></i>

    Back

</a>


<!-- =========================================
     DASHBOARD
========================================= -->

<section class="dashboard-section">

    <div class="dashboard-container">


        <!-- =====================================
             WELCOME
        ====================================== -->

        <div class="dashboard-welcome">

            <div>

                <span>
                    USER DASHBOARD
                </span>

                <h1>

                    Welcome,
                    <?php
                    echo htmlspecialchars($user_name);
                    ?>!

                </h1>

                <p>
                    Manage your events, bookings and account from here.
                </p>

            </div>


            <div class="welcome-icon">

                <i class="fas fa-user"></i>

            </div>

        </div>


        <!-- =====================================
             STATISTICS
        ====================================== -->

        <div class="dashboard-stats">


            <!-- =================================
                 TOTAL BOOKINGS
            ================================== -->

            <a
                href="my_bookings.php"
                class="dashboard-stat-card"
            >

                <div class="stat-icon">

                    <i class="fas fa-calendar-check"></i>

                </div>

                <div class="stat-content">

                    <span>
                        Total Bookings
                    </span>

                    <strong>
                        <?php
                        echo $total_bookings;
                        ?>
                    </strong>

                </div>

            </a>


            <!-- =================================
                 PENDING
            ================================== -->

            <a
                href="my_bookings.php?status=Pending"
                class="dashboard-stat-card"
            >

                <div class="stat-icon">

                    <i class="fas fa-clock"></i>

                </div>

                <div class="stat-content">

                    <span>
                        Pending
                    </span>

                    <strong>
                        <?php
                        echo $pending_bookings;
                        ?>
                    </strong>

                </div>

            </a>


            <!-- =================================
                 CONFIRMED
            ================================== -->

            <a
                href="my_bookings.php?status=Confirmed"
                class="dashboard-stat-card"
            >

                <div class="stat-icon">

                    <i class="fas fa-circle-check"></i>

                </div>

                <div class="stat-content">

                    <span>
                        Confirmed
                    </span>

                    <strong>
                        <?php
                        echo $confirmed_bookings;
                        ?>
                    </strong>

                </div>

            </a>


            <!-- =================================
                 NOTIFICATIONS
            ================================== -->

            <a
                href="notifications.php"
                class="dashboard-stat-card notification-stat"
            >

                <div class="stat-icon">

                    <i class="fas fa-bell"></i>

                </div>

                <div class="stat-content">

                    <span>
                        Unread Notifications
                    </span>

                    <strong>
                        <?php
                        echo $unread_notifications;
                        ?>
                    </strong>

                </div>

            </a>


        </div>


        <!-- =====================================
             DASHBOARD CARDS
        ====================================== -->

        <div class="dashboard-grid">


            <!-- =================================
                 MY BOOKINGS
            ================================== -->

            <a
                href="my_bookings.php"
                class="dashboard-card"
            >

                <div class="dashboard-card-icon">

                    <i class="fas fa-calendar-check"></i>

                </div>

                <h3>
                    My Bookings
                </h3>

                <p>
                    View and manage your event bookings.
                </p>

                <span class="dashboard-card-link">

                    View Bookings

                    <i class="fas fa-arrow-right"></i>

                </span>

            </a>


            <!-- =================================
                 WISHLIST
            ================================== -->

            <a
                href="wishlist.php"
                class="dashboard-card"
            >

                <div class="dashboard-card-icon">

                    <i class="fas fa-heart"></i>

                </div>

                <h3>
                    Wishlist
                </h3>

                <p>
                    Save your favourite events for later.
                </p>

                <span class="dashboard-card-link">

                    View Wishlist

                    <i class="fas fa-arrow-right"></i>

                </span>

            </a>


            <!-- =================================
                 NOTIFICATIONS
            ================================== -->

            <a
                href="notifications.php"
                class="dashboard-card"
            >

                <div class="dashboard-card-icon">

                    <i class="fas fa-bell"></i>

                </div>

                <h3>
                    Notifications
                </h3>

                <p>
                    Check your latest booking updates.
                </p>


                <?php if ($unread_notifications > 0) { ?>

                    <span class="notification-badge">

                        <?php
                        echo $unread_notifications;
                        ?>

                        New

                    </span>

                <?php } ?>


                <span class="dashboard-card-link">

                    View Notifications

                    <i class="fas fa-arrow-right"></i>

                </span>

            </a>


            <!-- =================================
                 PROFILE
            ================================== -->

            <a
                href="profile.php"
                class="dashboard-card"
            >

                <div class="dashboard-card-icon">

                    <i class="fas fa-user-circle"></i>

                </div>

                <h3>
                    My Profile
                </h3>

                <p>
                    View and manage your account details.
                </p>

                <span class="dashboard-card-link">

                    Manage Profile

                    <i class="fas fa-arrow-right"></i>

                </span>

            </a>


        </div>


        <!-- =====================================
             EXPLORE EVENTS
        ====================================== -->

        <div class="dashboard-explore">

            <div>

                <span class="explore-label">
                    EVENT CRAFTERS
                </span>

                <h2>
                    Find Your Next Event
                </h2>

                <p>
                    Explore exciting events and create unforgettable memories.
                </p>

            </div>


            <a
                href="../events.php"
                class="explore-btn"
            >

                Explore Events

                <i class="fas fa-arrow-right"></i>

            </a>

        </div>


    </div>

</section>


<?php

include "../includes/footer.php";

?>