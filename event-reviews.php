<?php

session_start();

include "config/db.php";


/* =========================================
   EVENT ID CHECK
========================================= */

if (
    !isset($_GET['event_id']) ||
    !is_numeric($_GET['event_id'])
) {

    header("Location: events.php");
    exit;

}

$event_id = (int) $_GET['event_id'];


/* =========================================
   GET EVENT
========================================= */

$event_sql = "
    SELECT
        id,
        event_name,
        category,
        image
    FROM events
    WHERE id = '$event_id'
    LIMIT 1
";

$event_result = mysqli_query(
    $conn,
    $event_sql
);


if (
    !$event_result ||
    mysqli_num_rows($event_result) == 0
) {

    header("Location: events.php");
    exit;

}


$event = mysqli_fetch_assoc(
    $event_result
);


/* =========================================
   GET RATING SUMMARY
========================================= */

$rating_sql = "
    SELECT
        AVG(rating) AS average_rating,
        COUNT(*) AS total_reviews
    FROM reviews
    WHERE event_id = '$event_id'
    AND status = 1
";

$rating_result = mysqli_query(
    $conn,
    $rating_sql
);


$average_rating = 0;
$total_reviews = 0;


if ($rating_result) {

    $rating_data =
        mysqli_fetch_assoc(
            $rating_result
        );


    $average_rating =
        $rating_data['average_rating'] !== null
        ? round(
            (float) $rating_data['average_rating'],
            1
        )
        : 0;


    $total_reviews =
        (int) $rating_data['total_reviews'];

}


/* =========================================
   GET REVIEWS
========================================= */

$reviews_sql = "
    SELECT

        reviews.id,
        reviews.rating,
        reviews.review,
        reviews.created_at,

        users.full_name

    FROM reviews

    INNER JOIN users
        ON reviews.user_id = users.id

    WHERE reviews.event_id = '$event_id'

    AND reviews.status = 1

    ORDER BY reviews.id DESC
";


$reviews_result = mysqli_query(
    $conn,
    $reviews_sql
);


include "includes/header.php";
include "includes/navbar.php";

?>

<link
    rel="stylesheet"
    href="assets/css/event-reviews.css"
>


<!-- =========================================
     BACK
========================================= -->

<a
    href="events.php"
    class="back-btn"
>

    <i class="fas fa-arrow-left"></i>

    Back to Events

</a>


<!-- =========================================
     REVIEWS PAGE
========================================= -->

<section class="event-reviews-page">

    <div class="event-reviews-container">


        <!-- =================================
             EVENT HEADER
        ================================== -->

        <div class="reviews-event-header">


            <div class="reviews-event-image">

                <?php

                if (!empty($event['image'])) {

                ?>

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

                <?php

                } else {

                ?>

                    <div class="reviews-no-image">

                        <i class="fas fa-calendar-days"></i>

                    </div>

                <?php

                }

                ?>

            </div>


            <div class="reviews-event-info">

                <span>
                    <?php
                    echo htmlspecialchars(
                        $event['category']
                    );
                    ?>
                </span>

                <h1>
                    <?php
                    echo htmlspecialchars(
                        $event['event_name']
                    );
                    ?>
                </h1>

                <p>
                    Customer Reviews & Ratings
                </p>

            </div>

        </div>


        <!-- =================================
             RATING SUMMARY
        ================================== -->

        <div class="rating-summary">


            <div class="rating-score">

                <strong>

                    <?php
                    echo $average_rating > 0
                        ? $average_rating
                        : "—";
                    ?>

                </strong>

                <div class="summary-stars">

                    <?php

                    $rounded =
                        round(
                            $average_rating
                        );


                    for (
                        $i = 1;
                        $i <= 5;
                        $i++
                    ) {

                        if (
                            $i <= $rounded
                        ) {

                            echo '<i class="fas fa-star"></i>';

                        } else {

                            echo '<i class="far fa-star"></i>';

                        }

                    }

                    ?>

                </div>

                <small>

                    <?php
                    echo $total_reviews;
                    ?>

                    Review<?php
                    echo $total_reviews != 1
                        ? "s"
                        : "";
                    ?>

                </small>

            </div>


            <div class="rating-summary-text">

                <h2>
                    Customer Reviews
                </h2>

                <p>
                    See what people are saying about this event.
                </p>

            </div>

        </div>


        <!-- =================================
             REVIEWS
        ================================== -->

        <div class="reviews-list">


            <?php

            if (
                $reviews_result &&
                mysqli_num_rows($reviews_result) > 0
            ) {

                while (
                    $review =
                    mysqli_fetch_assoc(
                        $reviews_result
                    )
                ) {

            ?>


                <!-- REVIEW CARD -->

                <div class="customer-review-card">


                    <div class="review-top">


                        <div class="review-user">


                            <div class="review-avatar">

                                <?php

                                echo strtoupper(
                                    substr(
                                        $review['full_name'],
                                        0,
                                        1
                                    )
                                );

                                ?>

                            </div>


                            <div>

                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $review['full_name']
                                    );

                                    ?>

                                </strong>


                                <small>

                                    <?php

                                    if (
                                        !empty(
                                            $review['created_at']
                                        )
                                    ) {

                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $review['created_at']
                                            )
                                        );

                                    }

                                    ?>

                                </small>

                            </div>

                        </div>


                        <!-- STARS -->

                        <div class="customer-stars">

                            <?php

                            $user_rating =
                                (int)
                                $review['rating'];


                            for (
                                $i = 1;
                                $i <= 5;
                                $i++
                            ) {

                                if (
                                    $i <=
                                    $user_rating
                                ) {

                                    echo '<i class="fas fa-star"></i>';

                                } else {

                                    echo '<i class="far fa-star"></i>';

                                }

                            }

                            ?>

                        </div>

                    </div>


                    <!-- REVIEW TEXT -->

                    <div class="review-text">

                        <?php

                        echo nl2br(
                            htmlspecialchars(
                                $review['review']
                            )
                        );

                        ?>

                    </div>


                </div>


            <?php

                }

            } else {

            ?>


                <!-- NO REVIEWS -->

                <div class="no-reviews">


                    <div class="no-reviews-icon">

                        <i class="far fa-star"></i>

                    </div>


                    <h2>
                        No Reviews Yet
                    </h2>


                    <p>
                        Be the first to share your experience
                        about this event.
                    </p>


                    <?php

                    if (
                        isset(
                            $_SESSION['user_id']
                        )
                    ) {

                    ?>

                        <a
                            href="events.php"
                            class="write-review-btn"
                        >

                            <i class="fas fa-star"></i>

                            Book & Review Event

                        </a>

                    <?php

                    }

                    ?>


                </div>


            <?php

            }

            ?>


        </div>


    </div>

</section>


<?php

include "includes/footer.php";

?>