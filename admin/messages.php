<?php

session_start();


/* =========================================
   ADMIN LOGIN CHECK
========================================= */

if (!isset($_SESSION['admin_id'])) {

    header("Location: index.php");
    exit;

}


/* =========================================
   DATABASE
========================================= */

include "../config/db.php";


/* =========================================
   DELETE MESSAGE
========================================= */

if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $delete_query = mysqli_query(
        $conn,
        "DELETE FROM contact_messages
         WHERE id = $id"
    );

    if ($delete_query) {

        header("Location: messages.php?deleted=1");
        exit;

    }

}


/* =========================================
   GET MESSAGES
========================================= */

$message_query = mysqli_query(
    $conn,
    "SELECT
        id,
        name,
        email,
        subject,
        message,
        created_at
     FROM contact_messages
     ORDER BY id DESC"
);


/* =========================================
   CHECK QUERY ERROR
========================================= */

if (!$message_query) {

    die(
        "Message Query Error: " .
        mysqli_error($conn)
    );

}


/* =========================================
   TOTAL MESSAGES
========================================= */

$total_messages = mysqli_num_rows(
    $message_query
);


/* =========================================
   HEADER
========================================= */

include "../includes/header.php";

?>


<!-- =========================================
     MESSAGES CSS
========================================= -->

<link
    rel="stylesheet"
    href="../assets/css/admin-messages.css"
>


<!-- =========================================
     MESSAGE MANAGEMENT
========================================= -->

<section class="admin-messages-section">

    <div class="admin-messages-container">


        <!-- =====================================
             BACK BUTTON
        ====================================== -->

        <a
            href="dashboard.php"
            class="back-btn"
        >

            <i class="fas fa-arrow-left"></i>

            Back to Dashboard

        </a>



        <!-- =====================================
             PAGE HEADING
        ====================================== -->

        <div class="admin-messages-heading">

            <span>
                ADMIN PANEL
            </span>

            <h1>
                Message Management
            </h1>

            <p>
                View and manage messages received from website visitors.
            </p>

        </div>



        <!-- =====================================
             DELETE SUCCESS
        ====================================== -->

        <?php

        if (isset($_GET['deleted'])) {

        ?>

            <div class="message-success">

                <i class="fas fa-circle-check"></i>

                Message deleted successfully.

            </div>

        <?php

        }

        ?>



        <!-- =====================================
             MESSAGES CARD
        ====================================== -->

        <div class="messages-list-card">


            <!-- CARD HEADER -->

            <div class="messages-card-heading">

                <div>

                    <span>
                        CONTACT MESSAGES
                    </span>

                    <h2>
                        All Messages
                    </h2>

                </div>


                <div class="messages-count">

                    <i class="fas fa-envelope"></i>

                    <?php echo $total_messages; ?>

                </div>

            </div>



            <!-- =================================
                 MESSAGE TABLE
            ================================== -->

            <div class="messages-table-wrapper">

                <table class="messages-table">


                    <!-- TABLE HEADER -->

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Subject</th>

                            <th>Message</th>

                            <th>Date</th>

                            <th>Action</th>

                        </tr>

                    </thead>



                    <!-- TABLE BODY -->

                    <tbody>


                    <?php

                    if ($total_messages > 0) {

                        $count = 1;


                        while (
                            $row = mysqli_fetch_assoc(
                                $message_query
                            )
                        ) {

                    ?>

                        <tr>


                            <!-- NUMBER -->

                            <td>

                                <?php echo $count; ?>

                            </td>



                            <!-- NAME -->

                            <td>

                                <div class="message-name">

                                    <div class="message-icon">

                                        <i class="fas fa-user"></i>

                                    </div>


                                    <span>

                                        <?php

                                        echo htmlspecialchars(
                                            $row['name']
                                        );

                                        ?>

                                    </span>

                                </div>

                            </td>



                            <!-- EMAIL -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row['email']
                                );

                                ?>

                            </td>



                            <!-- SUBJECT -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row['subject']
                                );

                                ?>

                            </td>



                            <!-- MESSAGE -->

                            <td>

                                <div class="message-text">

                                    <?php

                                    echo htmlspecialchars(
                                        $row['message']
                                    );

                                    ?>

                                </div>

                            </td>



                            <!-- DATE -->

                            <td>

                                <?php

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $row['created_at']
                                    )
                                );

                                ?>

                            </td>



                            <!-- DELETE -->

                            <td>

                                <a
                                    href="messages.php?delete=<?php echo $row['id']; ?>"
                                    class="message-delete-btn"
                                    onclick="return confirm('Are you sure you want to delete this message?');"
                                >

                                    <i class="fas fa-trash"></i>

                                    Delete

                                </a>

                            </td>


                        </tr>


                    <?php

                            $count++;

                        }


                    } else {

                    ?>

                        <!-- =====================
                             NO MESSAGES
                        ====================== -->

                        <tr>

                            <td colspan="7">

                                <div class="messages-empty">

                                    <i class="fas fa-envelope-open"></i>

                                    <h3>
                                        No Messages Found
                                    </h3>

                                    <p>
                                        No contact messages are available.
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

/* =========================================
   FOOTER
========================================= */

include "../includes/footer.php";

?>