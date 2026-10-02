<?php

session_start();

include "config/db.php";

include "includes/header.php";
include "includes/navbar.php";


/* =====================================================
   GET SEARCH
===================================================== */

$search_value = "";

if (isset($_GET['search'])) {

    $search_value = trim($_GET['search']);

}


/* =====================================================
   GET CATEGORY
===================================================== */

$selected_category = "";

if (isset($_GET['category'])) {

    $selected_category = trim($_GET['category']);

}


/* =====================================================
   ESCAPE VALUES
===================================================== */

$search_safe = mysqli_real_escape_string(
    $conn,
    $search_value
);

$category_safe = mysqli_real_escape_string(
    $conn,
    $selected_category
);


/* =====================================================
   GET EVENT CATEGORIES
===================================================== */

$category_sql = "
    SELECT DISTINCT category
    FROM events
    WHERE category IS NOT NULL
    AND category != ''
    ORDER BY category ASC
";

$category_result = mysqli_query(
    $conn,
    $category_sql
);

?>


<!-- =====================================================
     EVENTS CSS
===================================================== -->

<link
    rel="stylesheet"
    href="assets/css/events.css"
>


<!-- =====================================================
     BACK BUTTON
===================================================== -->

<a
    href="javascript:history.back()"
    class="back-btn"
>

    <i class="fas fa-arrow-left"></i>

    Back

</a>


<!-- =====================================================
     EVENTS HEADER
===================================================== -->

<section class="events-header">

    <div class="section-heading">

        <span class="events-eyebrow">
            EVENT CRAFTERS
        </span>

        <h1>
            OUR EVENTS
        </h1>

        <p>
            Discover exciting events and create unforgettable memories.
        </p>

    </div>

</section>


<!-- =====================================================
     SEARCH + CATEGORY FILTER
===================================================== -->

<section class="event-filter-section">


    <!-- =================================================
         SEARCH
    ================================================== -->

    <form
        method="GET"
        action="events.php"
        class="event-search"
    >

        <input
            type="text"
            name="search"
            placeholder="Search events, category or venue..."
            value="<?php
                echo htmlspecialchars(
                    $search_value
                );
            ?>"
        >


        <!-- KEEP CATEGORY WHEN SEARCHING -->

        <?php if ($selected_category !== "") { ?>

            <input
                type="hidden"
                name="category"
                value="<?php
                    echo htmlspecialchars(
                        $selected_category
                    );
                ?>"
            >

        <?php } ?>


        <button type="submit">

            <i class="fas fa-search"></i>

            Search

        </button>

    </form>


    <!-- =================================================
         CATEGORY FILTER
    ================================================== -->

    <div class="event-category-filter">


        <div class="category-filter-heading">

            <span>
                <i class="fas fa-layer-group"></i>
                Browse By Category
            </span>

        </div>


        <div class="category-buttons">


            <!-- =========================================
                 ALL EVENTS
            ========================================== -->

            <a
                href="events.php<?php

                    if ($search_value !== "") {

                        echo "?search=" .
                            urlencode(
                                $search_value
                            );

                    }

                ?>"
                class="
                    category-btn
                    <?php
                    echo
                        $selected_category === ""
                        ? "active"
                        : "";
                    ?>
                "
            >

                <i class="fas fa-border-all"></i>

                All Events

            </a>


            <!-- =========================================
                 DATABASE CATEGORIES
            ========================================== -->

            <?php

            if (
                $category_result &&
                mysqli_num_rows(
                    $category_result
                ) > 0
            ) {

                while (
                    $category_row =
                    mysqli_fetch_assoc(
                        $category_result
                    )
                ) {

                    $category_name =
                        trim(
                            $category_row['category']
                        );

                    if ($category_name === "") {
                        continue;
                    }


                    /*
                     * Category URL
                     */

                    $category_url =
                        "events.php?category=" .
                        urlencode(
                            $category_name
                        );


                    /*
                     * Keep search also
                     */

                    if ($search_value !== "") {

                        $category_url .=
                            "&search=" .
                            urlencode(
                                $search_value
                            );

                    }


                    /*
                     * Category icon
                     */

                    $category_lower =
                        strtolower(
                            $category_name
                        );


                    $category_icon =
                        "fa-calendar-days";


                    if (
                        strpos(
                            $category_lower,
                            "birthday"
                        ) !== false
                    ) {

                        $category_icon =
                            "fa-cake-candles";

                    }

                    elseif (
                        strpos(
                            $category_lower,
                            "wedding"
                        ) !== false
                    ) {

                        $category_icon =
                            "fa-ring";

                    }

                    elseif (
                        strpos(
                            $category_lower,
                            "anniversary"
                        ) !== false
                    ) {

                        $category_icon =
                            "fa-heart";

                    }

                    elseif (
                        strpos(
                            $category_lower,
                            "baby"
                        ) !== false
                    ) {

                        $category_icon =
                            "fa-baby";

                    }

                    elseif (
                        strpos(
                            $category_lower,
                            "corporate"
                        ) !== false
                    ) {

                        $category_icon =
                            "fa-building";

                    }

                    elseif (
                        strpos(
                            $category_lower,
                            "party"
                        ) !== false
                    ) {

                        $category_icon =
                            "fa-champagne-glasses";

                    }

                    elseif (
                        strpos(
                            $category_lower,
                            "festival"
                        ) !== false
                    ) {

                        $category_icon =
                            "fa-star";

                    }

            ?>


                    <a
                        href="<?php
                            echo htmlspecialchars(
                                $category_url
                            );
                        ?>"
                        class="
                            category-btn
                            <?php
                            echo
                                strtolower(
                                    $selected_category
                                ) ===
                                strtolower(
                                    $category_name
                                )
                                ? "active"
                                : "";
                            ?>
                        "
                    >

                        <i
                            class="fas <?php
                                echo $category_icon;
                            ?>"
                        ></i>

                        <?php
                        echo htmlspecialchars(
                            $category_name
                        );
                        ?>

                    </a>


            <?php

                }

            }

            ?>


        </div>

    </div>


</section>


<!-- =====================================================
     EVENTS SECTION
===================================================== -->

<section class="events-section">

    <div class="events-container">


        <?php

        /* =================================================
           BUILD WHERE CONDITION
        ================================================= */

        $conditions = array();


        /*
         * SEARCH
         */

        if ($search_value !== "") {

            $conditions[] = "
                (
                    event_name LIKE '%$search_safe%'
                    OR category LIKE '%$search_safe%'
                    OR venue LIKE '%$search_safe%'
                )
            ";

        }


        /*
         * CATEGORY
         */

        if ($selected_category !== "") {

            $conditions[] = "
                category = '$category_safe'
            ";

        }


        /*
         * WHERE
         */

        $where = "";

        if (count($conditions) > 0) {

            $where =
                "WHERE " .
                implode(
                    " AND ",
                    $conditions
                );

        }


        /* =================================================
           EVENTS QUERY
        ================================================= */

        $sql = "
            SELECT *
            FROM events
            $where
            ORDER BY id DESC
        ";


        $result = mysqli_query(
            $conn,
            $sql
        );


        /* =================================================
           DATABASE CHECK
        ================================================= */

        if (!$result) {

            die(
                "Database Error: " .
                htmlspecialchars(
                    mysqli_error($conn)
                )
            );

        }


        /* =================================================
           RESULT INFO
        ================================================= */

        $event_count =
            mysqli_num_rows(
                $result
            );

        ?>


        <!-- =================================================
             RESULT HEADER
        ================================================== -->

        <div class="events-result-header">

            <div>

                <?php if ($selected_category !== "") { ?>

                    <span class="result-label">
                        CATEGORY
                    </span>

                    <h2>
                        <?php
                        echo htmlspecialchars(
                            $selected_category
                        );
                        ?>
                    </h2>

                <?php } else { ?>

                    <span class="result-label">
                        DISCOVER
                    </span>

                    <h2>
                        Available Events
                    </h2>

                <?php } ?>

            </div>


            <span class="event-result-count">

                <?php
                echo $event_count;
                ?>

                <?php
                echo
                    $event_count == 1
                    ? " Event"
                    : " Events";
                ?>

            </span>

        </div>


        <?php

        /* =================================================
           EVENTS
        ================================================= */

        if ($event_count > 0) {


            while (
                $row =
                mysqli_fetch_assoc(
                    $result
                )
            ) {


                $event_id =
                    (int)$row['id'];


                /* =========================================
                   GET EVENT RATING
                ========================================== */

                $review_sql = "
                    SELECT
                        AVG(rating) AS average_rating,
                        COUNT(*) AS review_count
                    FROM reviews
                    WHERE event_id = '$event_id'
                    AND status = 1
                ";


                $review_result =
                    mysqli_query(
                        $conn,
                        $review_sql
                    );


                $average_rating = 0;

                $review_count = 0;


                if ($review_result) {

                    $review_row =
                        mysqli_fetch_assoc(
                            $review_result
                        );


                    if (
                        $review_row &&
                        $review_row['average_rating']
                        !== null
                    ) {

                        $average_rating =
                            round(
                                (float)
                                $review_row[
                                    'average_rating'
                                ],
                                1
                            );

                    }


                    if ($review_row) {

                        $review_count =
                            (int)
                            $review_row[
                                'review_count'
                            ];

                    }

                }

        ?>


        <!-- =================================================
             EVENT CARD
        ================================================== -->

        <div class="event-card">


            <!-- =================================================
                 IMAGE
            ================================================== -->

            <div class="event-image">

                <?php if (!empty($row['image'])) { ?>

                    <img
                        src="uploads/events/<?php
                            echo htmlspecialchars(
                                $row['image']
                            );
                        ?>"
                        alt="<?php
                            echo htmlspecialchars(
                                $row['event_name']
                            );
                        ?>"
                        onerror="
                            this.style.display='none';
                            this.parentElement.classList.add('image-error');
                        "
                    >

                <?php } else { ?>

                    <div class="event-image-placeholder">

                        <i class="fas fa-calendar-days"></i>

                    </div>

                <?php } ?>


                <!-- WISHLIST -->

                <?php

                if (
                    isset(
                        $_SESSION['user_id']
                    )
                ) {

                ?>

                    <a
                        href="wishlist_action.php?event_id=<?php
                            echo $event_id;
                        ?>"
                        class="wishlist-btn"
                        title="Add to Wishlist"
                    >

                        <i class="fas fa-heart"></i>

                    </a>

                <?php } ?>


            </div>


            <!-- =================================================
                 EVENT BODY
            ================================================== -->

            <div class="event-body">


                <!-- CATEGORY -->

                <span class="event-category">

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


                <!-- =================================================
                     RATING
                ================================================== -->

                <div class="event-rating">

                    <div class="rating-stars">

                        <?php

                        $rounded_rating =
                            round(
                                $average_rating
                            );


                        for (
                            $i = 1;
                            $i <= 5;
                            $i++
                        ) {

                            if (
                                $review_count > 0 &&
                                $i <= $rounded_rating
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


                    <span class="rating-number">

                        <?php

                        if (
                            $review_count > 0
                        ) {

                            echo $average_rating;

                        } else {

                            echo "No rating";

                        }

                        ?>

                    </span>


                    <?php if ($review_count > 0) { ?>

                        <span class="review-count">

                            (
                            <?php
                            echo $review_count;
                            ?>

                            <?php
                            echo
                                $review_count == 1
                                ? "Review"
                                : "Reviews";
                            ?>

                            )

                        </span>

                    <?php } ?>

                </div>


                <!-- =================================================
                     VIEW REVIEWS
                ================================================== -->

                <a
                    href="event-reviews.php?event_id=<?php
                        echo $event_id;
                    ?>"
                    class="event-reviews-link"
                >

                    <i class="fas fa-comment-dots"></i>

                    View Reviews

                </a>


                <!-- =================================================
                     DATE
                ================================================== -->

                <p>

                    <i class="fas fa-calendar-alt"></i>

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

                    } else {

                        echo "N/A";

                    }

                    ?>

                </p>


                <!-- =================================================
                     TIME
                ================================================== -->

                <p>

                    <i class="fas fa-clock"></i>

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

                    } else {

                        echo "N/A";

                    }

                    ?>

                </p>


                <!-- =================================================
                     VENUE
                ================================================== -->

                <p>

                    <i class="fas fa-map-marker-alt"></i>

                    <?php
                    echo htmlspecialchars(
                        $row['venue']
                    );
                    ?>

                </p>


                <!-- =================================================
                     BOTTOM
                ================================================== -->

                <div class="event-bottom">


                    <!-- PRICE -->
                    <div class="event-price">

₹<?php
    echo number_format(
        (float)$row['price'],
        2
    );
?>

<span>per person</span>

</div>


                    <!-- ACTIONS -->

                    <div class="event-actions">


                        <!-- VIEW DETAILS -->

                        <a
                            href="event-details.php?id=<?php
                                echo $event_id;
                            ?>"
                            class="event-details-btn"
                        >

                            <i class="fas fa-eye"></i>

                            View Details

                        </a>


                        <!-- BOOK -->

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
                                class="event-btn"
                            >

                                <i
                                    class="fas fa-calendar-check"
                                ></i>

                                Book Now

                            </a>

                        <?php

                        } else {

                        ?>

                            <a
                                href="login.php"
                                class="event-btn"
                            >

                                <i
                                    class="fas fa-right-to-bracket"
                                ></i>

                                Login To Book

                            </a>

                        <?php } ?>


                    </div>


                </div>


            </div>


        </div>


        <?php

            }

        } else {

        ?>


        <!-- =================================================
             NO EVENTS
        ================================================== -->

        <div class="no-events">

            <div class="no-events-icon">

                <i class="fas fa-calendar-xmark"></i>

            </div>


            <span>
                NO MATCHING EVENTS
            </span>


            <h3>
                No Events Found
            </h3>


            <p>

                <?php

                if (
                    $selected_category !== ""
                ) {

                    echo
                        "There are currently no " .
                        htmlspecialchars(
                            $selected_category
                        ) .
                        " events available.";

                } else {

                    echo
                        "Try searching with another event name, category or venue.";

                }

                ?>

            </p>


            <a
                href="events.php"
                class="clear-filter-btn"
            >

                <i class="fas fa-rotate-left"></i>

                View All Events

            </a>

        </div>


        <?php } ?>


    </div>

</section>


<?php

include "includes/footer.php";

?>