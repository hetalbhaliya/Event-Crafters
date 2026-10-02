<?php

session_start();

/* =========================================
   LOGIN CHECK
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
   EVENT ID
========================================= */

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($event_id <= 0) {
    header("Location: ../events.php");
    exit;
}


/* =========================================
   EVENT DETAILS
========================================= */

$event_query = mysqli_query(
    $conn,
    "SELECT
        id,
        event_name,
        category,
        subcategory,
        event_date,
        event_time,
        venue,
        price,
        image
     FROM events
     WHERE id = $event_id
     LIMIT 1"
);

if (!$event_query || mysqli_num_rows($event_query) == 0) {
    header("Location: ../events.php");
    exit;
}

$event = mysqli_fetch_assoc($event_query);


/* =========================================
   FOOD ITEMS
========================================= */

$food_query = mysqli_query(
    $conn,
    "SELECT
        id,
        food_name,
        description,
        price,
        image
     FROM food_items
     WHERE status = 1
     ORDER BY id DESC"
);


/* =========================================
   HEADER
========================================= */

include "../includes/header.php";

?>

<link
    rel="stylesheet"
    href="../assets/css/booking.css"
>


<div class="booking-page">

    <div class="booking-container">


        <!-- =====================================
             BACK BUTTON
        ====================================== -->

        <a
            href="../event-details.php?id=<?php echo (int)$event['id']; ?>"
            class="booking-back-btn"
        >

            <i class="fas fa-arrow-left"></i>

            Back to Event

        </a>


        <!-- =====================================
             PAGE HEADER
        ====================================== -->

        <div class="booking-header">

            <span>
                EVENT CRAFTERS
            </span>

            <h1>
                Book Your Event
            </h1>

            <p>
                Complete the details below to confirm your event booking.
            </p>

        </div>


        <!-- =====================================
             EVENT INFORMATION
        ====================================== -->

        <div class="booking-event-card">


            <!-- EVENT IMAGE -->

            <div class="booking-event-image">

                <?php if (!empty($event['image'])) { ?>

                    <img
                        src="../uploads/events/<?php echo htmlspecialchars($event['image']); ?>"
                        alt="<?php echo htmlspecialchars($event['event_name']); ?>"
                    >

                <?php } else { ?>

                    <img
                        src="../assets/images/default-event.jpg"
                        alt="Event"
                    >

                <?php } ?>

            </div>


            <!-- EVENT INFO -->

            <div class="booking-event-info">

                <div class="event-category">

                    <?php
                    echo htmlspecialchars($event['category']);
                    ?>

                    <?php if (!empty($event['subcategory'])) { ?>

                        &nbsp; / &nbsp;

                        <?php
                        echo htmlspecialchars($event['subcategory']);
                        ?>

                    <?php } ?>

                </div>


                <h2>
                    <?php
                    echo htmlspecialchars($event['event_name']);
                    ?>
                </h2>


                <p>

                    <i class="fas fa-calendar-days"></i>

                    <?php
                    echo date(
                        "d M Y",
                        strtotime($event['event_date'])
                    );
                    ?>

                </p>


                <p>

                    <i class="fas fa-clock"></i>

                    <?php
                    echo date(
                        "h:i A",
                        strtotime($event['event_time'])
                    );
                    ?>

                </p>


                <p>

                    <i class="fas fa-location-dot"></i>

                    <?php
                    echo htmlspecialchars($event['venue']);
                    ?>

                </p>


                <div class="booking-event-price">

                    ₹<?php
                    echo number_format(
                        (float)$event['price'],
                        2
                    );
                    ?>

                    <span>
                        / package
                    </span>

                </div>

            </div>

        </div>


        <!-- =====================================
             BOOKING FORM
        ====================================== -->

        <form
            action="booking_action.php"
            method="POST"
            id="bookingForm"
        >


            <!-- EVENT ID -->

            <input
                type="hidden"
                name="event_id"
                value="<?php echo (int)$event['id']; ?>"
            >


            <!-- =================================
                 BOOKING DETAILS
            ================================== -->

            <div class="booking-form-card">

                <div class="booking-section">

                    <div class="selection-heading">

                        <span>
                            BOOKING DETAILS
                        </span>

                        <h2>
                            Event Information
                        </h2>

                        <p>
                            Enter the number of people attending your event.
                        </p>

                    </div>


                    <!-- TOTAL PERSON -->

                    <div class="booking-form-group">

                        <label for="total_person">
                            Number of Persons
                        </label>

                        <div class="person-input">

                            <input
                                type="number"
                                id="total_person"
                                name="total_person"
                                value="1"
                                min="1"
                                max="100000"
                                required
                            >

                            <span class="person-label">
                                Persons
                            </span>

                        </div>

                    </div>

                </div>


                <!-- =================================
                     FOOD SELECTION
                ================================== -->

                <div class="booking-section">

                    <div class="selection-heading">

                        <span>
                            FOOD
                        </span>

                        <h2>
                            Choose Your Food
                        </h2>

                        <p>
                            Select food items for your event.
                            Food prices are calculated per plate.
                        </p>

                    </div>


                    <div class="food-selection">


                        <?php

                        if (
                            $food_query &&
                            mysqli_num_rows($food_query) > 0
                        ) {

                            while (
                                $food =
                                mysqli_fetch_assoc($food_query)
                            ) {

                        ?>


                            <!-- FOOD CARD -->

                            <div class="food-card">


                                <!-- ROUND CHECKBOX -->

                                <input
                                    type="checkbox"
                                    name="food[]"
                                    value="<?php echo (int)$food['id']; ?>"
                                    class="food-checkbox"
                                    data-price="<?php echo (float)$food['price']; ?>"
                                >


                                <!-- FOOD IMAGE -->

                                <div class="food-image">

                                    <?php if (!empty($food['image'])) { ?>

                                        <img
                                            src="../uploads/events/<?php echo htmlspecialchars($food['image']); ?>"
                                            alt="<?php echo htmlspecialchars($food['food_name']); ?>"
                                        >

                                    <?php } else { ?>

                                        <img
                                            src="../assets/images/default-food.jpg"
                                            alt="Food"
                                        >

                                    <?php } ?>

                                </div>


                                <!-- FOOD CONTENT -->

                                <div class="food-content">

                                    <h3>

                                        <?php
                                        echo htmlspecialchars(
                                            $food['food_name']
                                        );
                                        ?>

                                    </h3>


                                    <p>

                                        <?php
                                        echo htmlspecialchars(
                                            $food['description'] ?? ''
                                        );
                                        ?>

                                    </p>


                                    <div class="food-price">

                                        ₹<?php
                                        echo number_format(
                                            (float)$food['price'],
                                            2
                                        );
                                        ?>

                                        <span>
                                            / plate
                                        </span>

                                    </div>

                                </div>

                            </div>


                        <?php

                            }

                        } else {

                        ?>


                            <div class="booking-login-message">

                                <i class="fas fa-utensils"></i>

                                <h2>
                                    No Food Available
                                </h2>

                                <p>
                                    Food items are currently not available.
                                </p>

                            </div>


                        <?php

                        }

                        ?>


                    </div>

                </div>


                <!-- =================================
                     COUPON
                ================================== -->

                <div class="booking-section">

                    <div class="selection-heading">

                        <span>
                            OFFER
                        </span>

                        <h2>
                            Apply Coupon
                        </h2>

                        <p>
                            Have a coupon code? Apply it here.
                        </p>

                    </div>


                    <div class="coupon-section">

                        <div class="coupon-form">

                            <input
                                type="text"
                                id="coupon_code"
                                placeholder="Enter coupon code"
                                autocomplete="off"
                            >


                            <button
                                type="button"
                                id="applyCoupon"
                            >

                                Apply

                            </button>

                        </div>


                        <div
                            id="couponMessage"
                            class="coupon-message"
                        ></div>


                        <input
                            type="hidden"
                            name="applied_coupon"
                            id="applied_coupon"
                            value=""
                        >

                    </div>

                </div>


                <!-- =================================
                     TOTAL SUMMARY
                ================================== -->

                <div class="booking-total-card">


                    <!-- EVENT PACKAGE -->

                    <div class="total-row">

                        <span>
                            Event Package
                        </span>

                        <strong id="eventTotal">

                            ₹<?php
                            echo number_format(
                                (float)$event['price'],
                                2
                            );
                            ?>

                        </strong>

                    </div>


                    <!-- FOOD TOTAL -->

                    <div class="total-row">

                        <span>
                            Food Total
                        </span>

                        <strong id="foodTotal">
                            ₹0.00
                        </strong>

                    </div>


                    <!-- COUPON DISCOUNT -->

                    <div
                        class="total-row discount-row"
                        id="discountRow"
                        style="display:none;"
                    >

                        <span>
                            Coupon Discount
                        </span>

                        <strong id="discountAmount">
                            -₹0.00
                        </strong>

                    </div>


                    <!-- GRAND TOTAL -->

                    <div class="grand-total">

                        <span>
                            Grand Total
                        </span>

                        <strong id="grandTotal">

                            ₹<?php
                            echo number_format(
                                (float)$event['price'],
                                2
                            );
                            ?>

                        </strong>

                    </div>


                    <!-- TOTAL AMOUNT FOR DATABASE -->

                    <input
                        type="hidden"
                        name="total_amount"
                        id="total_amount"
                        value="<?php echo (float)$event['price']; ?>"
                    >


                    <!-- CONFIRM BUTTON -->

                    <button
                        type="submit"
                        name="confirm_booking"
                        class="confirm-booking-btn"
                    >

                        <i class="fas fa-calendar-check"></i>

                        Confirm Booking

                    </button>


                </div>


            </div>

        </form>

    </div>

</div>


<!-- =========================================
     BOOKING JAVASCRIPT
========================================= -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        /* =====================================
           EVENT PACKAGE PRICE
        ====================================== */

        const eventPrice =
            <?php echo (float)$event['price']; ?>;


        /* =====================================
           MAX PERSON LIMIT
        ====================================== */

        const MAX_PERSONS = 100000;


        /* =====================================
           ELEMENTS
        ====================================== */

        const personInput =
            document.getElementById(
                "total_person"
            );


        const foodCheckboxes =
            document.querySelectorAll(
                ".food-checkbox"
            );


        const eventTotal =
            document.getElementById(
                "eventTotal"
            );


        const foodTotal =
            document.getElementById(
                "foodTotal"
            );


        const grandTotal =
            document.getElementById(
                "grandTotal"
            );


        const totalAmount =
            document.getElementById(
                "total_amount"
            );


        const couponCode =
            document.getElementById(
                "coupon_code"
            );


        const appliedCoupon =
            document.getElementById(
                "applied_coupon"
            );


        const couponMessage =
            document.getElementById(
                "couponMessage"
            );


        const discountRow =
            document.getElementById(
                "discountRow"
            );


        const discountAmount =
            document.getElementById(
                "discountAmount"
            );


        const applyCoupon =
            document.getElementById(
                "applyCoupon"
            );


        /* =====================================
           COUPON DISCOUNT
        ====================================== */

        let couponDiscount = 0;


        /* =====================================
           MONEY FORMAT
        ====================================== */

        function formatMoney(amount) {

            return "₹" +
                Number(amount).toFixed(2);

        }


        /* =====================================
           FOOD TOTAL
        ====================================== */

        function getFoodTotal() {

            let total = 0;

            const persons =
                parseInt(
                    personInput.value
                ) || 1;


            foodCheckboxes.forEach(
                function (checkbox) {

                    if (checkbox.checked) {

                        const price =
                            parseFloat(
                                checkbox.dataset.price
                            ) || 0;


                        /*
                         * FOOD PRICE × PERSONS
                         */

                        total +=
                            price * persons;

                    }

                }
            );


            return total;

        }


        /* =====================================
           UPDATE TOTAL
        ====================================== */

        function updateTotal() {


            let persons =
                parseInt(
                    personInput.value
                ) || 1;


            /* ================================
               PERSON VALIDATION
            ================================= */

            if (persons < 1) {

                persons = 1;

                personInput.value = 1;

            }


            if (persons > MAX_PERSONS) {

                persons = MAX_PERSONS;

                personInput.value =
                    MAX_PERSONS;

            }


            /*
             * EVENT PRICE IS FIXED PACKAGE
             */

            const eventAmount =
                eventPrice;


            /*
             * FOOD PRICE × PERSONS
             */

            const foodAmount =
                getFoodTotal();


            /*
             * SUBTOTAL
             */

            const subtotal =
                eventAmount +
                foodAmount;


            /*
             * DISCOUNT LIMIT
             */

            if (
                couponDiscount >
                subtotal
            ) {

                couponDiscount =
                    subtotal;

            }


            /*
             * FINAL TOTAL
             */

            const finalAmount =
                Math.max(
                    0,
                    subtotal -
                    couponDiscount
                );


            /*
             * DISPLAY EVENT
             */

            eventTotal.textContent =
                formatMoney(
                    eventAmount
                );


            /*
             * DISPLAY FOOD
             */

            foodTotal.textContent =
                formatMoney(
                    foodAmount
                );


            /*
             * DISPLAY GRAND TOTAL
             */

            grandTotal.textContent =
                formatMoney(
                    finalAmount
                );


            /*
             * HIDDEN TOTAL
             */

            totalAmount.value =
                finalAmount.toFixed(2);


            /*
             * DISCOUNT DISPLAY
             */

            if (
                couponDiscount > 0
            ) {

                discountRow.style.display =
                    "flex";


                discountAmount.textContent =
                    "-" +
                    formatMoney(
                        couponDiscount
                    );

            } else {

                discountRow.style.display =
                    "none";


                discountAmount.textContent =
                    "-₹0.00";

            }

        }


        /* =====================================
           RESET COUPON
        ====================================== */

        function resetCoupon() {

            couponDiscount = 0;

            appliedCoupon.value = "";

            couponMessage.textContent = "";

            couponCode.value = "";

            updateTotal();

        }


        /* =====================================
           PERSON CHANGE
        ====================================== */

        personInput.addEventListener(
            "input",
            function () {


                let persons =
                    parseInt(
                        personInput.value
                    ) || 1;


                if (persons < 1) {

                    personInput.value = 1;

                }


                if (persons > MAX_PERSONS) {

                    personInput.value =
                        MAX_PERSONS;

                }


                /*
                 * Person count changes
                 * food total, so coupon reset.
                 */

                couponDiscount = 0;

                appliedCoupon.value = "";

                couponMessage.textContent = "";

                updateTotal();

            }
        );


        /* =====================================
           FOOD CHANGE
        ====================================== */

        foodCheckboxes.forEach(
            function (checkbox) {

                checkbox.addEventListener(
                    "change",
                    function () {


                        /*
                         * Food selection changes
                         * subtotal, so coupon reset.
                         */

                        couponDiscount = 0;

                        appliedCoupon.value = "";

                        couponMessage.textContent = "";

                        updateTotal();

                    }
                );

            }
        );


        /* =====================================
           APPLY COUPON
        ====================================== */

        applyCoupon.addEventListener(
            "click",
            function () {


                const code =
                    couponCode.value.trim();


                if (code === "") {

                    couponMessage.textContent =
                        "Please enter a coupon code.";

                    couponMessage.style.color =
                        "#ff6b6b";

                    return;

                }


                /* =================================
                   CURRENT SUBTOTAL
                ================================== */

                const eventAmount =
                    eventPrice;


                const foodAmount =
                    getFoodTotal();


                const subtotal =
                    eventAmount +
                    foodAmount;


                if (subtotal <= 0) {

                    couponMessage.textContent =
                        "Invalid booking amount.";

                    couponMessage.style.color =
                        "#ff6b6b";

                    return;

                }


                /* =================================
                   AJAX DATA
                ================================== */

                const formData =
                    new URLSearchParams();


                formData.append(
                    "coupon_code",
                    code
                );


                /*
                 * validate_coupon.php expects
                 * $_POST['subtotal']
                 */

                formData.append(
                    "subtotal",
                    subtotal.toFixed(2)
                );


                /* =================================
                   BUTTON STATE
                ================================== */

                applyCoupon.disabled = true;

                applyCoupon.textContent =
                    "Checking...";


                /* =================================
                   VALIDATE COUPON
                ================================== */

                fetch(
                    "validate_coupon.php",
                    {
                        method: "POST",

                        headers: {
                            "Content-Type":
                                "application/x-www-form-urlencoded"
                        },

                        body:
                            formData.toString()
                    }
                )
                .then(
                    function (response) {

                        if (!response.ok) {

                            throw new Error(
                                "Server error"
                            );

                        }


                        return response.json();

                    }
                )
                .then(
                    function (data) {


                        if (data.success) {


                            couponDiscount =
                                parseFloat(
                                    data.discount
                                ) || 0;


                            appliedCoupon.value =
                                data.coupon_code ||
                                code;


                            couponMessage.textContent =
                                data.message ||
                                "Coupon applied successfully.";


                            couponMessage.style.color =
                                "#35D6FF";


                            updateTotal();


                        } else {


                            couponDiscount = 0;

                            appliedCoupon.value = "";


                            couponMessage.textContent =
                                data.message ||
                                "Invalid coupon code.";


                            couponMessage.style.color =
                                "#ff6b6b";


                            updateTotal();

                        }

                    }
                )
                .catch(
                    function (error) {


                        console.error(
                            "Coupon Error:",
                            error
                        );


                        couponDiscount = 0;

                        appliedCoupon.value = "";


                        couponMessage.textContent =
                            "Unable to validate coupon. Please try again.";


                        couponMessage.style.color =
                            "#ff6b6b";


                        updateTotal();

                    }
                )
                .finally(
                    function () {

                        applyCoupon.disabled = false;

                        applyCoupon.textContent =
                            "Apply";

                    }
                );

            }
        );


        /* =====================================
           ENTER KEY FOR COUPON
        ====================================== */

        couponCode.addEventListener(
            "keydown",
            function (event) {

                if (event.key === "Enter") {

                    event.preventDefault();

                    applyCoupon.click();

                }

            }
        );


        /* =====================================
           INITIAL TOTAL
        ====================================== */

        updateTotal();

    }
);

</script>


<?php

include "../includes/footer.php";

?>