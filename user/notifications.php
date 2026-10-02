<?php

/* =========================================
   SESSION
========================================= */

session_start();


/* =========================================
   LOGIN CHECK
========================================= */

if(!isset($_SESSION['user_id']))
{
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

$user_id = (int)$_SESSION['user_id'];


/* =========================================
   MARK ALL AS READ
========================================= */

if(isset($_GET['read']) && $_GET['read'] == "all")
{

    $read_sql = "
        UPDATE notifications
        SET is_read = 1
        WHERE user_id = '$user_id'
    ";

    mysqli_query($conn, $read_sql);


    /* REDIRECT BEFORE HTML OUTPUT */

    header("Location: notifications.php");
    exit;
}


/* =========================================
   GET NOTIFICATIONS
========================================= */

$sql = "
    SELECT
        id,
        user_id,
        title,
        message,
        type,
        is_read,
        created_at
    FROM notifications
    WHERE user_id = '$user_id'
    ORDER BY id DESC
";


$result = mysqli_query($conn, $sql);


/* =========================================
   UNREAD COUNT
========================================= */

$unread_count = 0;


$count_sql = "
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = '$user_id'
    AND is_read = 0
";


$count_result = mysqli_query($conn, $count_sql);


if($count_result)
{
    $count_data = mysqli_fetch_assoc($count_result);

    $unread_count = (int)$count_data['total'];
}


/* =========================================
   HEADER
========================================= */

include "../includes/header.php";

include "../includes/navbar.php";

?>


<link
    rel="stylesheet"
    href="../assets/css/notifications.css"
>


<!-- =========================================
     BACK BUTTON
========================================= -->

<a
    href="javascript:history.back()"
    class="back-btn"
>

    <i class="fas fa-arrow-left"></i>

    Back

</a>


<!-- =========================================
     NOTIFICATIONS SECTION
========================================= -->

<section class="notifications-section">


    <div class="notifications-container">


        <!-- =====================================
             HEADING
        ====================================== -->

        <div class="notifications-heading">

            <span>
                USER ACCOUNT
            </span>


            <h1>
                Notifications
            </h1>


            <p>
                Stay updated about your bookings and events.
            </p>

        </div>


        <!-- =====================================
             NOTIFICATION TOP BAR
        ====================================== -->

        <div class="notification-topbar">


            <div class="notification-summary">

                <i class="fas fa-bell"></i>

                <?php

                if($unread_count > 0)
                {

                    echo $unread_count .
                         " unread notification";

                    if($unread_count > 1)
                    {
                        echo "s";
                    }

                }
                else
                {

                    echo "All notifications are read.";

                }

                ?>

            </div>


            <?php if($unread_count > 0) { ?>

                <a
                    href="notifications.php?read=all"
                    class="mark-read-btn"
                >

                    <i class="fas fa-check-double"></i>

                    Mark All as Read

                </a>

            <?php } ?>


        </div>


        <!-- =====================================
             NOTIFICATION LIST
        ====================================== -->

        <?php

        if(
            $result &&
            mysqli_num_rows($result) > 0
        )
        {

        ?>


            <div class="notifications-list">


                <?php

                while(
                    $row =
                    mysqli_fetch_assoc($result)
                )
                {

                    $is_unread =
                        ((int)$row['is_read'] === 0)
                        ? "unread"
                        : "";


                    $type =
                        strtolower(
                            trim(
                                $row['type']
                            )
                        );


                    /* =============================
                       ICON
                    ============================== */

                    if($type == "booking")
                    {

                        $icon =
                            "fa-calendar-check";

                    }
                    elseif($type == "success")
                    {

                        $icon =
                            "fa-circle-check";

                    }
                    elseif($type == "cancelled")
                    {

                        $icon =
                            "fa-circle-xmark";

                    }
                    else
                    {

                        $icon =
                            "fa-bell";

                    }

                ?>


                    <!-- =================================
                         NOTIFICATION CARD
                    ================================== -->

                    <a
                        href="read_notification.php?id=<?php echo (int)$row['id']; ?>"
                        class="notification-card <?php echo $is_unread; ?>"
                    >


                        <!-- ICON -->

                        <div class="notification-icon">

                            <i
                                class="fas <?php echo $icon; ?>"
                            ></i>

                        </div>


                        <!-- CONTENT -->

                        <div class="notification-content">


                            <div class="notification-title-row">


                                <h3>

                                    <?php

                                    echo htmlspecialchars(
                                        $row['title']
                                    );

                                    ?>

                                </h3>


                                <?php

                                if(
                                    (int)$row['is_read'] === 0
                                )
                                {

                                ?>

                                    <span class="new-label">
                                        NEW
                                    </span>

                                <?php

                                }

                                ?>

                            </div>


                            <p>

                                <?php

                                echo htmlspecialchars(
                                    $row['message']
                                );

                                ?>

                            </p>


                            <small>

                                <i class="fas fa-clock"></i>

                                <?php

                                if(
                                    !empty(
                                        $row['created_at']
                                    )
                                )
                                {

                                    echo date(
                                        "d M Y, h:i A",
                                        strtotime(
                                            $row['created_at']
                                        )
                                    );

                                }
                                else
                                {

                                    echo "Recently";

                                }

                                ?>

                            </small>


                        </div>


                        <!-- UNREAD DOT -->

                        <?php

                        if(
                            (int)$row['is_read'] === 0
                        )
                        {

                        ?>

                            <span
                                class="unread-dot"
                            ></span>

                        <?php

                        }


                        ?>


                        <!-- ARROW -->

                        <div class="notification-arrow">

                            <i class="fas fa-chevron-right"></i>

                        </div>


                    </a>


                <?php

                }

                ?>


            </div>


        <?php

        }
        else
        {

        ?>


            <!-- =====================================
                 EMPTY NOTIFICATIONS
            ====================================== -->

            <div class="empty-notifications">


                <div class="empty-notification-icon">

                    <i class="fas fa-bell-slash"></i>

                </div>


                <h2>
                    No Notifications
                </h2>


                <p>
                    You don't have any notifications yet.
                </p>


                <a
                    href="../events.php"
                    class="explore-notifications-btn"
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

include "../includes/footer.php";

?>