<?php

session_start();

include "config/db.php";

/* =========================
   GET EVENT ID
========================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: events.php");
    exit();
}

$event_id = (int) $_GET['id'];


/* =========================
   GET EVENT
========================= */

$sql = "
    SELECT *
    FROM events
    WHERE id = '$event_id'
";

$result = mysqli_query($conn, $sql);


if (!$result) {
    die(
        "Database Error: " .
        htmlspecialchars(mysqli_error($conn))
    );
}


if (mysqli_num_rows($result) == 0) {
    header("Location: events.php");
    exit();
}


$event = mysqli_fetch_assoc($result);


/* =========================
   GET REVIEWS
========================= */

$review_sql = "
    SELECT
        AVG(rating) AS average_rating,
        COUNT(*) AS review_count
    FROM reviews
    WHERE event_id = '$event_id'
    AND status = 1
";

$review_result = mysqli_query($conn, $review_sql);

$average_rating = 0;
$review_count = 0;

if ($review_result) {

    $review_data = mysqli_fetch_assoc($review_result);

    if ($review_data['average_rating'] !== null) {

        $average_rating = round(
            (float) $review_data['average_rating'],
            1
        );
    }

    $review_count = (int) $review_data['review_count'];
}


include "includes/header.php";
include "includes/navbar.php";

?>

<link
    rel="stylesheet"
    href="assets/css/event-details.css"
>


<!-- =========================
     BACK BUTTON
========================= -->

<section class="event-details-section">

    <a
        href="events.php"
        class="back-btn"
    >
        <i class="fas fa-arrow-left"></i>
        Back To Events
    </a>


    <!-- =========================
         EVENT DETAILS
    ========================= -->

    <div class="event-details-card">


        <!-- =========================
             IMAGE
        ========================= -->

        <div class="event-details-image">

            <img
                src="uploads/events/<?php
                    echo htmlspecialchars(
                        $event['image']
                    );
                ?>"
                alt="<?php
                    echo htmlspecialchars(
                        $event['event_name']
                    );
                ?>"
            >

        </div>


        <!-- =========================
             CONTENT
        ========================= -->

        <div class="event-details-content">


            <!-- CATEGORY -->

            <span class="event-details-category">

                <?php
                echo htmlspecialchars(
                    $event['category']
                );
                ?>

            </span>


            <!-- EVENT NAME -->

            <h1>

                <?php
                echo htmlspecialchars(
                    $event['event_name']
                );
                ?>

            </h1>


            <!-- =========================
                 RATING
            ========================= -->

            <div class="event-details-rating">

                <div class="rating-stars">

                    <?php

                    $rounded_rating =
                        round($average_rating);

                    for (
                        $i = 1;
                        $i <= 5;
                        $i++
                    ) {

                        if (
                            $i <=
                            $rounded_rating
                        ) {

                    ?>

                        <i
                            class="fas fa-star filled-star"
                        ></i>

                    <?php

                        } else {

                    ?>

                        <i
                            class="far fa-star empty-star"
                        ></i>

                    <?php

                        }

                    }

                    ?>

                </div>


                <span>

                    <?php

                    if ($review_count > 0) {

                        echo $average_rating .
                             " (" .
                             $review_count .
                             " Review";

                        echo $review_count > 1
                            ? "s"
                            : "";

                        echo ")";

                    } else {

                        echo "No reviews yet";

                    }

                    ?>

                </span>

            </div>


            <!-- =========================
                 EVENT INFO
            ========================= -->

            <div class="event-details-info">


                <!-- DATE -->

                <div class="event-info-item">

                    <i class="fas fa-calendar-alt"></i>

                    <div>

                        <span>Date</span>

                        <strong>

                            <?php

                            if (
                                !empty(
                                    $event['event_date']
                                )
                            ) {

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $event['event_date']
                                    )
                                );

                            } else {

                                echo "N/A";

                            }

                            ?>

                        </strong>

                    </div>

                </div>


                <!-- TIME -->

                <div class="event-info-item">

                    <i class="fas fa-clock"></i>

                    <div>

                        <span>Time</span>

                        <strong>

                            <?php

                            if (
                                !empty(
                                    $event['event_time']
                                )
                            ) {

                                echo date(
                                    "h:i A",
                                    strtotime(
                                        $event['event_time']
                                    )
                                );

                            } else {

                                echo "N/A";

                            }

                            ?>

                        </strong>

                    </div>

                </div>


                <!-- VENUE -->

                <div class="event-info-item">

                    <i class="fas fa-map-marker-alt"></i>

                    <div>

                        <span>Venue</span>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $event['venue']
                            );
                            ?>

                        </strong>

                    </div>

                </div>

            </div>


            <!-- =========================
                 DESCRIPTION
            ========================= -->

            <?php

            if (
                isset($event['description']) &&
                !empty($event['description'])
            ) {

            ?>

                <div class="event-description">

                    <h3>
                        About This Event
                    </h3>

                    <p>

                        <?php

                        echo nl2br(
                            htmlspecialchars(
                                $event['description']
                            )
                        );

                        ?>

                    </p>

                </div>

            <?php

            }

            ?>


            <!-- =========================
                 PRICE + BOOK
            ========================= -->

            <div class="event-details-bottom">


                <div class="event-details-price">

                    <span>
                        Starting From
                    </span>

                    <strong>

                        ₹<?php

                        echo number_format(
                            (float)
                            $event['price']
                        );

                        ?>

                    </strong>

                </div>


                <?php

                if (
                    isset(
                        $_SESSION['user_id']
                    )
                ) {

                ?>

                    <a
                        href="user/book.php?id=<?php
                            echo $event_id;
                        ?>"
                        class="event-details-book-btn"
                    >

                        <i class="fas fa-calendar-check"></i>

                        Book Now

                    </a>

                <?php

                } else {

                ?>

                    <a
                        href="login.php"
                        class="event-details-book-btn"
                    >

                        <i class="fas fa-sign-in-alt"></i>

                        Login To Book

                    </a>

                <?php

                }

                ?>

            </div>


            <!-- =========================
                 REVIEWS LINK
            ========================= -->

            <a
                href="event-reviews.php?event_id=<?php
                    echo $event_id;
                ?>"
                class="event-details-reviews-link"
            >

                <i class="fas fa-comment-dots"></i>

                View All Reviews

            </a>


        </div>

    </div>

</section>


<?php

include "includes/footer.php";

?>