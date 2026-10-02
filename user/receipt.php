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
   USER ID
========================================= */

$user_id = (int) $_SESSION['user_id'];


/* =========================================
   BOOKING ID CHECK
========================================= */

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: my_bookings.php");
    exit;
}

$booking_id = (int) $_GET['id'];


/* =========================================
   GET BOOKING
========================================= */

$sql = "
    SELECT
        b.id AS booking_id,
        b.user_id,
        b.full_name,
        b.email,
        b.mobile,
        b.total_person,
        b.total_amount,
        b.status,
        b.booking_date,

        e.event_name,
        e.category,
        e.event_date,
        e.event_time,
        e.venue,
        e.price

    FROM bookings b

    INNER JOIN events e
        ON b.event_id = e.id

    WHERE b.id = $booking_id
    AND b.user_id = $user_id

    LIMIT 1
";


$result = mysqli_query($conn, $sql);


/* =========================================
   DATABASE ERROR
========================================= */

if (!$result) {
    die(
        "Database Error: " .
        htmlspecialchars(mysqli_error($conn))
    );
}


/* =========================================
   BOOKING NOT FOUND
========================================= */

if (mysqli_num_rows($result) == 0) {
    header("Location: my_bookings.php");
    exit;
}


$booking = mysqli_fetch_assoc($result);


/* =========================================
   GET FOOD ITEMS
========================================= */

$food_items = [];

$food_total = 0;

$food_sql = "
    SELECT
        bf.quantity,
        bf.price,
        fi.food_name

    FROM booking_food bf

    INNER JOIN food_items fi
        ON bf.food_id = fi.id

    WHERE bf.booking_id = $booking_id
";


$food_result = mysqli_query($conn, $food_sql);


if ($food_result) {

    while ($food = mysqli_fetch_assoc($food_result)) {

        $quantity = (int) $food['quantity'];

        $price = (float) $food['price'];

        $item_total = $quantity * $price;

        $food_total += $item_total;

        $food_items[] = [
            'name' => $food['food_name'],
            'quantity' => $quantity,
            'price' => $price,
            'total' => $item_total
        ];
    }
}


/* =========================================
   EVENT TOTAL
========================================= */

$ticket_price = (float) $booking['price'];

$total_person = (int) $booking['total_person'];

$event_total = $ticket_price * $total_person;


/* =========================================
   GRAND TOTAL
========================================= */

$grand_total = (float) $booking['total_amount'];


/* =========================================
   STATUS
========================================= */

$status = strtolower(
    trim($booking['status'])
);


/* =========================================
   FORMAT DATES
========================================= */

$event_date = "N/A";

if (!empty($booking['event_date'])) {

    $event_date = date(
        "d M Y",
        strtotime($booking['event_date'])
    );
}


$event_time = "N/A";

if (!empty($booking['event_time'])) {

    $event_time = date(
        "h:i A",
        strtotime($booking['event_time'])
    );
}


$booking_date = "N/A";

if (!empty($booking['booking_date'])) {

    $booking_date = date(
        "d M Y, h:i A",
        strtotime($booking['booking_date'])
    );
}


/* =========================================
   SAFE VALUES
========================================= */

$event_name = htmlspecialchars(
    $booking['event_name'] ?? ''
);

$category = htmlspecialchars(
    $booking['category'] ?? ''
);

$venue = htmlspecialchars(
    $booking['venue'] ?? ''
);

$full_name = htmlspecialchars(
    $booking['full_name'] ?? ''
);

$email = htmlspecialchars(
    $booking['email'] ?? ''
);

$mobile = htmlspecialchars(
    $booking['mobile'] ?? ''
);

$booking_status = htmlspecialchars(
    $booking['status'] ?? ''
);


/* =========================================
   HEADER
========================================= */

include "../includes/header.php";

include "../includes/navbar.php";

?>

<style>

    body {
        background: #0b1020;
        color: #ffffff;
    }

    .receipt-page {
        padding: 60px 20px;
        min-height: 100vh;
    }

    .receipt-container {
        max-width: 850px;
        margin: auto;
    }

    .receipt-top {
        text-align: center;
        margin-bottom: 30px;
    }

    .receipt-top span {
        color: #35d6ff;
        font-size: 13px;
        font-weight: bold;
        letter-spacing: 3px;
    }

    .receipt-top h1 {
        margin: 10px 0;
        font-size: 36px;
    }

    .receipt-top p {
        color: #9da9c7;
    }

    .receipt-card {
        background: rgba(18, 25, 45, 0.95);
        border: 1px solid rgba(53, 214, 255, 0.20);
        border-radius: 18px;
        padding: 35px;
    }

    .receipt-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 25px;
        border-bottom: 1px solid #27324d;
        margin-bottom: 25px;
    }

    .receipt-logo {
        color: #35d6ff;
        font-size: 24px;
        font-weight: bold;
    }

    .receipt-id {
        color: #aab5cc;
        text-align: right;
    }

    .receipt-section {
        margin-top: 28px;
    }

    .receipt-section-title {
        color: #35d6ff;
        font-size: 14px;
        font-weight: bold;
        letter-spacing: 1px;
        margin-bottom: 15px;
    }

    .receipt-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .receipt-item {
        background: #10182c;
        border-radius: 10px;
        padding: 14px;
    }

    .receipt-item span {
        display: block;
        color: #8793ad;
        font-size: 12px;
        margin-bottom: 5px;
    }

    .receipt-item strong {
        color: #ffffff;
        font-size: 14px;
    }

    .receipt-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .receipt-table th,
    .receipt-table td {
        padding: 13px 10px;
        border-bottom: 1px solid #27324d;
        text-align: left;
    }

    .receipt-table th {
        color: #8f9bb8;
        font-size: 12px;
        text-transform: uppercase;
    }

    .receipt-table td {
        color: #e5ebfa;
        font-size: 14px;
    }

    .receipt-table td:last-child,
    .receipt-table th:last-child {
        text-align: right;
    }

    .receipt-total {
        margin-top: 25px;
        padding: 20px;
        background: rgba(53, 214, 255, 0.08);
        border: 1px solid rgba(53, 214, 255, 0.20);
        border-radius: 12px;

        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .receipt-total span {
        color: #b8c3dc;
        font-size: 15px;
    }

    .receipt-total strong {
        color: #35d6ff;
        font-size: 28px;
    }

    .receipt-status {
        margin-top: 20px;
        text-align: center;
    }

    .receipt-status span {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 30px;
        font-weight: bold;
        text-transform: capitalize;
    }

    .receipt-status .confirmed {
        background: rgba(40, 200, 120, 0.12);
        color: #55df9b;
    }

    .receipt-status .pending {
        background: rgba(255, 190, 50, 0.12);
        color: #ffd166;
    }

    .receipt-status .cancelled {
        background: rgba(255, 70, 70, 0.12);
        color: #ff8c8c;
    }

    .receipt-actions {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 25px;
    }

    .receipt-btn {
        display: inline-block;
        padding: 12px 22px;
        border-radius: 9px;
        text-decoration: none;
        font-weight: bold;
    }

    .receipt-back {
        background: #26324d;
        color: #ffffff;
    }

    .receipt-print {
        background: #35d6ff;
        color: #07101d;
        border: none;
        cursor: pointer;
    }

    .receipt-btn:hover {
        text-decoration: none;
    }

    @media (max-width: 600px) {

        .receipt-card {
            padding: 20px;
        }

        .receipt-grid {
            grid-template-columns: 1fr;
        }

        .receipt-header {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }

        .receipt-id {
            text-align: center;
        }

        .receipt-actions {
            flex-direction: column;
        }

    }

    @media print {

        body {
            background: #ffffff;
            color: #000000;
        }

        .receipt-page {
            padding: 0;
        }

        .receipt-card {
            background: #ffffff;
            border: none;
            color: #000000;
        }

        .receipt-actions,
        .receipt-top {
            display: none;
        }

    }

</style>


<section class="receipt-page">

    <div class="receipt-container">


        <!-- PAGE HEADING -->

        <div class="receipt-top">

            <span>
                EVENT CRAFTERS
            </span>

            <h1>
                Booking Receipt
            </h1>

            <p>
                Your booking summary and payment details
            </p>

        </div>


        <!-- RECEIPT -->

        <div class="receipt-card">


            <!-- HEADER -->

            <div class="receipt-header">

                <div class="receipt-logo">
                    Event Crafters
                </div>

                <div class="receipt-id">

                    Booking ID:
                    <strong>
                        #<?php echo $booking_id; ?>
                    </strong>

                    <br>

                    <?php echo $booking_date; ?>

                </div>

            </div>


            <!-- CUSTOMER -->

            <div class="receipt-section">

                <div class="receipt-section-title">
                    CUSTOMER INFORMATION
                </div>

                <div class="receipt-grid">

                    <div class="receipt-item">

                        <span>
                            Full Name
                        </span>

                        <strong>
                            <?php echo $full_name; ?>
                        </strong>

                    </div>


                    <div class="receipt-item">

                        <span>
                            Email
                        </span>

                        <strong>
                            <?php echo $email; ?>
                        </strong>

                    </div>


                    <div class="receipt-item">

                        <span>
                            Mobile
                        </span>

                        <strong>
                            <?php echo $mobile; ?>
                        </strong>

                    </div>


                    <div class="receipt-item">

                        <span>
                            Total Persons
                        </span>

                        <strong>
                            <?php echo $total_person; ?>
                        ?>
                            Person(s)
                        </strong>

                    </div>

                </div>

            </div>


            <!-- EVENT -->

            <div class="receipt-section">

                <div class="receipt-section-title">
                    EVENT INFORMATION
                </div>

                <div class="receipt-grid">

                    <div class="receipt-item">

                        <span>
                            Event
                        </span>

                        <strong>
                            <?php echo $event_name; ?>
                        </strong>

                    </div>


                    <div class="receipt-item">

                        <span>
                            Category
                        </span>

                        <strong>
                            <?php echo $category; ?>
                        </strong>

                    </div>


                    <div class="receipt-item">

                        <span>
                            Event Date
                        </span>

                        <strong>
                            <?php echo $event_date; ?>
                        </strong>

                    </div>


                    <div class="receipt-item">

                        <span>
                            Event Time
                        </span>

                        <strong>
                            <?php echo $event_time; ?>
                        </strong>

                    </div>


                    <div class="receipt-item">

                        <span>
                            Venue
                        </span>

                        <strong>
                            <?php echo $venue; ?>
                        </strong>

                    </div>

                </div>

            </div>


            <!-- PAYMENT -->

            <div class="receipt-section">

                <div class="receipt-section-title">
                    PAYMENT DETAILS
                </div>

                <table class="receipt-table">

                    <thead>

                        <tr>

                            <th>
                                Description
                            </th>

                            <th>
                                Quantity
                            </th>

                            <th>
                                Price
                            </th>

                            <th>
                                Total
                            </th>

                        </tr>

                    </thead>

                    <tbody>


                        <!-- EVENT TICKET -->

                        <tr>

                            <td>
                                <?php echo $event_name; ?> Ticket
                            </td>

                            <td>
                                <?php echo $total_person; ?>
                            </td>

                            <td>
                                ₹<?php echo number_format($ticket_price); ?>
                            </td>

                            <td>
                                ₹<?php echo number_format($event_total); ?>
                            </td>

                        </tr>


                        <!-- FOOD -->

                        <?php foreach ($food_items as $food) { ?>

                            <tr>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $food['name']
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo $food['quantity'];
                                    ?>
                                </td>

                                <td>
                                    ₹<?php
                                    echo number_format(
                                        $food['price']
                                    );
                                    ?>
                                </td>

                                <td>
                                    ₹<?php
                                    echo number_format(
                                        $food['total']
                                    );
                                    ?>
                                </td>

                            </tr>

                        <?php } ?>


                        <!-- FOOD TOTAL -->

                        <?php if (count($food_items) > 0) { ?>

                            <tr>

                                <td colspan="3">
                                    <strong>
                                        Food Total
                                    </strong>
                                </td>

                                <td>

                                    <strong>
                                        ₹<?php
                                        echo number_format(
                                            $food_total
                                        );
                                        ?>
                                    </strong>

                                </td>

                            </tr>

                        <?php } ?>


                    </tbody>

                </table>


                <!-- GRAND TOTAL -->

                <div class="receipt-total">

                    <span>
                        Grand Total
                    </span>

                    <strong>
                        ₹<?php
                        echo number_format(
                            $grand_total
                        );
                        ?>
                    </strong>

                </div>


                <!-- STATUS -->

                <div class="receipt-status">

                    <span
                        class="<?php echo htmlspecialchars($status); ?>"
                    >

                        <?php

                        if ($status === "confirmed") {

                            echo "✓ ";

                        }
                        elseif ($status === "cancelled") {

                            echo "✕ ";

                        }
                        else {

                            echo "◷ ";

                        }

                        echo $booking_status;

                        ?>

                    </span>

                </div>

            </div>


            <!-- ACTIONS -->

            <div class="receipt-actions">

                <a
                    href="booking-details.php?id=<?php echo $booking_id; ?>"
                    class="receipt-btn receipt-back"
                >
                    ← Booking Details
                </a>


                <button
                    type="button"
                    onclick="window.print();"
                    class="receipt-btn receipt-print"
                >
                    🖨 Print Receipt
                </button>

            </div>


        </div>

    </div>

</section>


<?php

include "../includes/footer.php";

?>