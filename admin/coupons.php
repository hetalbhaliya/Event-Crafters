<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

include "../config/db.php";

$message = "";
$error = "";


/* =====================================================
   DELETE COUPON
===================================================== */

if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $sql = "DELETE FROM coupons WHERE id = $id";

    if (mysqli_query($conn, $sql)) {

        header("Location: coupons.php?deleted=1");
        exit;

    } else {

        $error = "Unable to delete coupon.";
    }
}


/* =====================================================
   TOGGLE COUPON STATUS
===================================================== */

if (isset($_GET['toggle'])) {

    $id = (int) $_GET['toggle'];

    $sql = "UPDATE coupons
            SET status = CASE
                WHEN status = 1 THEN 0
                ELSE 1
            END
            WHERE id = $id";

    if (mysqli_query($conn, $sql)) {

        header("Location: coupons.php?updated=1");
        exit;

    } else {

        $error = "Unable to update coupon status.";
    }
}


/* =====================================================
   ADD COUPON
===================================================== */

if (isset($_POST['add_coupon'])) {

    $coupon_code = strtoupper(
        trim($_POST['coupon_code'] ?? '')
    );

    $discount_type = $_POST['discount_type'] ?? 'percentage';

    $discount_value = (float)(
        $_POST['discount_value'] ?? 0
    );

    $min_amount = (float)(
        $_POST['min_amount'] ?? 0
    );

    $max_discount = trim(
        $_POST['max_discount'] ?? ''
    );

    $start_date = trim(
        $_POST['start_date'] ?? ''
    );

    $expiry_date = trim(
        $_POST['expiry_date'] ?? ''
    );


    /* ---------------------------------------------
       MAXIMUM DISCOUNT
    --------------------------------------------- */

    if ($max_discount === "") {

        $max_discount_sql = "NULL";

    } else {

        $max_discount_sql = (float)$max_discount;
    }


    /* ---------------------------------------------
       START DATE
    --------------------------------------------- */

    if ($start_date === "") {

        $start_date_sql = "NULL";

    } else {

        $start_date_safe = mysqli_real_escape_string(
            $conn,
            $start_date
        );

        $start_date_sql = "'$start_date_safe'";
    }


    /* ---------------------------------------------
       EXPIRY DATE
    --------------------------------------------- */

    if ($expiry_date === "") {

        $expiry_date_sql = "NULL";

    } else {

        $expiry_date_safe = mysqli_real_escape_string(
            $conn,
            $expiry_date
        );

        $expiry_date_sql = "'$expiry_date_safe'";
    }


    /* ---------------------------------------------
       VALIDATION
    --------------------------------------------- */

    if ($coupon_code === "") {

        $error = "Coupon code is required.";

    } elseif ($discount_value <= 0) {

        $error = "Discount value must be greater than 0.";

    } elseif (
        $discount_type === "percentage"
        &&
        $discount_value > 100
    ) {

        $error = "Percentage discount cannot be more than 100%.";

    } elseif ($min_amount < 0) {

        $error = "Minimum booking amount cannot be negative.";

    } elseif (
        $max_discount !== ""
        &&
        (float)$max_discount < 0
    ) {

        $error = "Maximum discount cannot be negative.";

    } elseif (
        $start_date !== ""
        &&
        $expiry_date !== ""
        &&
        $expiry_date < $start_date
    ) {

        $error = "Expiry date cannot be before start date.";

    } elseif (
        !in_array(
            $discount_type,
            ['percentage', 'fixed'],
            true
        )
    ) {

        $error = "Invalid discount type.";

    } else {


        /* -----------------------------------------
           SAFE VALUES
        ----------------------------------------- */

        $coupon_code_safe = mysqli_real_escape_string(
            $conn,
            $coupon_code
        );

        $discount_type_safe = mysqli_real_escape_string(
            $conn,
            $discount_type
        );


        /* -----------------------------------------
           CHECK DUPLICATE COUPON
        ----------------------------------------- */

        $check = mysqli_query(
            $conn,
            "SELECT id
             FROM coupons
             WHERE coupon_code = '$coupon_code_safe'
             LIMIT 1"
        );


        if (!$check) {

            $error = "Unable to check coupon.";

        } elseif (mysqli_num_rows($check) > 0) {

            $error = "Coupon code already exists.";

        } else {


            /* -------------------------------------
               INSERT COUPON
            ------------------------------------- */

            $sql = "INSERT INTO coupons
                    (
                        coupon_code,
                        discount_type,
                        discount_value,
                        min_amount,
                        max_discount,
                        status,
                        start_date,
                        expiry_date
                    )
                    VALUES
                    (
                        '$coupon_code_safe',
                        '$discount_type_safe',
                        $discount_value,
                        $min_amount,
                        $max_discount_sql,
                        1,
                        $start_date_sql,
                        $expiry_date_sql
                    )";


            if (mysqli_query($conn, $sql)) {

                header("Location: coupons.php?added=1");
                exit;

            } else {

                $error = "Unable to add coupon.";
            }
        }
    }
}


/* =====================================================
   SUCCESS MESSAGES
===================================================== */

if (isset($_GET['added'])) {

    $message = "Coupon added successfully.";
}

if (isset($_GET['updated'])) {

    $message = "Coupon status updated.";
}

if (isset($_GET['deleted'])) {

    $message = "Coupon deleted successfully.";
}


/* =====================================================
   GET ALL COUPONS
===================================================== */

$result = mysqli_query(
    $conn,
    "SELECT *
     FROM coupons
     ORDER BY id DESC"
);


/* =====================================================
   ADMIN HEADER ONLY
===================================================== */

/*
   IMPORTANT:
   User-side navbar intentionally removed.

   DO NOT include:
   include "../includes/navbar.php";
*/

include "../includes/header.php";

?>


<!-- =====================================================
     ADMIN COUPON CSS
===================================================== -->

<link
    rel="stylesheet"
    href="../assets/css/admin-coupons.css"
>


<div class="admin-page">


    <!-- =================================================
         PAGE HEADER
    ================================================== -->

    <div class="admin-page-header">

        <div>


            <!-- BACK BUTTON -->

            <a
                href="dashboard.php"
                class="back-btn"
            >

                <i class="fas fa-arrow-left"></i>

                Back to Dashboard

            </a>


            <!-- PAGE TITLE -->

            <h1>
                Coupon Management
            </h1>


            <p>
                Create and manage promotional discount codes.
            </p>


        </div>

    </div>



    <!-- =================================================
         SUCCESS MESSAGE
    ================================================== -->

    <?php if ($message != "") { ?>

        <div class="admin-success">

            <i class="fas fa-circle-check"></i>

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php } ?>



    <!-- =================================================
         ERROR MESSAGE
    ================================================== -->

    <?php if ($error != "") { ?>

        <div class="admin-error">

            <i class="fas fa-circle-exclamation"></i>

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php } ?>



    <!-- =================================================
         ADD COUPON CARD
    ================================================== -->

    <div class="coupon-form-card">


        <!-- FORM TITLE -->

        <div class="coupon-form-title">

            <div class="coupon-title-icon">

                <i class="fas fa-ticket"></i>

            </div>


            <div>

                <h2>
                    Add New Coupon
                </h2>

                <p>
                    Create a promotional code for customers.
                </p>

            </div>

        </div>



        <!-- FORM -->

        <form method="POST">


            <div class="coupon-form-grid">


                <!-- =====================================
                     COUPON CODE
                ====================================== -->

                <div class="coupon-field">

                    <label>
                        Coupon Code
                    </label>

                    <input
                        type="text"
                        name="coupon_code"
                        placeholder="Example: EVENT10"
                        maxlength="50"
                        required
                    >

                </div>



                <!-- =====================================
                     DISCOUNT TYPE
                ====================================== -->

                <div class="coupon-field">

                    <label>
                        Discount Type
                    </label>

                    <select
                        name="discount_type"
                        required
                    >

                        <option value="percentage">
                            Percentage (%)
                        </option>

                        <option value="fixed">
                            Fixed Amount (₹)
                        </option>

                    </select>

                </div>



                <!-- =====================================
                     DISCOUNT VALUE
                ====================================== -->

                <div class="coupon-field">

                    <label>
                        Discount Value
                    </label>

                    <input
                        type="number"
                        name="discount_value"
                        placeholder="Example: 10"
                        min="1"
                        step="0.01"
                        required
                    >

                </div>



                <!-- =====================================
                     MINIMUM AMOUNT
                ====================================== -->

                <div class="coupon-field">

                    <label>
                        Minimum Booking Amount
                    </label>

                    <input
                        type="number"
                        name="min_amount"
                        placeholder="Example: 10000"
                        min="0"
                        step="0.01"
                        value="0"
                    >

                </div>



                <!-- =====================================
                     MAXIMUM DISCOUNT
                ====================================== -->

                <div class="coupon-field">

                    <label>
                        Maximum Discount
                    </label>

                    <input
                        type="number"
                        name="max_discount"
                        placeholder="Optional"
                        min="0"
                        step="0.01"
                    >

                </div>



                <!-- =====================================
                     START DATE
                ====================================== -->

                <div class="coupon-field">

                    <label>
                        Start Date
                    </label>

                    <input
                        type="date"
                        name="start_date"
                    >

                </div>



                <!-- =====================================
                     EXPIRY DATE
                ====================================== -->

                <div class="coupon-field">

                    <label>
                        Expiry Date
                    </label>

                    <input
                        type="date"
                        name="expiry_date"
                    >

                </div>


            </div>



            <!-- ADD BUTTON -->

            <button
                type="submit"
                name="add_coupon"
                class="add-coupon-btn"
            >

                <i class="fas fa-plus"></i>

                Add Coupon

            </button>


        </form>

    </div>



    <!-- =================================================
         ALL COUPONS
    ================================================== -->

    <div class="coupon-list-card">


        <!-- LIST HEADER -->

        <div class="coupon-list-header">

            <div>

                <h2>
                    All Coupons
                </h2>

                <p>
                    Manage your promotional codes.
                </p>

            </div>


            <div class="coupon-count">

                <?php

                if ($result) {

                    echo mysqli_num_rows($result);

                } else {

                    echo 0;
                }

                ?>

                Coupons

            </div>

        </div>



        <!-- =================================================
             TABLE
        ================================================== -->

        <div class="coupon-table-wrapper">


            <table class="coupon-table">


                <thead>

                    <tr>

                        <th>
                            Code
                        </th>

                        <th>
                            Discount
                        </th>

                        <th>
                            Minimum
                        </th>

                        <th>
                            Maximum
                        </th>

                        <th>
                            Validity
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
                    $result
                    &&
                    mysqli_num_rows($result) > 0
                ) {

                ?>


                    <?php while (
                        $coupon = mysqli_fetch_assoc($result)
                    ) { ?>


                        <tr>


                            <!-- =================================
                                 CODE
                            ================================== -->

                            <td>

                                <div class="coupon-code">

                                    <i class="fas fa-ticket"></i>

                                    <?php

                                    echo htmlspecialchars(
                                        $coupon['coupon_code']
                                    );

                                    ?>

                                </div>

                            </td>



                            <!-- =================================
                                 DISCOUNT
                            ================================== -->

                            <td>

                                <strong
                                    class="discount-value"
                                >

                                    <?php

                                    if (
                                        $coupon['discount_type']
                                        ===
                                        'percentage'
                                    ) {

                                        echo
                                            htmlspecialchars(
                                                $coupon[
                                                    'discount_value'
                                                ]
                                            )
                                            .
                                            "% OFF";

                                    } else {

                                        echo
                                            "₹"
                                            .
                                            number_format(
                                                $coupon[
                                                    'discount_value'
                                                ],
                                                0
                                            )
                                            .
                                            " OFF";
                                    }

                                    ?>

                                </strong>

                            </td>



                            <!-- =================================
                                 MINIMUM
                            ================================== -->

                            <td>

                                ₹<?php

                                echo number_format(
                                    $coupon['min_amount'],
                                    0
                                );

                                ?>

                            </td>



                            <!-- =================================
                                 MAXIMUM
                            ================================== -->

                            <td>

                                <?php

                                if (
                                    $coupon['max_discount']
                                    !==
                                    null
                                    &&
                                    $coupon['max_discount']
                                    !==
                                    ''
                                ) {

                                    echo
                                        "₹"
                                        .
                                        number_format(
                                            $coupon[
                                                'max_discount'
                                            ],
                                            0
                                        );

                                } else {

                                    echo "No Limit";
                                }

                                ?>

                            </td>



                            <!-- =================================
                                 VALIDITY
                            ================================== -->

                            <td>

                                <div class="validity">


                                    <?php

                                    if (
                                        !empty(
                                            $coupon['start_date']
                                        )
                                    ) {

                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $coupon[
                                                    'start_date'
                                                ]
                                            )
                                        );

                                    } else {

                                        echo "Anytime";
                                    }

                                    ?>


                                    <span>
                                        to
                                    </span>


                                    <?php

                                    if (
                                        !empty(
                                            $coupon['expiry_date']
                                        )
                                    ) {

                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $coupon[
                                                    'expiry_date'
                                                ]
                                            )
                                        );

                                    } else {

                                        echo "No Expiry";
                                    }

                                    ?>


                                </div>

                            </td>



                            <!-- =================================
                                 STATUS
                            ================================== -->

                            <td>


                                <?php

                                if (
                                    $coupon['status'] == 1
                                ) {

                                ?>


                                    <a
                                        href="coupons.php?toggle=<?php echo (int)$coupon['id']; ?>"
                                        class="coupon-status active"
                                        title="Click to deactivate"
                                    >

                                        <i class="fas fa-check"></i>

                                        Active

                                    </a>


                                <?php

                                } else {

                                ?>


                                    <a
                                        href="coupons.php?toggle=<?php echo (int)$coupon['id']; ?>"
                                        class="coupon-status inactive"
                                        title="Click to activate"
                                    >

                                        <i class="fas fa-ban"></i>

                                        Inactive

                                    </a>


                                <?php

                                }

                                ?>


                            </td>



                            <!-- =================================
                                 ACTION
                            ================================== -->

                            <td>


                                <div class="coupon-actions">


                                    <!-- EDIT -->

                                    <a
                                        href="edit-coupon.php?id=<?php echo (int)$coupon['id']; ?>"
                                        class="edit-coupon-btn"
                                        title="Edit Coupon"
                                    >

                                        <i class="fas fa-pen"></i>

                                    </a>



                                    <!-- DELETE -->

                                    <a
                                        href="coupons.php?delete=<?php echo (int)$coupon['id']; ?>"
                                        class="delete-coupon-btn"
                                        title="Delete Coupon"
                                        onclick="return confirm('Are you sure you want to delete this coupon?');"
                                    >

                                        <i class="fas fa-trash"></i>

                                    </a>


                                </div>


                            </td>


                        </tr>


                    <?php } ?>


                <?php

                } else {

                ?>


                    <!-- =================================
                         NO COUPONS
                    ================================== -->

                    <tr>

                        <td
                            colspan="7"
                            class="no-coupons"
                        >

                            <i class="fas fa-ticket"></i>

                            <h3>
                                No Coupons Yet
                            </h3>

                            <p>
                                Create your first promotional coupon above.
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