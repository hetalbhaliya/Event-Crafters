<?php

session_start();


/* =========================================
   LOGIN CHECK
========================================= */

if (isset($_SESSION['uid'])) {

    $user_id = (int)$_SESSION['uid'];

}
elseif (isset($_SESSION['user_id'])) {

    $user_id = (int)$_SESSION['user_id'];

}
else {

    header("Location: ../login.php");
    exit;

}


/* =========================================
   DATABASE
========================================= */

include("../config/db.php");


if (!$conn) {

    die("Database connection failed.");

}


/* =========================================
   BOOKING ID
========================================= */

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {

    header("Location: my_bookings.php");
    exit;

}


$booking_id = (int)$_GET['id'];


/* =========================================
   FETCH BOOKING
========================================= */

$booking_sql = "
    SELECT
        bookings.*,

        events.event_name,
        events.price AS event_price,
        events.image AS event_image,
        events.category,
        events.venue,
        events.event_date,
        events.event_time

    FROM bookings

    INNER JOIN events
        ON bookings.event_id = events.id

    WHERE bookings.id = '$booking_id'

    AND bookings.user_id = '$user_id'

    LIMIT 1
";


$booking_result = mysqli_query(
    $conn,
    $booking_sql
);


if (!$booking_result) {

    die(
        "Booking database error: " .
        htmlspecialchars(mysqli_error($conn))
    );

}


if (mysqli_num_rows($booking_result) == 0) {

    header("Location: my_bookings.php");
    exit;

}


$booking = mysqli_fetch_assoc(
    $booking_result
);


/* =========================================
   BOOKING STATUS
========================================= */

$booking_status = strtolower(
    trim(
        $booking['status'] ?? ''
    )
);


/* =========================================
   EDITABLE STATUS
========================================= */

/*
 * Pending   → Can edit
 * Confirmed → Can edit, but after editing
 *             booking becomes Pending again.
 *
 * Cancelled / Completed → Cannot edit.
 */

if (
    $booking_status !== 'pending' &&
    $booking_status !== 'confirmed'
) {

    header("Location: my_bookings.php");
    exit;

}


/* =========================================
   FETCH ACTIVE FOOD ITEMS
========================================= */

$foods = [];


$food_sql = "
    SELECT
        id,
        food_name,
        description,
        price,
        image,
        status

    FROM food_items

    WHERE status = 1

    ORDER BY id ASC
";


$food_result = mysqli_query(
    $conn,
    $food_sql
);


if (!$food_result) {

    die(
        "Food database error: " .
        htmlspecialchars(mysqli_error($conn))
    );

}


while (
    $food = mysqli_fetch_assoc(
        $food_result
    )
) {

    $foods[] = $food;

}


/* =========================================
   FETCH CURRENT SELECTED FOOD
========================================= */

$selected_foods = [];


$selected_food_sql = "
    SELECT
        food_id

    FROM booking_food

    WHERE booking_id = '$booking_id'
";


$selected_food_result = mysqli_query(
    $conn,
    $selected_food_sql
);


if ($selected_food_result) {

    while (
        $food_row =
        mysqli_fetch_assoc(
            $selected_food_result
        )
    ) {

        $selected_foods[] =
            (int)$food_row['food_id'];

    }

}


/* =========================================
   MESSAGE
========================================= */

$error = "";


/* =========================================
   UPDATE BOOKING
========================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {


    /* =====================================
       TOTAL PERSON
    ====================================== */

    $total_person =
        isset($_POST['total_person'])
        ? (int)$_POST['total_person']
        : 1;


    if ($total_person < 1) {

        $total_person = 1;

    }


    if ($total_person > 500) {

        $total_person = 500;

    }


    /* =====================================
       FOOD IDS
    ====================================== */

    $food_ids =
        isset($_POST['foods'])
        ? $_POST['foods']
        : [];


    if (!is_array($food_ids)) {

        $food_ids = [];

    }


    /* =====================================
       REMOVE DUPLICATE FOOD IDS
    ====================================== */

    $food_ids = array_unique(
        $food_ids
    );


    /* =====================================
       EVENT PRICE
    ====================================== */

    $event_price =
        (float)$booking['event_price'];


    $event_total =
        $event_price *
        $total_person;


    /* =====================================
       FOOD TOTAL
    ====================================== */

    $food_total = 0;

    $valid_food_ids = [];


    foreach (
        $food_ids as $food_id
    ) {


        if (!is_numeric($food_id)) {

            continue;

        }


        $food_id = (int)$food_id;


        if ($food_id <= 0) {

            continue;

        }


        /* =================================
           VERIFY ACTIVE FOOD
        ================================= */

        $food_query = mysqli_query(
            $conn,

            "
            SELECT
                id,
                food_name,
                price

            FROM food_items

            WHERE id = '$food_id'

            AND status = 1

            LIMIT 1
            "
        );


        if (
            $food_query &&
            mysqli_num_rows(
                $food_query
            ) > 0
        ) {


            $food_data =
                mysqli_fetch_assoc(
                    $food_query
                );


            $food_price =
                (float)$food_data['price'];


            /*
             * Food price is PER PERSON.
             *
             * Example:
             *
             * Food = ₹500
             * Persons = 10
             *
             * Food Total = ₹5,000
             */

            $food_total +=
                $food_price *
                $total_person;


            $valid_food_ids[] =
                $food_id;

        }

    }


    /* =====================================
       FINAL TOTAL
    ====================================== */

    $total_amount =
        $event_total +
        $food_total;


    /* =====================================
       DECIDE NEW BOOKING STATUS
    ====================================== */

    /*
     * Confirmed booking edited:
     *
     * Confirmed → Pending
     *
     * Pending booking edited:
     *
     * Pending → Pending
     */

    if (
        $booking_status === 'confirmed'
    ) {

        $new_status = 'Pending';

    }
    else {

        /*
         * Keep original Pending status.
         */

        $new_status =
            $booking['status'];

    }


    /* =====================================
       FORMAT TOTAL
    ====================================== */

    $total_amount_sql =
        number_format(
            $total_amount,
            2,
            '.',
            ''
        );


    /* =====================================
       ESCAPE STATUS
    ====================================== */

    $new_status_sql =
        mysqli_real_escape_string(
            $conn,
            $new_status
        );


    /* =====================================
       START TRANSACTION
    ====================================== */

    mysqli_begin_transaction(
        $conn
    );


    try {


        /* =================================
           UPDATE BOOKING
        ================================= */

        $update_sql = "
            UPDATE bookings

            SET
                total_person = '$total_person',
                total_amount = '$total_amount_sql',
                status = '$new_status_sql'

            WHERE id = '$booking_id'

            AND user_id = '$user_id'
        ";


        $update_result = mysqli_query(
            $conn,
            $update_sql
        );


        if (!$update_result) {

            throw new Exception(
                "Unable to update booking."
            );

        }


        /* =================================
           REMOVE OLD FOOD
        ================================= */

        $delete_food_sql = "
            DELETE FROM booking_food

            WHERE booking_id = '$booking_id'
        ";


        $delete_food_result =
            mysqli_query(
                $conn,
                $delete_food_sql
            );


        if (!$delete_food_result) {

            throw new Exception(
                "Unable to update selected food."
            );

        }


        /* =================================
           ADD NEW FOOD
        ================================= */

        foreach (
            $valid_food_ids as $food_id
        ) {


            $insert_food_sql = "
                INSERT INTO booking_food
                (
                    booking_id,
                    food_id,
                    quantity
                )

                VALUES
                (
                    '$booking_id',
                    '$food_id',
                    '$total_person'
                )
            ";


            $insert_food_result =
                mysqli_query(
                    $conn,
                    $insert_food_sql
                );


            if (!$insert_food_result) {

                throw new Exception(
                    "Unable to save selected food."
                );

            }

        }


        /* =================================
           NOTIFICATION
        ================================= */

        if (
            $booking_status === 'confirmed'
        ) {


            $notification_title =
                "Booking Sent for Re-Confirmation";


            $notification_message =
                "Your changes to " .
                $booking['event_name'] .
                " have been saved. " .
                "Your booking is now pending admin confirmation again.";

        }
        else {


            $notification_title =
                "Booking Updated";


            $notification_message =
                "Your booking for " .
                $booking['event_name'] .
                " has been updated successfully and is pending admin confirmation.";

        }


        /* =================================
           ESCAPE NOTIFICATION
        ================================= */

        $notification_title =
            mysqli_real_escape_string(
                $conn,
                $notification_title
            );


        $notification_message =
            mysqli_real_escape_string(
                $conn,
                $notification_message
            );


        /* =================================
           INSERT NOTIFICATION
        ================================= */

        $notification_sql = "
            INSERT INTO notifications
            (
                user_id,
                title,
                message,
                is_read
            )

            VALUES
            (
                '$user_id',
                '$notification_title',
                '$notification_message',
                0
            )
        ";


        $notification_result =
            mysqli_query(
                $conn,
                $notification_sql
            );


        if (!$notification_result) {

            throw new Exception(
                "Booking updated but notification could not be created."
            );

        }


        /* =================================
           COMMIT
        ================================= */

        mysqli_commit(
            $conn
        );


        /* =================================
           REDIRECT
        ================================= */

        header(
            "Location: my_bookings.php?updated=1"
        );

        exit;


    }
    catch (Exception $e) {


        /* =================================
           ROLLBACK
        ================================= */

        mysqli_rollback(
            $conn
        );


        $error =
            $e->getMessage();

    }

}


/* =========================================
   HEADER
========================================= */

include("../includes/header.php");

include("../includes/navbar.php");

?>


<link
    rel="stylesheet"
    href="../assets/css/edit-booking.css"
>


<div class="edit-booking-page">

    <div class="edit-booking-container">


        <!-- =================================
             PAGE HEADER
        ================================== -->

        <div class="edit-booking-header">


            <a
                href="my_bookings.php"
                class="edit-back-btn"
            >

                <i class="fas fa-arrow-left"></i>

                Back to My Bookings

            </a>


            <span>
                MANAGE BOOKING
            </span>


            <h1>
                Edit Booking
            </h1>


            <p>
                Update your number of persons and food selection.
            </p>


        </div>


        <!-- =================================
             ERROR
        ================================== -->

        <?php if (!empty($error)) { ?>


            <div class="edit-error">

                <i class="fas fa-circle-exclamation"></i>

                <?php

                echo htmlspecialchars(
                    $error
                );

                ?>

            </div>


        <?php } ?>


        <!-- =================================
             BOOKING CARD
        ================================== -->

        <div class="edit-booking-card">


            <!-- =================================
                 EVENT HEADER
            ================================== -->

            <div class="edit-event-info">


                <div>

                    <span>
                        EVENT
                    </span>


                    <h2>

                        <?php

                        echo htmlspecialchars(
                            $booking['event_name']
                        );

                        ?>

                    </h2>

                </div>


                <div class="edit-status">

                    <?php

                    echo htmlspecialchars(
                        ucfirst(
                            $booking_status
                        )
                    );

                    ?>

                </div>


            </div>


            <!-- =================================
                 FORM
            ================================== -->

            <form
                method="POST"
                action=""
            >


                <!-- =================================
                     NUMBER OF PERSONS
                ================================== -->

                <div class="edit-section">


                    <div class="edit-section-title">


                        <i class="fas fa-users"></i>


                        <div>

                            <h3>
                                Number of Persons
                            </h3>

                            <p>
                                Select the required number of guests.
                            </p>

                        </div>


                    </div>


                    <div class="person-control">


                        <button
                            type="button"
                            id="minusPerson"
                        >

                            <i class="fas fa-minus"></i>

                        </button>


                        <input
                            type="number"
                            id="totalPerson"
                            name="total_person"

                            value="<?php

                                echo max(
                                    1,
                                    (int)$booking['total_person']
                                );

                            ?>"

                            min="1"
                            max="500"
                            readonly
                        >


                        <button
                            type="button"
                            id="plusPerson"
                        >

                            <i class="fas fa-plus"></i>

                        </button>


                    </div>


                </div>


                <!-- =================================
                     FOOD SELECTION
                ================================== -->

                <div class="edit-section">


                    <div class="edit-section-title">


                        <i class="fas fa-utensils"></i>


                        <div>

                            <h3>
                                Food Selection
                            </h3>

                            <p>
                                Food price is calculated per person.
                            </p>

                        </div>


                    </div>


                    <?php if (
                        count($foods) > 0
                    ) { ?>


                        <div class="edit-food-grid">


                            <?php

                            foreach (
                                $foods as $food
                            ) {


                                $food_id =
                                    (int)$food['id'];


                                $checked =
                                    in_array(
                                        $food_id,
                                        $selected_foods
                                    );

                            ?>


                                <label
                                    class="edit-food-card <?php

                                        echo $checked
                                            ? 'selected'
                                            : '';

                                    ?>"
                                >


                                    <!-- CHECKBOX -->

                                    <input
                                        type="checkbox"

                                        name="foods[]"

                                        value="<?php

                                            echo $food_id;

                                        ?>"

                                        <?php

                                        echo $checked
                                            ? 'checked'
                                            : '';

                                        ?>
                                    >


                                    <!-- FOOD IMAGE -->

                                    <div class="edit-food-image">


                                        <?php if (
                                            !empty(
                                                $food['image']
                                            )
                                        ) { ?>


                                            <img
                                                src="../uploads/events/<?php

                                                    echo htmlspecialchars(
                                                        $food['image']
                                                    );

                                                ?>"

                                                alt="<?php

                                                    echo htmlspecialchars(
                                                        $food['food_name']
                                                    );

                                                ?>"
                                            >


                                        <?php } else { ?>


                                            <div class="edit-food-icon">

                                                <i class="fas fa-utensils"></i>

                                            </div>


                                        <?php } ?>


                                    </div>


                                    <!-- FOOD CONTENT -->

                                    <div class="edit-food-content">


                                        <strong>

                                            <?php

                                            echo htmlspecialchars(
                                                $food['food_name']
                                            );

                                            ?>

                                        </strong>


                                        <?php if (
                                            !empty(
                                                $food['description']
                                            )
                                        ) { ?>


                                            <p>

                                                <?php

                                                echo htmlspecialchars(
                                                    $food['description']
                                                );

                                                ?>

                                            </p>


                                        <?php } ?>


                                        <span>

                                            ₹<?php

                                            echo number_format(
                                                (float)$food['price'],
                                                2
                                            );

                                            ?>

                                            / plate

                                        </span>


                                    </div>


                                    <!-- CHECK ICON -->

                                    <div class="edit-check">

                                        <i class="fas fa-check"></i>

                                    </div>


                                </label>


                            <?php } ?>


                        </div>


                    <?php } else { ?>


                        <!-- =================================
                             NO FOOD
                        ================================== -->

                        <div class="no-food">


                            <i class="fas fa-utensils"></i>


                            <p>
                                No active food options available.
                            </p>


                        </div>


                    <?php } ?>


                </div>


                <!-- =================================
                     CURRENT TOTAL INFORMATION
                ================================== -->

                <div class="edit-total-info">


                    <div>

                        <span>
                        Event Package
                        </span>

                        <strong>

                            ₹<?php

                            echo number_format(
                                (float)$booking['event_price'],
                                2
                            );

                            ?>

                        </strong>

                    </div>


                    <div>

                        <span>
                            Current Booking Total
                        </span>

                        <strong>

                            ₹<?php

                            echo number_format(
                                (float)$booking['total_amount'],
                                2
                            );

                            ?>

                        </strong>

                    </div>


                </div>


                <!-- =================================
                     CONFIRMED EDIT NOTICE
                ================================== -->

                <?php if (
                    $booking_status === 'confirmed'
                ) { ?>


                    <div class="edit-confirmed-notice">


                        <i class="fas fa-circle-info"></i>


                        <div>


                            <strong>
                                Confirmed Booking
                            </strong>


                            <p>
                                You can edit this booking.
                                After making changes, the booking
                                will become pending and must be
                                confirmed by the admin again.
                            </p>


                        </div>


                    </div>


                <?php } ?>


                <!-- =================================
                     ACTIONS
                ================================== -->

                <div class="edit-actions">


                    <a
                        href="my_bookings.php"
                        class="edit-cancel-btn"
                    >

                        Cancel

                    </a>


                    <button
                        type="submit"
                        class="edit-save-btn"
                    >

                        <i class="fas fa-check"></i>

                        Update Booking

                    </button>


                </div>


            </form>


        </div>


    </div>

</div>


<!-- =========================================
     JAVASCRIPT
========================================= -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        /* =====================================
           PERSON CONTROLS
        ====================================== */

        const personInput =
            document.getElementById(
                "totalPerson"
            );


        const minusBtn =
            document.getElementById(
                "minusPerson"
            );


        const plusBtn =
            document.getElementById(
                "plusPerson"
            );


        if (
            personInput &&
            minusBtn &&
            plusBtn
        ) {


            minusBtn.addEventListener(
                "click",
                function () {


                    let value =
                        parseInt(
                            personInput.value
                        ) || 1;


                    if (value > 1) {

                        personInput.value =
                            value - 1;

                    }

                }
            );


            plusBtn.addEventListener(
                "click",
                function () {


                    let value =
                        parseInt(
                            personInput.value
                        ) || 1;


                    if (value < 500) {

                        personInput.value =
                            value + 1;

                    }

                }
            );

        }


        /* =====================================
           FOOD CARD SELECTION
        ====================================== */

        const foodCards =
            document.querySelectorAll(
                ".edit-food-card"
            );


        foodCards.forEach(
            function (card) {


                const checkbox =
                    card.querySelector(
                        "input[type='checkbox']"
                    );


                if (!checkbox) {

                    return;

                }


                function updateCard() {


                    if (
                        checkbox.checked
                    ) {

                        card.classList.add(
                            "selected"
                        );

                    }
                    else {

                        card.classList.remove(
                            "selected"
                        );

                    }

                }


                checkbox.addEventListener(
                    "change",
                    updateCard
                );


                updateCard();

            }
        );


    }
);

</script>


<?php

include("../includes/footer.php");

?>