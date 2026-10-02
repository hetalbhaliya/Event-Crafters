<?php

session_start();

include "../config/db.php";

/* =========================
   ADMIN LOGIN CHECK
========================= */

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}


/* =========================
   DELETE REVIEW
========================= */

if (isset($_GET['delete'])) {

    $review_id = (int) $_GET['delete'];

    mysqli_query(
        $conn,
        "DELETE FROM reviews WHERE id = '$review_id'"
    );

    header("Location: reviews.php");
    exit();
}


/* =========================
   TOGGLE REVIEW STATUS
========================= */

if (isset($_GET['toggle'])) {

    $review_id = (int) $_GET['toggle'];

    $review_query = mysqli_query(
        $conn,
        "SELECT status FROM reviews WHERE id = '$review_id'"
    );

    if ($review_query && mysqli_num_rows($review_query) > 0) {

        $review = mysqli_fetch_assoc($review_query);

        $new_status =
            ($review['status'] == 1) ? 0 : 1;

        mysqli_query(
            $conn,
            "UPDATE reviews
             SET status = '$new_status'
             WHERE id = '$review_id'"
        );
    }

    header("Location: reviews.php");
    exit();
}


/* =========================
   GET REVIEWS
========================= */

$sql = "
    SELECT
        reviews.*,
        users.full_name,
        events.event_name
    FROM reviews

    LEFT JOIN users
        ON reviews.user_id = users.id

    LEFT JOIN events
        ON reviews.event_id = events.id

    ORDER BY reviews.id DESC
";

$result = mysqli_query($conn, $sql);


include "../includes/header.php";

?>

<link
    rel="stylesheet"
    href="../assets/css/admin-reviews.css"
>


<div class="admin-page">


    <!-- =========================
         PAGE HEADER
    ========================= -->

    <div class="admin-page-header">

        <div>

            <h1>
                Review Management
            </h1>

            <p>
                Manage customer reviews and ratings.
            </p>

        </div>

        <a
            href="dashboard.php"
            class="back-btn"
        >

            <i class="fas fa-arrow-left"></i>

            Back To Dashboard

        </a>

    </div>


    <!-- =========================
         REVIEWS TABLE
    ========================= -->

    <div class="admin-table-card">

        <div class="table-responsive">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>
                            #
                        </th>

                        <th>
                            User
                        </th>

                        <th>
                            Event
                        </th>

                        <th>
                            Rating
                        </th>

                        <th>
                            Review
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php

                if (
                    $result &&
                    mysqli_num_rows($result) > 0
                ) {

                    $count = 1;

                    while (
                        $row =
                        mysqli_fetch_assoc($result)
                    ) {

                ?>

                    <tr>

                        <!-- NUMBER -->

                        <td>

                            <?php
                            echo $count++;
                            ?>

                        </td>


                        <!-- USER -->

                        <td>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $row['full_name']
                                    ?? 'Unknown User'
                                );

                                ?>

                            </strong>

                        </td>


                        <!-- EVENT -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $row['event_name']
                                ?? 'Unknown Event'
                            );

                            ?>

                        </td>


                        <!-- RATING -->

                        <td>

                            <div class="admin-rating">

                                <?php

                                $rating =
                                    (int)
                                    $row['rating'];

                                for (
                                    $i = 1;
                                    $i <= 5;
                                    $i++
                                ) {

                                    if (
                                        $i <= $rating
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

                                <span>
                                    <?php
                                    echo $rating;
                                    ?>/5
                                </span>

                            </div>

                        </td>


                        <!-- REVIEW -->

                        <td>

                            <div class="review-text">

                                <?php

                                echo nl2br(
                                    htmlspecialchars(
                                        $row['review']
                                    )
                                );

                                ?>

                            </div>

                        </td>


                        <!-- DATE -->

                        <td>

                            <?php

                            if (
                                !empty(
                                    $row['created_at']
                                )
                            ) {

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $row['created_at']
                                    )
                                );

                            } else {

                                echo "N/A";

                            }

                            ?>

                        </td>


                        <!-- STATUS -->

                        <td>

                            <?php

                            if (
                                $row['status'] == 1
                            ) {

                            ?>

                                <span class="status active">
                                    Approved
                                </span>

                            <?php

                            } else {

                            ?>

                                <span class="status inactive">
                                    Hidden
                                </span>

                            <?php

                            }

                            ?>

                        </td>


                        <!-- ACTION -->

                        <td>

                            <div class="review-actions">

                                <a
                                    href="reviews.php?toggle=<?php
                                        echo (int)
                                        $row['id'];
                                    ?>"
                                    class="toggle-btn"
                                >

                                    <?php

                                    if (
                                        $row['status'] == 1
                                    ) {

                                    ?>

                                        <i class="fas fa-eye-slash"></i>
                                        Hide

                                    <?php

                                    } else {

                                    ?>

                                        <i class="fas fa-eye"></i>
                                        Approve

                                    <?php

                                    }

                                    ?>

                                </a>


                                <a
                                    href="reviews.php?delete=<?php
                                        echo (int)
                                        $row['id'];
                                    ?>"
                                    class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete this review?');"
                                >

                                    <i class="fas fa-trash"></i>

                                    Delete

                                </a>

                            </div>

                        </td>

                    </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td
                            colspan="8"
                            class="no-reviews"
                        >

                            <i class="fas fa-star"></i>

                            <h3>
                                No Reviews Found
                            </h3>

                            <p>
                                Customer reviews will appear here.
                            </p>

                        </td>

                    </tr>

                <?php

                }

                ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<?php

include "../includes/footer.php";

?>