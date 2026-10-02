

<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

include "../config/db.php";


/* =========================
   CHECK COUPON ID
========================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: coupons.php");
    exit;
}

$id = (int) $_GET['id'];


/* =========================
   UPDATE COUPON
========================= */

if (isset($_POST['update_coupon'])) {

    $coupon_code = strtoupper(trim($_POST['coupon_code']));
    $discount_type = $_POST['discount_type'];
    $discount_value = (float) $_POST['discount_value'];
    $min_amount = (float) $_POST['min_amount'];

    $max_discount = trim($_POST['max_discount']);

    $start_date = trim($_POST['start_date']);
    $expiry_date = trim($_POST['expiry_date']);


    /* Maximum discount */

    if ($max_discount === "") {
        $max_discount_sql = "NULL";
    } else {
        $max_discount_sql = (float) $max_discount;
    }


    /* Start date */

    if ($start_date === "") {
        $start_date_sql = "NULL";
    } else {
        $start_date_safe = mysqli_real_escape_string(
            $conn,
            $start_date
        );

        $start_date_sql = "'$start_date_safe'";
    }


    /* Expiry date */

    if ($expiry_date === "") {
        $expiry_date_sql = "NULL";
    } else {
        $expiry_date_safe = mysqli_real_escape_string(
            $conn,
            $expiry_date
        );

        $expiry_date_sql = "'$expiry_date_safe'";
    }


    /* Validation */

    if ($coupon_code == "") {

        $error = "Coupon code is required.";

    } elseif ($discount_value <= 0) {

        $error = "Discount value must be greater than 0.";

    } elseif (
        $discount_type == "percentage"
        &&
        $discount_value > 100
    ) {

        $error = "Percentage discount cannot be more than 100%.";

    } else {

        $coupon_code_safe = mysqli_real_escape_string(
            $conn,
            $coupon_code
        );

        $discount_type_safe = mysqli_real_escape_string(
            $conn,
            $discount_type
        );


        /* Check duplicate code */

        $check = mysqli_query(
            $conn,
            "SELECT id
             FROM coupons
             WHERE coupon_code = '$coupon_code_safe'
             AND id != $id
             LIMIT 1"
        );


        if (mysqli_num_rows($check) > 0) {

            $error = "Coupon code already exists.";

        } else {

            $sql = "UPDATE coupons SET

                    coupon_code = '$coupon_code_safe',

                    discount_type = '$discount_type_safe',

                    discount_value = $discount_value,

                    min_amount = $min_amount,

                    max_discount = $max_discount_sql,

                    start_date = $start_date_sql,

                    expiry_date = $expiry_date_sql

                    WHERE id = $id";


            if (mysqli_query($conn, $sql)) {

                header("Location: coupons.php?updated=1");
                exit;

            } else {

                $error = "Unable to update coupon.";
            }
        }
    }
}


/* =========================
   GET COUPON
========================= */

$result = mysqli_query(
    $conn,
    "SELECT *
     FROM coupons
     WHERE id = $id
     LIMIT 1"
);


if (!$result || mysqli_num_rows($result) == 0) {

    header("Location: coupons.php");
    exit;
}


$coupon = mysqli_fetch_assoc($result);


/* =========================
   HEADER
========================= */

include "../includes/header.php";
include "../includes/navbar.php";

?>

<link rel="stylesheet"
      href="../assets/css/admin-coupons.css">


<div class="admin-page">


    <!-- =========================
         HEADER
    ========================== -->

    <div class="admin-page-header">

        <a href="coupons.php"
           class="admin-back-btn">

            <i class="fas fa-arrow-left"></i>

            Back to Coupons

        </a>


        <h1>
            Edit Coupon
        </h1>

        <p>
            Update your promotional discount code.
        </p>

    </div>


    <!-- =========================
         ERROR
    ========================== -->

    <?php if (isset($error) && $error != "") { ?>

        <div class="admin-error">

            <i class="fas fa-circle-exclamation"></i>

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php } ?>


    <!-- =========================
         EDIT CARD
    ========================== -->

    <div class="coupon-form-card">


        <div class="coupon-form-title">

            <div class="coupon-title-icon">

                <i class="fas fa-pen"></i>

            </div>


            <div>

                <h2>
                    Edit Coupon
                </h2>

                <p>
                    Change the coupon details below.
                </p>

            </div>

        </div>


        <form method="POST">


            <div class="coupon-form-grid">


                <!-- Coupon Code -->

                <div class="coupon-field">

                    <label>
                        Coupon Code
                    </label>

                    <input
                        type="text"
                        name="coupon_code"
                        maxlength="50"
                        value="<?php
                        echo htmlspecialchars(
                            $coupon['coupon_code']
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- Discount Type -->

                <div class="coupon-field">

                    <label>
                        Discount Type
                    </label>

                    <select
                        name="discount_type"
                        required
                    >

                        <option
                            value="percentage"
                            <?php
                            if (
                                $coupon['discount_type']
                                ==
                                'percentage'
                            ) {
                                echo 'selected';
                            }
                            ?>
                        >
                            Percentage (%)
                        </option>


                        <option
                            value="fixed"
                            <?php
                            if (
                                $coupon['discount_type']
                                ==
                                'fixed'
                            ) {
                                echo 'selected';
                            }
                            ?>
                        >
                            Fixed Amount (₹)
                        </option>

                    </select>

                </div>


                <!-- Discount Value -->

                <div class="coupon-field">

                    <label>
                        Discount Value
                    </label>

                    <input
                        type="number"
                        name="discount_value"
                        min="1"
                        step="0.01"
                        value="<?php
                        echo htmlspecialchars(
                            $coupon['discount_value']
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- Minimum Amount -->

                <div class="coupon-field">

                    <label>
                        Minimum Booking Amount
                    </label>

                    <input
                        type="number"
                        name="min_amount"
                        min="0"
                        step="0.01"
                        value="<?php
                        echo htmlspecialchars(
                            $coupon['min_amount']
                        );
                        ?>"
                    >

                </div>


                <!-- Maximum Discount -->

                <div class="coupon-field">

                    <label>
                        Maximum Discount
                    </label>

                    <input
                        type="number"
                        name="max_discount"
                        min="0"
                        step="0.01"
                        value="<?php

                        if (
                            $coupon['max_discount']
                            !== null
                        ) {

                            echo htmlspecialchars(
                                $coupon['max_discount']
                            );
                        }

                        ?>"
                        placeholder="Optional"
                    >

                </div>


                <!-- Start Date -->

                <div class="coupon-field">

                    <label>
                        Start Date
                    </label>

                    <input
                        type="date"
                        name="start_date"
                        value="<?php

                        if (
                            !empty(
                                $coupon['start_date']
                            )
                        ) {

                            echo htmlspecialchars(
                                $coupon['start_date']
                            );
                        }

                        ?>"
                    >

                </div>


                <!-- Expiry Date -->

                <div class="coupon-field">

                    <label>
                        Expiry Date
                    </label>

                    <input
                        type="date"
                        name="expiry_date"
                        value="<?php

                        if (
                            !empty(
                                $coupon['expiry_date']
                            )
                        ) {

                            echo htmlspecialchars(
                                $coupon['expiry_date']
                            );
                        }

                        ?>"
                    >

                </div>


            </div>


            <!-- =========================
                 BUTTONS
            ========================== -->

            <div class="coupon-edit-actions">


                <a
                    href="coupons.php"
                    class="coupon-cancel-btn"
                >

                    Cancel

                </a>


                <button
                    type="submit"
                    name="update_coupon"
                    class="add-coupon-btn"
                >

                    <i class="fas fa-save"></i>

                    Update Coupon

                </button>


            </div>


        </form>

    </div>

</div>


<?php

include "../includes/footer.php";

?>