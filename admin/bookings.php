<?php

session_start();

/* =========================================
   ADMIN LOGIN CHECK
========================================= */

if(!isset($_SESSION['admin_id']))
{
    header("Location: login.php");
    exit;
}


/* =========================================
   DATABASE
========================================= */

include "../config/db.php";


/* =========================================
   GET BOOKINGS
========================================= */

$sql = "SELECT
            bookings.*,
            events.event_name,
            events.category,
            events.event_date,
            events.event_time,
            events.venue

        FROM bookings

        INNER JOIN events
        ON bookings.event_id = events.id

        ORDER BY bookings.id DESC";


$result = mysqli_query($conn, $sql);


/* =========================================
   HEADER
========================================= */

include "../includes/header.php";

?>

<link
    rel="stylesheet"
    href="../assets/css/admin-bookings.css"
>


<!-- =========================================
     BACK BUTTON
========================================= -->

<a
    href="dashboard.php"
    class="admin-back-btn"
>

    <i class="fas fa-arrow-left"></i>

    Back to Dashboard

</a>


<!-- =========================================
     BOOKINGS SECTION
========================================= -->

<section class="admin-bookings-section">

    <div class="admin-bookings-container">


        <!-- =====================================
             HEADING
        ====================================== -->

        <div class="admin-bookings-heading">

            <div>

                <span>
                    ADMIN PANEL
                </span>

                <h1>
                    Manage Bookings
                </h1>

                <p>
                    View and manage all event bookings.
                </p>

            </div>

        </div>


        <!-- =====================================
             BOOKINGS CARD
        ====================================== -->

        <div class="admin-bookings-card">

            <div class="bookings-table-wrapper">

                <table class="bookings-table">


                    <!-- =================================
                         TABLE HEAD
                    ================================== -->

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
                                Persons
                            </th>

                            <th>
                                Booking Date
                            </th>

                            <th>
                                Amount
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <!-- =================================
                         TABLE BODY
                    ================================== -->

                    <tbody>

                    <?php

                    if(
                        $result &&
                        mysqli_num_rows($result) > 0
                    )
                    {

                        $count = 1;


                        while(
                            $row =
                            mysqli_fetch_assoc($result)
                        )
                        {

                            $status =
                                strtolower(
                                    trim(
                                        $row['status']
                                    )
                                );

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

                                <div class="booking-user">

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $row['full_name']
                                        );
                                        ?>

                                    </strong>

                                    <small>

                                        <?php
                                        echo htmlspecialchars(
                                            $row['email']
                                        );
                                        ?>

                                    </small>

                                </div>

                            </td>


                            <!-- EVENT -->

                            <td>

                                <div class="booking-event">

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $row['event_name']
                                        );
                                        ?>

                                    </strong>

                                    <small>

                                        <?php
                                        echo htmlspecialchars(
                                            $row['category']
                                        );
                                        ?>

                                    </small>

                                </div>

                            </td>


                            <!-- PERSONS -->

                            <td>

                                <span class="booking-persons">

                                    <i class="fas fa-users"></i>

                                    <?php
                                    echo (int)
                                        $row['total_person'];
                                    ?>

                                </span>

                            </td>


                            <!-- BOOKING DATE -->

                            <td>

                                <?php

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $row['booking_date']
                                    )
                                );

                                ?>

                            </td>


                            <!-- AMOUNT -->

                            <td>

                                <strong
                                    class="booking-amount"
                                >

                                    ₹

                                    <?php

                                    echo number_format(
                                        (float)
                                        $row['total_amount']
                                    );

                                    ?>

                                </strong>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span
                                    class="
                                    booking-status
                                    <?php
                                    echo htmlspecialchars(
                                        $status
                                    );
                                    ?>
                                    "
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $row['status']
                                    );
                                    ?>

                                </span>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <a
                                    href="booking-action.php?id=<?php echo (int)$row['id']; ?>"
                                    class="booking-action-btn"
                                    title="Manage Booking"
                                >

                                    <i
                                        class="fas fa-pen"
                                    ></i>

                                </a>

                            </td>


                        </tr>

                    <?php

                        }

                    }
                    else
                    {

                    ?>

                        <tr>

                            <td
                                colspan="8"
                                class="no-bookings"
                            >

                                <div
                                    class="no-bookings-content"
                                >

                                    <i
                                        class="fas fa-calendar-xmark"
                                    ></i>

                                    <h2>
                                        No Bookings Found
                                    </h2>

                                    <p>
                                        There are no event
                                        bookings yet.
                                    </p>

                                </div>

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

</section>


<?php

include "../includes/footer.php";

?>