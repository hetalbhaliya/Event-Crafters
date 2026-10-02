<?php

session_start();

include "config/db.php";


/* =====================================================
   TODAY'S DATE
===================================================== */

$today = date("Y-m-d");


/* =====================================================
   GET ACTIVE COUPONS
===================================================== */

$sql = "
    SELECT *
    FROM coupons
    WHERE status = 1
    AND (
        start_date IS NULL
        OR start_date = ''
        OR start_date <= '$today'
    )
    AND (
        expiry_date IS NULL
        OR expiry_date = ''
        OR expiry_date >= '$today'
    )
    ORDER BY id DESC
";

$result = mysqli_query($conn, $sql);


include "includes/header.php";
include "includes/navbar.php";

?>

<link
    rel="stylesheet"
    href="assets/css/coupons.css"
>


<!-- =====================================================
     COUPONS SECTION
===================================================== -->

<section class="coupons-section">


    <!-- =================================================
         HEADER
    ================================================== -->

    <div class="section-heading">

        <h1>
            AVAILABLE COUPONS
        </h1>

        <p>
            Save more on your special event with our exclusive offers.
        </p>

    </div>



    <!-- =================================================
         COUPONS CONTAINER
    ================================================== -->

    <div class="coupons-container">


        <?php

        if (
            $result &&
            mysqli_num_rows($result) > 0
        ) {

        ?>


            <?php

            while (
                $coupon = mysqli_fetch_assoc($result)
            ) {

            ?>


                <!-- =================================================
                     COUPON CARD
                ================================================== -->

                <div class="coupon-card">


                    <!-- LEFT SIDE -->

                    <div class="coupon-left">


                        <div class="coupon-icon">

                            <i class="fas fa-ticket"></i>

                        </div>


                        <div class="coupon-code-label">

                            COUPON CODE

                        </div>


                        <div class="coupon-code">

                            <?php

                            echo htmlspecialchars(
                                $coupon['coupon_code']
                            );

                            ?>

                        </div>


                    </div>



                    <!-- RIGHT SIDE -->

                    <div class="coupon-right">


                        <!-- DISCOUNT -->

                        <div class="coupon-discount">

                            <?php

                            if (
                                $coupon['discount_type']
                                ===
                                'percentage'
                            ) {

                                echo
                                    htmlspecialchars(
                                        $coupon['discount_value']
                                    )
                                    . "% OFF";

                            } else {

                                echo
                                    "₹"
                                    .
                                    number_format(
                                        $coupon['discount_value'],
                                        0
                                    )
                                    .
                                    " OFF";

                            }

                            ?>

                        </div>



                        <!-- MINIMUM AMOUNT -->

                        <p class="coupon-minimum">

                            <?php

                            if (
                                (float)$coupon['min_amount'] > 0
                            ) {

                                echo
                                    "Minimum booking amount: ₹"
                                    .
                                    number_format(
                                        $coupon['min_amount'],
                                        0
                                    );

                            } else {

                                echo
                                    "No minimum booking amount";

                            }

                            ?>

                        </p>



                        <!-- MAXIMUM DISCOUNT -->

                        <?php

                        if (
                            $coupon['max_discount']
                            !==
                            null
                            &&
                            $coupon['max_discount'] !== ''
                        ) {

                        ?>

                            <p class="coupon-maximum">

                                Maximum discount:
                                ₹<?php

                                echo number_format(
                                    $coupon['max_discount'],
                                    0
                                );

                                ?>

                            </p>

                        <?php

                        }

                        ?>



                        <!-- VALIDITY -->

                        <div class="coupon-validity">

                            <i class="far fa-calendar"></i>


                            <?php

                            if (
                                !empty(
                                    $coupon['start_date']
                                )
                            ) {

                                echo
                                    date(
                                        "d M Y",
                                        strtotime(
                                            $coupon['start_date']
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

                                echo
                                    date(
                                        "d M Y",
                                        strtotime(
                                            $coupon['expiry_date']
                                        )
                                    );

                            } else {

                                echo "No Expiry";

                            }

                            ?>

                        </div>



                        <!-- USE COUPON -->

                        <a
                            href="user/book.php?coupon=<?php echo urlencode($coupon['coupon_code']); ?>"
                            class="use-coupon-btn"
                        >

                            Use Coupon

                            <i class="fas fa-arrow-right"></i>

                        </a>


                    </div>


                </div>


            <?php

            }

            ?>


        <?php

        } else {

        ?>


            <!-- =================================================
                 EMPTY STATE
            ================================================== -->

            <div class="empty-coupons">


                <div class="empty-coupon-icon">

                    <i class="fas fa-ticket"></i>

                </div>


                <h3>

                    No Coupons Available

                </h3>


                <p>

                    There are currently no active coupons.
                    Please check again later.

                </p>


                <a
                    href="events.php"
                    class="explore-coupons-btn"
                >

                    Explore Events

                    <i class="fas fa-arrow-right"></i>

                </a>


            </div>


        <?php

        }

        ?>


    </div>

</section>



<?php

include "includes/footer.php";

?>