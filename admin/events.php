<a href="dashboard.php" class="back-btn">
    <i class="fas fa-arrow-left"></i>
    Back to Dashboard
</a>

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
   HEADER
========================================= */

include "../includes/header.php";


/* =========================================
   ADMIN NAME
========================================= */

$admin_name = "Admin";

if(isset($_SESSION['admin_name']))
{
    $admin_name = $_SESSION['admin_name'];
}


/* =========================================
   GET EVENTS
========================================= */

$sql = "SELECT *
        FROM events
        ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

?>

<link
    rel="stylesheet"
    href="../assets/css/admin-events.css"
>


<!-- =========================================
     ADMIN EVENTS
========================================= -->

<section class="admin-events-section">

    <div class="admin-events-container">


        <!-- =====================================
             HEADER
        ====================================== -->

        <div class="admin-events-heading">

            <div>

                <span>
                    ADMIN PANEL
                </span>

                <h1>
                    Manage Events
                </h1>

                <p>
                    Add, edit and manage all Event Crafters events.
                </p>

            </div>


            <a
                href="add-event.php"
                class="add-event-btn"
            >

                <i class="fas fa-plus"></i>

                Add New Event

            </a>

        </div>


        <!-- =====================================
             EVENTS TABLE
        ====================================== -->

        <div class="admin-events-card">


            <?php if($result && mysqli_num_rows($result) > 0) { ?>


            <div class="events-table-wrapper">

                <table class="events-table">


                    <thead>

                        <tr>

                            <th>
                                Image
                            </th>

                            <th>
                                Event
                            </th>

                            <th>
                                Category
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Venue
                            </th>

                            <th>
                                Price
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    while($row = mysqli_fetch_assoc($result))
                    {

                    ?>


                        <tr>


                            <!-- IMAGE -->

                            <td>

                                <div class="event-table-image">

                                    <?php

                                    if(
                                        !empty($row['image']) &&
                                        file_exists(
                                            "../uploads/events/" .
                                            $row['image']
                                        )
                                    )
                                    {

                                    ?>

                                        <img
                                            src="../uploads/events/<?php
                                            echo htmlspecialchars(
                                                $row['image']
                                            );
                                            ?>"
                                            alt="Event"
                                        >

                                    <?php

                                    }
                                    else
                                    {

                                    ?>

                                        <div class="no-image">

                                            <i class="fas fa-image"></i>

                                        </div>

                                    <?php

                                    }

                                    ?>

                                </div>

                            </td>


                            <!-- EVENT -->

                            <td>

                                <strong class="event-name">

                                    <?php

                                    echo htmlspecialchars(
                                        $row['event_name']
                                    );

                                    ?>

                                </strong>

                            </td>


                            <!-- CATEGORY -->

                            <td>

                                <span class="event-category">

                                    <?php

                                    echo htmlspecialchars(
                                        $row['category']
                                    );

                                    ?>

                                </span>

                            </td>


                            <!-- DATE -->

                            <td>

                                <?php

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $row['event_date']
                                    )
                                );

                                ?>

                            </td>


                            <!-- VENUE -->

                            <td>

                                <span class="event-venue">

                                    <i
                                        class="fas fa-location-dot"
                                    ></i>

                                    <?php

                                    echo htmlspecialchars(
                                        $row['venue']
                                    );

                                    ?>

                                </span>

                            </td>


                            <!-- PRICE -->

                            <td>

                                <strong class="event-price">

                                    ₹ <?php

                                    echo number_format(
                                        $row['price']
                                    );

                                    ?>

                                </strong>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <div class="event-actions">


                                    <a
                                        href="edit-event.php?id=<?php
                                        echo (int)$row['id'];
                                        ?>"
                                        class="edit-event-btn"
                                        title="Edit Event"
                                    >

                                        <i
                                            class="fas fa-pen"
                                        ></i>

                                    </a>


                                    <a
                                        href="delete-event.php?id=<?php
                                        echo (int)$row['id'];
                                        ?>"
                                        class="delete-event-btn"
                                        title="Delete Event"
                                        onclick="return confirm(
                                            'Are you sure you want to delete this event?'
                                        );"
                                    >

                                        <i
                                            class="fas fa-trash"
                                        ></i>

                                    </a>


                                </div>

                            </td>


                        </tr>


                    <?php

                    }

                    ?>


                    </tbody>

                </table>

            </div>


            <?php } else { ?>


                <!-- =================================
                     NO EVENTS
                ================================== -->

                <div class="no-admin-events">

                    <div class="no-events-icon">

                        <i class="fas fa-calendar-xmark"></i>

                    </div>

                    <h2>
                        No Events Found
                    </h2>

                    <p>
                        You haven't added any events yet.
                    </p>

                    <a
                        href="add-event.php"
                        class="add-event-btn"
                    >

                        <i class="fas fa-plus"></i>

                        Add Your First Event

                    </a>

                </div>


            <?php } ?>


        </div>


    </div>

</section>


<?php

include "../includes/footer.php";

?>