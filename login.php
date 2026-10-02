<?php

/* =========================================
   SESSION
========================================= */

session_start();


/* =========================================
   DATABASE
========================================= */

include "config/db.php";


/* =========================================
   MESSAGE
========================================= */

$message = "";
$message_type = "";


/* =========================================
   LOGIN PROCESS
========================================= */

if (isset($_POST['login'])) {

    /* =====================================
       GET FORM DATA
    ===================================== */

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);


    /* =====================================
       VALIDATION
    ===================================== */

    if ($email == "" || $password == "") {

        $message = "Please enter email and password.";
        $message_type = "error";

    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    }

    else {

        /* =================================
           ESCAPE DATA
        ================================= */

        $email = mysqli_real_escape_string(
            $conn,
            $email
        );

        $password = mysqli_real_escape_string(
            $conn,
            $password
        );


        /* =================================
           CHECK USER
        ================================= */

        $sql = "
            SELECT
                id,
                full_name,
                email
            FROM users
            WHERE email = '$email'
            AND password = '$password'
            LIMIT 1
        ";


        $result = mysqli_query(
            $conn,
            $sql
        );


        /* =================================
           LOGIN SUCCESS
        ================================= */

        if (
            $result &&
            mysqli_num_rows($result) == 1
        ) {

            $user = mysqli_fetch_assoc(
                $result
            );


            /* =============================
               CREATE USER SESSION
            ============================== */

            $_SESSION['user_id'] =
                $user['id'];

            $_SESSION['user_name'] =
                $user['full_name'];

            $_SESSION['user_email'] =
                $user['email'];


            /* =============================
               REDIRECT TO USER DASHBOARD
            ============================== */

            header(
                "Location: user/dashboard.php"
            );

            exit;

        }


        /* =================================
           LOGIN FAILED
        ================================= */

        else {

            $message =
                "Invalid email or password.";

            $message_type =
                "error";

        }

    }

}


/* =========================================
   HEADER
========================================= */

include "includes/header.php";

include "includes/navbar.php";

?>


<!-- =========================================
     LOGIN CSS
========================================= -->

<link
    rel="stylesheet"
    href="assets/css/login.css"
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
     LOGIN SECTION
========================================= -->

<section class="login-section">


    <div class="login-box">


        <!-- =================================
             HEADING
        ================================== -->

        <div class="login-heading">

            <span>
                WELCOME BACK
            </span>

            <h1>
                Login
            </h1>

            <p>
                Login to manage your events and bookings.
            </p>

        </div>


        <!-- =================================
             MESSAGE
        ================================== -->

        <?php if ($message != "") { ?>

            <div
                class="login-message <?php echo $message_type; ?>"
            >

                <?php if ($message_type == "error") { ?>

                    <i class="fas fa-circle-exclamation"></i>

                <?php } ?>

                <?php
                echo htmlspecialchars(
                    $message
                );
                ?>

            </div>

        <?php } ?>


        <!-- =================================
             LOGIN FORM
        ================================== -->

        <form
            method="POST"
            action=""
        >


            <!-- EMAIL -->

            <div class="form-group">

                <label>

                    <i class="fas fa-envelope"></i>

                    Email Address

                </label>


                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    value="<?php

                    if (isset($_POST['email'])) {

                        echo htmlspecialchars(
                            $_POST['email']
                        );

                    }

                    ?>"
                    required
                >

            </div>


            <!-- PASSWORD -->

            <div class="form-group">

                <label>

                    <i class="fas fa-lock"></i>

                    Password

                </label>


                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

            </div>


            <!-- LOGIN BUTTON -->

            <button
                type="submit"
                name="login"
                class="login-submit"
            >

                <i class="fas fa-right-to-bracket"></i>

                Login

            </button>


        </form>


        <!-- =================================
             REGISTER LINK
        ================================== -->

        <p class="register-link">

            Don't have an account?

            <a href="register.php">

                Create Account

            </a>

        </p>


    </div>


</section>


<?php

/* =========================================
   FOOTER
========================================= */

include "includes/footer.php";

?>