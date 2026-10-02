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
   GET WISHLIST EVENTS
========================================= */

$sql = "
    SELECT

        wishlist.id AS wishlist_id,

        events.id AS event_id,
        events.event_name,
        events.category,
        events.event_date,
        events.event_time,
        events.venue,
        events.price,
        events.image

    FROM wishlist

    INNER JOIN events
        ON wishlist.event_id = events.id

    WHERE wishlist.user_id = '$user_id'

    ORDER BY wishlist.id DESC
";


$result = mysqli_query($conn, $sql);


/* =========================================
   DATABASE ERROR
========================================= */

if (!$result) {

    die(
        "Database Error: " .
        htmlspecialchars(mysqli_error($conn))
    );

}


/* =========================================
   HEADER
========================================= */

include "../includes/header.php";
include "../includes/navbar.php";

?>

<link
    rel="stylesheet"
    href="../assets/css/wishlist.css"
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
     WISHLIST SECTION
========================================= -->

<section class="wishlist-section">

    <div class="wishlist-container">


        <!-- =====================================
             HEADING
        ====================================== -->

        <div class="wishlist-heading">

            <span>
                USER ACCOUNT
            </span>

            <h1>
                My Wishlist
            </h1>

            <p>
                Save your favourite events and book them anytime.
            </p>

        </div>


        <?php if (mysqli_num_rows($result) > 0) { ?>


        <!-- =====================================
             WISHLIST HEADER
        ====================================== -->

        <div class="wishlist-summary">

            <div>

                <i class="fas fa-heart"></i>

                <span>

                    <?php
                    echo mysqli_num_rows($result);
                    ?>

                    Saved Event<?php
                    echo (
                        mysqli_num_rows($result) != 1
                    )
                    ? "s"
                    : "";
                    ?>

                </span>

            </div>

            <a
                href="../events.php"
                class="wishlist-explore-btn"
            >

                <i class="fas fa-calendar-days"></i>

                Explore Events

            </a>

        </div>


        <!-- =====================================
             WISHLIST GRID
        ====================================== -->

        <div class="wishlist-grid">


        <?php while ($row = mysqli_fetch_assoc($result)) { ?>


            <!-- =================================
                 WISHLIST CARD
            ================================== -->

            <div class="wishlist-card">


                <!-- =================================
                     IMAGE
                ================================== -->

                <div class="wishlist-image">


                    <?php if (!empty($row['image'])) { ?>

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

                    <?php } else { ?>

                        <div class="wishlist-no-image">

                            <i class="fas fa-calendar-days"></i>

                        </div>

                    <?php } ?>


                    <!-- =================================
                         SAVED BADGE
                    ================================== -->

                    <span class="wishlist-saved-badge">

                        <i class="fas fa-heart"></i>

                        Saved

                    </span>


                    <!-- =================================
                         REMOVE BUTTON
                    ================================== -->

                    <a
                        href="../wishlist_action.php?event_id=<?php
                            echo (int)$row['event_id'];
                        ?>"
                        class="remove-wishlist"
                        title="Remove from Wishlist"
                        onclick="
                            return confirm(
                                'Remove this event from your wishlist?'
                            );
                        "
                    >

                        <i class="fas fa-heart"></i>

                    </a>


                </div>


                <!-- =================================
                     CARD BODY
                ================================== -->

                <div class="wishlist-body">


                    <!-- CATEGORY -->

                    <span class="wishlist-category">

                        <?php
                        echo htmlspecialchars(
                            $row['category']
                        );
                        ?>

                    </span>


                    <!-- EVENT NAME -->

                    <h3>

                        <?php
                        echo htmlspecialchars(
                            $row['event_name']
                        );
                        ?>

                    </h3>


                    <!-- =================================
                         EVENT DATE
                    ================================== -->

                    <?php
                    $event_date = "";

                    if (
                        !empty($row['event_date']) &&
                        strtotime($row['event_date'])
                    ) {

                        $event_date = date(
                            "d M Y",
                            strtotime($row['event_date'])
                        );

                    } else {

                        $event_date = "Date not available";

                    }
                    ?>


                    <p class="wishlist-info">

                        <i class="fas fa-calendar-alt"></i>

                        <?php
                        echo htmlspecialchars($event_date);
                        ?>

                    </p>


                    <!-- =================================
                         EVENT TIME
                    ================================== -->

                    <?php
                    $event_time = "";

                    if (
                        !empty($row['event_time']) &&
                        strtotime($row['event_time'])
                    ) {

                        $event_time = date(
                            "h:i A",
                            strtotime($row['event_time'])
                        );

                    } else {

                        $event_time = "Time not available";

                    }
                    ?>


                    <p class="wishlist-info">

                        <i class="fas fa-clock"></i>

                        <?php
                        echo htmlspecialchars($event_time);
                        ?>

                    </p>


                    <!-- =================================
                         VENUE
                    ================================== -->

                    <p class="wishlist-info">

                        <i class="fas fa-location-dot"></i>

                        <?php
                        echo htmlspecialchars(
                            $row['venue']
                        );
                        ?>

                    </p>


                    <!-- =================================
                         BOTTOM
                    ================================== -->

                    <div class="wishlist-bottom">


                        <!-- PRICE -->

                        <div class="wishlist-price">

                            <small>
                                Starting from
                            </small>

                            <strong>

                                ₹<?php
                                echo number_format(
                                    (float)$row['price']
                                );
                                ?>

                            </strong>

                        </div>


                        <!-- ACTIONS -->

                        <div class="wishlist-actions">


                            <!-- VIEW EVENT -->

                            <a
                                href="../event-details.php?id=<?php
                                    echo (int)$row['event_id'];
                                ?>"
                                class="wishlist-view-btn"
                                title="View Event"
                            >

                                <i class="fas fa-eye"></i>

                            </a>


                            <!-- BOOK NOW -->

                            <a
                                href="book.php?id=<?php
                                    echo (int)$row['event_id'];
                                ?>"
                                class="wishlist-book-btn"
                            >

                                <i class="fas fa-ticket"></i>

                                Book Now

                            </a>


                        </div>


                    </div>


                </div>


            </div>


        <?php } ?>


        </div>


        <?php } else { ?>


        <!-- =====================================
             EMPTY WISHLIST
        ====================================== -->

        <div class="empty-wishlist">


            <div class="empty-wishlist-icon">

                <i class="fas fa-heart"></i>

            </div>


            <h2>
                Your Wishlist is Empty
            </h2>


            <p>
                You haven't saved any events yet.
                Explore our events and add your favourites here.
            </p>


            <a
                href="../events.php"
                class="browse-events-btn"
            >

                <i class="fas fa-calendar-days"></i>

                Browse Events

                <i class="fas fa-arrow-right"></i>

            </a>


        </div>


        <?php } ?>


    </div>

</section>


<?php

include "../includes/footer.php";

?>