<?php

session_start();


/* =========================================
   CHECK USER LOGIN
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
   ONLY POST REQUEST
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../events.php");
    exit;

}


/* =========================================
   USER ID
========================================= */

$user_id = (int)$_SESSION['user_id'];


/* =========================================
   EVENT ID
========================================= */

$event_id = isset($_POST['event_id'])
    ? (int)$_POST['event_id']
    : 0;


/* =========================================
   TOTAL PERSON
========================================= */

$total_person = isset($_POST['total_person'])
    ? (int)$_POST['total_person']
    : 0;


/* =========================================
   COUPON CODE
========================================= */

$applied_coupon = isset($_POST['applied_coupon'])
    ? trim($_POST['applied_coupon'])
    : "";


/* =========================================
   VALIDATION

   Maximum Persons = 100000
========================================= */

if (
    $event_id <= 0 ||
    $total_person <= 0 ||
    $total_person > 100000
) {

    echo "<script>
            alert('Please enter valid booking details. Maximum 100000 persons are allowed.');
            window.history.back();
          </script>";

    exit;

}


/* =========================================
   GET USER DETAILS
========================================= */

$user_sql = "
    SELECT
        full_name,
        email,
        mobile
    FROM users
    WHERE id = '$user_id'
    LIMIT 1
";


$user_result = mysqli_query(
    $conn,
    $user_sql
);


if (
    !$user_result ||
    mysqli_num_rows($user_result) == 0
) {

    echo "<script>
            alert('User details not found.');
            window.location.href='../login.php';
          </script>";

    exit;

}


$user = mysqli_fetch_assoc(
    $user_result
);


/* =========================================
   GET EVENT DETAILS
========================================= */

$event_sql = "
    SELECT
        id,
        event_name,
        category,
        subcategory,
        price,
        event_date,
        event_time,
        venue
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

    echo "<script>
            alert('Event not found.');
            window.location.href='../events.php';
          </script>";

    exit;

}


$event = mysqli_fetch_assoc(
    $event_result
);


/* =========================================
   USER DATA
========================================= */

$full_name = mysqli_real_escape_string(
    $conn,
    $user['full_name']
);


$email = mysqli_real_escape_string(
    $conn,
    $user['email']
);


$mobile = mysqli_real_escape_string(
    $conn,
    $user['mobile']
);


/* =========================================
   EVENT PRICE

   EVENT PRICE IS A FIXED PACKAGE

   DO NOT MULTIPLY BY PERSONS
========================================= */

$event_price =
    (float)$event['price'];


$event_total =
    $event_price;


/* =========================================
   FOOD TOTAL
========================================= */

$food_total = 0;


/* =========================================
   SELECTED FOOD
========================================= */

$selected_food = array();


if (
    isset($_POST['food']) &&
    is_array($_POST['food'])
) {

    $selected_food = $_POST['food'];

}


/* =========================================
   REMOVE DUPLICATE FOOD IDS
========================================= */

$selected_food =
    array_unique(
        $selected_food
    );


/* =========================================
   CALCULATE FOOD TOTAL

   FOOD PRICE × PERSONS
========================================= */

foreach (
    $selected_food
    as $food_id
) {

    $food_id =
        (int)$food_id;


    if ($food_id <= 0) {
        continue;
    }


    /* =====================================
       GET ACTIVE FOOD
    ===================================== */

    $food_sql = "
        SELECT
            id,
            food_name,
            price
        FROM food_items
        WHERE id = '$food_id'
        AND status = 1
        LIMIT 1
    ";


    $food_result =
        mysqli_query(
            $conn,
            $food_sql
        );


    if (
        $food_result &&
        mysqli_num_rows(
            $food_result
        ) > 0
    ) {

        $food =
            mysqli_fetch_assoc(
                $food_result
            );


        /* =================================
           FOOD PRICE
        ================================= */

        $food_price =
            (float)$food['price'];


        /* =================================
           FOOD TOTAL

           PRICE × PERSONS
        ================================= */

        $food_total +=
            $food_price *
            $total_person;

    }

}


/* =========================================
   SUBTOTAL

   EVENT PACKAGE
   +
   FOOD TOTAL
========================================= */

$subtotal =
    $event_total +
    $food_total;


/* =========================================
   COUPON DISCOUNT
========================================= */

$coupon_discount = 0;


/* =========================================
   VALIDATE COUPON
========================================= */

if (
    $applied_coupon !== ""
) {

    $coupon_code_db =
        mysqli_real_escape_string(
            $conn,
            $applied_coupon
        );


    /* =====================================
       GET COUPON
    ===================================== */

    $coupon_sql = "
        SELECT
            id,
            coupon_code,
            discount_type,
            discount_value,
            min_amount,
            max_discount,
            status,
            start_date,
            expiry_date
        FROM coupons
        WHERE coupon_code = '$coupon_code_db'
        LIMIT 1
    ";


    $coupon_result =
        mysqli_query(
            $conn,
            $coupon_sql
        );


    if (
        !$coupon_result ||
        mysqli_num_rows(
            $coupon_result
        ) == 0
    ) {

        echo "<script>
                alert('Invalid coupon code.');
                window.history.back();
              </script>";

        exit;

    }


    $coupon =
        mysqli_fetch_assoc(
            $coupon_result
        );


    /* =====================================
       CHECK STATUS
    ===================================== */

    if (
        (int)$coupon['status'] !== 1
    ) {

        echo "<script>
                alert('This coupon is inactive.');
                window.history.back();
              </script>";

        exit;

    }


    /* =====================================
       CURRENT DATE
    ===================================== */

    $today =
        date("Y-m-d");


    /* =====================================
       START DATE
    ===================================== */

    if (
        !empty($coupon['start_date']) &&
        $today < $coupon['start_date']
    ) {

        echo "<script>
                alert('This coupon is not active yet.');
                window.history.back();
              </script>";

        exit;

    }


    /* =====================================
       EXPIRY DATE
    ===================================== */

    if (
        !empty($coupon['expiry_date']) &&
        $today > $coupon['expiry_date']
    ) {

        echo "<script>
                alert('This coupon has expired.');
                window.history.back();
              </script>";

        exit;

    }


    /* =====================================
       MINIMUM AMOUNT
    ===================================== */

    $min_amount =
        (float)$coupon['min_amount'];


    if (
        $subtotal <
        $min_amount
    ) {

        echo "<script>
                alert('Minimum booking amount for this coupon is ₹" .
                number_format(
                    $min_amount,
                    2
                ) .
                ".');
                window.history.back();
              </script>";

        exit;

    }


    /* =====================================
       DISCOUNT TYPE
    ===================================== */

    $discount_type =
        strtolower(
            trim(
                $coupon['discount_type']
            )
        );


    /* =====================================
       DISCOUNT VALUE
    ===================================== */

    $discount_value =
        (float)$coupon['discount_value'];


    /* =====================================
       CALCULATE DISCOUNT
    ===================================== */

    if (
        $discount_type ===
        "percentage"
    ) {

        $coupon_discount =
            (
                $subtotal *
                $discount_value
            ) / 100;

    }

    else {

        /*
         * FIXED DISCOUNT
         */

        $coupon_discount =
            $discount_value;

    }


    /* =====================================
       MAX DISCOUNT
    ===================================== */

    $max_discount =
        (float)$coupon['max_discount'];


    if (
        $max_discount > 0 &&
        $coupon_discount >
        $max_discount
    ) {

        $coupon_discount =
            $max_discount;

    }


    /* =====================================
       DISCOUNT CANNOT EXCEED SUBTOTAL
    ===================================== */

    if (
        $coupon_discount >
        $subtotal
    ) {

        $coupon_discount =
            $subtotal;

    }


    /* =====================================
       ROUND DISCOUNT
    ===================================== */

    $coupon_discount =
        round(
            $coupon_discount,
            2
        );

}


/* =========================================
   FINAL TOTAL
========================================= */

$total_amount =
    $subtotal -
    $coupon_discount;


/* =========================================
   SAFETY CHECK
========================================= */

if (
    $total_amount < 0
) {

    $total_amount = 0;

}


/* =========================================
   BOOKING DATE
========================================= */

$booking_date =
    date(
        "Y-m-d H:i:s"
    );


/* =========================================
   STATUS
========================================= */

$status =
    "Pending";


/* =========================================
   INSERT BOOKING

   IMPORTANT:
   NO decoration_id
   NO decoration_price
========================================= */

$booking_sql = "

    INSERT INTO bookings
    (
        event_id,
        user_id,
        full_name,
        email,
        mobile,
        total_person,
        booking_date,
        total_amount,
        status,
        created_at
    )

    VALUES
    (
        '$event_id',
        '$user_id',
        '$full_name',
        '$email',
        '$mobile',
        '$total_person',
        '$booking_date',
        '$total_amount',
        '$status',
        NOW()
    )

";


$booking_result =
    mysqli_query(
        $conn,
        $booking_sql
    );


/* =========================================
   BOOKING INSERT CHECK
========================================= */

if (!$booking_result) {

    die(
        "Booking Failed: " .
        mysqli_error($conn)
    );

}


/* =========================================
   BOOKING ID
========================================= */

$booking_id =
    mysqli_insert_id(
        $conn
    );


/* =========================================
   SAVE FOOD IN booking_food
========================================= */

foreach (
    $selected_food
    as $food_id
) {

    $food_id =
        (int)$food_id;


    if ($food_id <= 0) {
        continue;
    }


    /* =====================================
       GET ACTIVE FOOD PRICE
    ===================================== */

    $food_sql = "
        SELECT
            id,
            price
        FROM food_items
        WHERE id = '$food_id'
        AND status = 1
        LIMIT 1
    ";


    $food_result =
        mysqli_query(
            $conn,
            $food_sql
        );


    if (
        $food_result &&
        mysqli_num_rows(
            $food_result
        ) > 0
    ) {

        $food =
            mysqli_fetch_assoc(
                $food_result
            );


        $food_price =
            (float)$food['price'];


        /* =================================
           QUANTITY = PERSONS
        ================================= */

        $quantity =
            $total_person;


        /* =================================
           INSERT FOOD
        ================================= */

        $food_insert_sql = "

            INSERT INTO booking_food
            (
                booking_id,
                food_id,
                quantity,
                price
            )

            VALUES
            (
                '$booking_id',
                '$food_id',
                '$quantity',
                '$food_price'
            )

        ";


        $food_insert_result =
            mysqli_query(
                $conn,
                $food_insert_sql
            );


        /*
         * If food insert fails,
         * show error instead of silently continuing.
         */

        if (!$food_insert_result) {

            die(
                "Food Booking Failed: " .
                mysqli_error($conn)
            );

        }

    }

}


/* =========================================
   CREATE NOTIFICATION
========================================= */

$title =
    "Booking Received";


/* =========================================
   BASE MESSAGE
========================================= */

$message =
    "Your booking for " .
    $event['event_name'] .
    " has been received for " .
    $total_person .
    " person(s).";


/* =========================================
   CATEGORY MESSAGE
========================================= */

if (
    !empty($event['category'])
) {

    $message .=
        " Category: " .
        $event['category'];

}


if (
    !empty($event['subcategory'])
) {

    $message .=
        " / " .
        $event['subcategory'];

}


/* =========================================
   TOTAL MESSAGE
========================================= */

$message .=
    ". Total amount: ₹" .
    number_format(
        $total_amount,
        2
    );


/* =========================================
   COUPON MESSAGE
========================================= */

if (
    $applied_coupon !== "" &&
    $coupon_discount > 0
) {

    $message .=
        " Coupon " .
        $applied_coupon .
        " applied. Discount: ₹" .
        number_format(
            $coupon_discount,
            2
        ) .
        ".";

}


/* =========================================
   ESCAPE NOTIFICATION
========================================= */

$title =
    mysqli_real_escape_string(
        $conn,
        $title
    );


$message =
    mysqli_real_escape_string(
        $conn,
        $message
    );


/* =========================================
   INSERT NOTIFICATION
========================================= */

$notification_sql = "

    INSERT INTO notifications
    (
        user_id,
        title,
        message,
        type,
        is_read,
        created_at
    )

    VALUES
    (
        '$user_id',
        '$title',
        '$message',
        'booking',
        0,
        NOW()
    )

";


mysqli_query(
    $conn,
    $notification_sql
);


/* =========================================
   SUCCESS
========================================= */

echo "

<script>

    alert('Booking successful!');

    window.location.href =
        'my_bookings.php';

</script>

";


exit;

?>