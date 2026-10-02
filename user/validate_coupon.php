<?php

session_start();

header("Content-Type: application/json");


/* =========================================
   CHECK USER LOGIN
========================================= */

if (!isset($_SESSION['user_id'])) {

    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);

    exit;
}


/* =========================================
   DATABASE
========================================= */

include "../config/db.php";


/* =========================================
   GET DATA
========================================= */

$coupon_code = isset($_POST['coupon_code'])
    ? trim($_POST['coupon_code'])
    : "";

$amount = isset($_POST['subtotal'])
    ? (float)$_POST['subtotal']
    : 0;


/* =========================================
   BASIC VALIDATION
========================================= */

if ($coupon_code === "") {

    echo json_encode([
        "success" => false,
        "message" => "Please enter coupon code."
    ]);

    exit;
}


if ($amount <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid booking amount."
    ]);

    exit;
}


/* =========================================
   ESCAPE COUPON CODE
========================================= */

$coupon_code = mysqli_real_escape_string(
    $conn,
    strtoupper($coupon_code)
);


/* =========================================
   FIND COUPON
========================================= */

$sql = "
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
    WHERE UPPER(coupon_code) = '$coupon_code'
    LIMIT 1
";


$result = mysqli_query(
    $conn,
    $sql
);


/* =========================================
   DATABASE ERROR
========================================= */

if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Unable to check coupon."
    ]);

    exit;
}


/* =========================================
   COUPON NOT FOUND
========================================= */

if (mysqli_num_rows($result) == 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid coupon code."
    ]);

    exit;
}


$coupon = mysqli_fetch_assoc($result);


/* =========================================
   CHECK STATUS
========================================= */

if ((int)$coupon['status'] !== 1) {

    echo json_encode([
        "success" => false,
        "message" => "This coupon is inactive."
    ]);

    exit;
}


/* =========================================
   CURRENT DATE
========================================= */

$today = date("Y-m-d");


/* =========================================
   START DATE
========================================= */

if (
    !empty($coupon['start_date']) &&
    $today < $coupon['start_date']
) {

    echo json_encode([
        "success" => false,
        "message" => "This coupon is not active yet."
    ]);

    exit;
}


/* =========================================
   EXPIRY DATE
========================================= */

if (
    !empty($coupon['expiry_date']) &&
    $today > $coupon['expiry_date']
) {

    echo json_encode([
        "success" => false,
        "message" => "This coupon has expired."
    ]);

    exit;
}


/* =========================================
   MINIMUM BOOKING AMOUNT
========================================= */

$min_amount = (float)$coupon['min_amount'];

if ($amount < $min_amount) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Minimum booking amount is ₹" .
            number_format($min_amount, 2)
    ]);

    exit;
}


/* =========================================
   DISCOUNT DETAILS
========================================= */

$discount_type = strtolower(
    trim($coupon['discount_type'])
);

$discount_value = (float)$coupon['discount_value'];

$discount = 0;


/* =========================================
   PERCENTAGE DISCOUNT
========================================= */

if ($discount_type === "percentage") {

    $discount =
        ($amount * $discount_value) / 100;
}


/* =========================================
   FIXED DISCOUNT
========================================= */

elseif ($discount_type === "fixed") {

    $discount =
        $discount_value;
}


/* =========================================
   INVALID DISCOUNT TYPE
========================================= */

else {

    echo json_encode([
        "success" => false,
        "message" => "Invalid discount type."
    ]);

    exit;
}


/* =========================================
   MAXIMUM DISCOUNT
========================================= */

if (
    $coupon['max_discount'] !== null &&
    $coupon['max_discount'] !== '' &&
    (float)$coupon['max_discount'] > 0
) {

    $max_discount =
        (float)$coupon['max_discount'];

    if ($discount > $max_discount) {

        $discount =
            $max_discount;
    }
}


/* =========================================
   DISCOUNT CANNOT EXCEED AMOUNT
========================================= */

if ($discount > $amount) {

    $discount =
        $amount;
}


/* =========================================
   ROUND DISCOUNT
========================================= */

$discount =
    round($discount, 2);


/* =========================================
   SUCCESS RESPONSE
========================================= */

echo json_encode([

    "success" => true,

    "message" =>
        "Coupon applied successfully!",

    "coupon_code" =>
        $coupon['coupon_code'],

    "discount" =>
        $discount

]);

exit;

?>