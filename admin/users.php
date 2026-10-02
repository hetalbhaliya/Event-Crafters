<a href="dashboard.php" class="back-btn">
    <i class="fas fa-arrow-left"></i>
    Back to Dashboard
</a>

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
   HEADER
========================================= */

include "../includes/header.php";


/* =========================================
   DELETE USER
========================================= */

if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];


    $delete_query = mysqli_query(
        $conn,
        "DELETE FROM users
         WHERE id = $id"
    );


    if ($delete_query) {

        header("Location: users.php?deleted=1");
        exit;

    }

}


/* =========================================
   GET USERS
========================================= */

$user_query = mysqli_query(
    $conn,
    "SELECT
        id,
        full_name,
        email,
        mobile,
        created_at
     FROM users
     ORDER BY id DESC"
);


/* =========================================
   TOTAL USERS
========================================= */

$total_users = 0;


if ($user_query) {

    $total_users = mysqli_num_rows(
        $user_query
    );

}

?>


<!-- =========================================
     USERS CSS
========================================= -->

<link
    rel="stylesheet"
    href="../assets/css/admin-users.css"
>


<!-- =========================================
     USER MANAGEMENT
========================================= -->

<section class="admin-users-section">

    <div class="admin-users-container">


        <!-- =====================================
             PAGE HEADING
        ====================================== -->

        <div class="admin-users-heading">

            <span>
                ADMIN PANEL
            </span>

            <h1>
                User Management
            </h1>

            <p>
                Manage all registered Event Crafters users.
            </p>

        </div>



        <!-- =====================================
             DELETE SUCCESS MESSAGE
        ====================================== -->

        <?php

        if (isset($_GET['deleted'])) {

        ?>

            <div class="user-success">

                <i class="fas fa-circle-check"></i>

                User deleted successfully.

            </div>

        <?php

        }

        ?>



        <!-- =====================================
             USERS LIST CARD
        ====================================== -->

        <div class="users-list-card">


            <!-- =================================
                 CARD HEADER
            ================================== -->

            <div class="users-card-heading">

                <div>

                    <span>
                        REGISTERED USERS
                    </span>

                    <h2>
                        All Users
                    </h2>

                </div>


                <div class="users-count">

                    <i class="fas fa-users"></i>

                    <?php

                    echo $total_users;

                    ?>

                </div>

            </div>



            <!-- =================================
                 USERS TABLE
            ================================== -->

            <div class="users-table-wrapper">

                <table class="users-table">


                    <!-- TABLE HEADER -->

                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                User
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                Mobile
                            </th>

                            <th>
                                Registered On
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>



                    <!-- TABLE BODY -->

                    <tbody>


                    <?php

                    if (
                        $user_query &&
                        mysqli_num_rows($user_query) > 0
                    ) {


                        $count = 1;


                        while (
                            $row = mysqli_fetch_assoc(
                                $user_query
                            )
                        ) {

                    ?>


                        <tr>


                            <!-- =====================
                                 NUMBER
                            ====================== -->

                            <td>

                                <?php

                                echo $count;

                                ?>

                            </td>



                            <!-- =====================
                                 USER NAME
                            ====================== -->

                            <td>

                                <div class="user-name">


                                    <div class="user-icon">

                                        <i class="fas fa-user"></i>

                                    </div>


                                    <span>

                                        <?php

                                        echo htmlspecialchars(
                                            $row['full_name']
                                        );

                                        ?>

                                    </span>


                                </div>

                            </td>



                            <!-- =====================
                                 EMAIL
                            ====================== -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row['email']
                                );

                                ?>

                            </td>



                            <!-- =====================
                                 MOBILE
                            ====================== -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row['mobile']
                                );

                                ?>

                            </td>



                            <!-- =====================
                                 REGISTERED DATE
                            ====================== -->

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



                            <!-- =====================
                                 DELETE
                            ====================== -->

                            <td>

                                <a
                                    href="users.php?delete=<?php echo $row['id']; ?>"
                                    class="user-delete-btn"
                                    onclick="return confirm('Are you sure you want to delete this user?');"
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
                             NO USERS
                        ====================== -->

                        <tr>

                            <td colspan="6">

                                <div class="users-empty">


                                    <i class="fas fa-users"></i>


                                    <h3>
                                        No Users Found
                                    </h3>


                                    <p>
                                        No registered users are available.
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