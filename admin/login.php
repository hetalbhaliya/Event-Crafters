<?php

session_start();

include "../config/db.php";

$message = "";


/* =========================================
   ADMIN LOGIN
========================================= */

if(isset($_POST['login']))
{
    $email = mysqli_real_escape_string(
        $conn,
        trim($_POST['email'])
    );

    $password = mysqli_real_escape_string(
        $conn,
        $_POST['password']
    );


    /* =====================================
       VALIDATION
    ===================================== */

    if($email == "" || $password == "")
    {
        $message = "Please enter email and password.";
    }
    else
    {

        /* =================================
           CHECK ADMIN
        ================================= */

        $sql = "SELECT *
                FROM admins
                WHERE email='$email'
                AND password='$password'
                LIMIT 1";


        $result = mysqli_query(
            $conn,
            $sql
        );


        /* =================================
           LOGIN SUCCESS
        ================================= */

        if(
            $result &&
            mysqli_num_rows($result) == 1
        )
        {

            $admin = mysqli_fetch_assoc(
                $result
            );


            /* =============================
               ADMIN SESSION
            ============================= */

            $_SESSION['admin_id'] =
                (int)$admin['id'];

            $_SESSION['admin_name'] =
                $admin['full_name'];

            $_SESSION['admin_email'] =
                $admin['email'];


            /* =============================
               REDIRECT
            ============================= */

            header(
                "Location: dashboard.php"
            );

            exit;

        }
        else
        {

            $message =
                "Invalid admin email or password.";

        }

    }
}

?>


<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Admin Login - Event Crafters
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/admin-login.css"
    >

</head>


<body>


<!-- =====================================
     ADMIN LOGIN
====================================== -->

<section class="admin-login-section">


    <div class="admin-login-box">


        <!-- =================================
             HEADING
        ================================== -->

        <div class="admin-login-heading">

            <span>
                EVENT CRAFTERS
            </span>

            <h1>
                Admin Login
            </h1>

            <p>
                Login to manage events,
                bookings and users.
            </p>

        </div>


        <!-- =================================
             MESSAGE
        ================================== -->

        <?php if($message != "") { ?>

            <div class="admin-login-message">

                <?php
                echo htmlspecialchars(
                    $message
                );
                ?>

            </div>

        <?php } ?>


        <!-- =================================
             FORM
        ================================== -->

        <form
            method="POST"
            action=""
        >


            <!-- EMAIL -->

            <div class="form-group">

                <label>
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter admin email"
                    required
                >

            </div>


            <!-- PASSWORD -->

            <div class="form-group">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter admin password"
                    required
                >

            </div>


            <!-- BUTTON -->

            <button
                type="submit"
                name="login"
                class="admin-login-btn"
            >

                <i class="fas fa-right-to-bracket"></i>

                Login to Dashboard

            </button>


        </form>


        <!-- =================================
             BACK WEBSITE
        ================================== -->

        <a
            href="../index.php"
            class="back-website"
        >

            <i class="fas fa-arrow-left"></i>

            Back to Website

        </a>


    </div>


</section>


</body>

</html>