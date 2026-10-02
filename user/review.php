<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

$user_id = $_SESSION['user_id'];

if (!isset($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    header("Location: my_bookings.php");
    exit;
}

$booking_id = (int)$_GET['booking_id'];


// ===============================
// GET BOOKING DETAILS
// ===============================

$sql = "SELECT 
            b.id AS booking_id,
            b.user_id,
            b.event_id,
            e.event_name,
            e.image
        FROM bookings b
        INNER JOIN events e ON b.event_id = e.id
        WHERE b.id = '$booking_id'
        AND b.user_id = '$user_id'
        LIMIT 1";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: my_bookings.php");
    exit;
}

$booking = mysqli_fetch_assoc($result);


// ===============================
// CHECK EXISTING REVIEW
// ===============================

$check_sql = "SELECT id 
              FROM reviews
              WHERE user_id = '$user_id'
              AND booking_id = '$booking_id'
              LIMIT 1";

$check_result = mysqli_query($conn, $check_sql);

if ($check_result && mysqli_num_rows($check_result) > 0) {
    $already_reviewed = true;
} else {
    $already_reviewed = false;
}


// ===============================
// SUBMIT REVIEW
// ===============================

$message = "";
$message_type = "";

if (isset($_POST['submit_review']) && !$already_reviewed) {

    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $review = trim($_POST['review']);

    if ($rating < 1 || $rating > 5) {

        $message = "Please select a rating.";
        $message_type = "error";

    } elseif ($review == "") {

        $message = "Please write your review.";
        $message_type = "error";

    } else {

        $review_safe = mysqli_real_escape_string($conn, $review);

        $insert_sql = "INSERT INTO reviews
                       (user_id, event_id, booking_id, rating, review, status)
                       VALUES
                       ('$user_id',
                        '{$booking['event_id']}',
                        '$booking_id',
                        '$rating',
                        '$review_safe',
                        1)";

        if (mysqli_query($conn, $insert_sql)) {

            header("Location: review.php?booking_id=$booking_id&success=1");
            exit;

        } else {

            $message = "Something went wrong. Please try again.";
            $message_type = "error";
        }
    }
}


if (isset($_GET['success'])) {
    $message = "Your review has been submitted successfully!";
    $message_type = "success";
}


include "../includes/header.php";
include "../includes/navbar.php";

?>

<link rel="stylesheet" href="../assets/css/review.css">


<section class="review-page">

    <div class="review-container">

        <a href="my_bookings.php" class="review-back">
            <i class="fa-solid fa-arrow-left"></i>
            Back to My Bookings
        </a>


        <div class="review-card">

            <div class="review-event">

                <div class="review-event-image">

                    <?php if (!empty($booking['image'])) { ?>

                        <img
                            src="../uploads/events/<?php echo htmlspecialchars($booking['image']); ?>"
                            alt="<?php echo htmlspecialchars($booking['event_name']); ?>"
                        >

                    <?php } else { ?>

                        <div class="review-no-image">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>

                    <?php } ?>

                </div>


                <div class="review-event-info">

                    <span class="review-label">
                        YOUR EVENT
                    </span>

                    <h1>
                        <?php echo htmlspecialchars($booking['event_name']); ?>
                    </h1>

                    <p>
                        Share your experience with this event.
                    </p>

                </div>

            </div>


            <?php if ($message != "") { ?>

                <div class="review-message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php } ?>


            <?php if ($already_reviewed) { ?>

                <div class="review-completed">

                    <div class="completed-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>

                    <h2>Review Already Submitted</h2>

                    <p>
                        You have already submitted a review for this booking.
                    </p>

                    <a href="my_bookings.php" class="review-btn">
                        Back to My Bookings
                    </a>

                </div>

            <?php } else { ?>


                <form method="POST" class="review-form">

                    <div class="rating-section">

                        <h3>
                            How was your experience?
                        </h3>

                        <p>
                            Select a rating from 1 to 5 stars.
                        </p>


                        <div class="star-rating">

                            <input type="radio" name="rating" id="star5" value="5">
                            <label for="star5">★</label>

                            <input type="radio" name="rating" id="star4" value="4">
                            <label for="star4">★</label>

                            <input type="radio" name="rating" id="star3" value="3">
                            <label for="star3">★</label>

                            <input type="radio" name="rating" id="star2" value="2">
                            <label for="star2">★</label>

                            <input type="radio" name="rating" id="star1" value="1">
                            <label for="star1">★</label>

                        </div>

                    </div>


                    <div class="review-input">

                        <label for="review">
                            Your Review
                        </label>

                        <textarea
                            name="review"
                            id="review"
                            rows="6"
                            maxlength="1000"
                            placeholder="Tell us about your experience..."
                        ></textarea>

                    </div>


                    <button
                        type="submit"
                        name="submit_review"
                        class="review-submit"
                    >
                        <i class="fa-solid fa-paper-plane"></i>
                        Submit Review
                    </button>

                </form>

            <?php } ?>

        </div>

    </div>

</section>


<?php include "../includes/footer.php"; ?>