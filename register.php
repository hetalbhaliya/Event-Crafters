<?php

session_start();

include "config/db.php";

$message = "";
$message_type = "";


/* =========================================
   REGISTRATION
========================================= */

if(isset($_POST['register']))
{
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);


    /* =====================================
       VALIDATION
    ===================================== */

    if(
        $full_name == "" ||
        $email == "" ||
        $mobile == "" ||
        $password == "" ||
        $confirm_password == ""
    )
    {
        $message = "Please fill all fields.";
        $message_type = "error";
    }

    elseif(!filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    ))
    {
        $message = "Please enter a valid email address.";
        $message_type = "error";
    }

    elseif(!preg_match(
        "/^[0-9]{10}$/",
        $mobile
    ))
    {
        $message =
            "Mobile number must contain exactly 10 digits.";

        $message_type = "error";
    }

    elseif($password != $confirm_password)
    {
        $message = "Passwords do not match.";
        $message_type = "error";
    }

    else
    {

        /* =================================
           ESCAPE DATA
        ================================= */

        $full_name = mysqli_real_escape_string(
            $conn,
            $full_name
        );

        $email = mysqli_real_escape_string(
            $conn,
            $email
        );

        $mobile = mysqli_real_escape_string(
            $conn,
            $mobile
        );

        $password = mysqli_real_escape_string(
            $conn,
            $password
        );


        /* =================================
           CHECK EMAIL
        ================================= */

        $check_sql = "
            SELECT id
            FROM users
            WHERE email='$email'
            LIMIT 1
        ";

        $check_result = mysqli_query(
            $conn,
            $check_sql
        );


        if(
            $check_result &&
            mysqli_num_rows($check_result) > 0
        )
        {
            $message =
                "Email already registered. Please login.";

            $message_type = "error";
        }

        else
        {

            /* =============================
               INSERT USER
            ============================= */

            $insert_sql = "
                INSERT INTO users
                (
                    full_name,
                    email,
                    mobile,
                    password
                )

                VALUES
                (
                    '$full_name',
                    '$email',
                    '$mobile',
                    '$password'
                )
            ";


            if(mysqli_query(
                $conn,
                $insert_sql
            ))
            {
                $message =
                    "Registration successful! You can now login.";

                $message_type = "success";
            }

            else
            {
                $message =
                    "Registration failed. Please try again.";

                $message_type = "error";
            }

        }

    }
}


/* =========================================
   HEADER
========================================= */

include "includes/header.php";
include "includes/navbar.php";

?>


<link
    rel="stylesheet"
    href="assets/css/register.css"
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
     REGISTER SECTION
========================================= -->

<section class="register-section">

    <div class="register-box">


        <!-- =================================
             HEADING
        ================================== -->

        <div class="register-heading">

            <span>
                JOIN EVENT CRAFTERS
            </span>

            <h1>
                Create Account
            </h1>

            <p>
                Register now and discover amazing events.
            </p>

        </div>


        <!-- =================================
             MESSAGE
        ================================== -->

        <?php if($message != "") { ?>

            <div
                class="
                register-message
                <?php echo $message_type; ?>
                "
            >

                <?php
                echo htmlspecialchars($message);
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


            <!-- FULL NAME -->

            <div class="form-group">

                <label>
                    Full Name
                </label>

                <input
                    type="text"
                    name="full_name"
                    value="<?php
                        echo isset($_POST['full_name'])
                            ? htmlspecialchars(
                                $_POST['full_name']
                            )
                            : '';
                    ?>"
                    placeholder="Enter your full name"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label>
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    value="<?php
                        echo isset($_POST['email'])
                            ? htmlspecialchars(
                                $_POST['email']
                            )
                            : '';
                    ?>"
                    placeholder="Enter your email"
                    required
                >

            </div>


            <!-- MOBILE -->

            <div class="form-group">

                <label>
                    Mobile Number
                </label>

                <input
                    type="text"
                    name="mobile"
                    value="<?php
                        echo isset($_POST['mobile'])
                            ? htmlspecialchars(
                                $_POST['mobile']
                            )
                            : '';
                    ?>"
                    maxlength="10"
                    pattern="[0-9]{10}"
                    inputmode="numeric"
                    placeholder="Enter 10 digit mobile number"
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
                    placeholder="Create password"
                    required
                >

            </div>


            <!-- CONFIRM PASSWORD -->

            <div class="form-group">

                <label>
                    Confirm Password
                </label>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm password"
                    required
                >

            </div>


            <!-- SUBMIT -->

            <button
                type="submit"
                name="register"
                class="register-submit"
            >

                <i class="fas fa-user-plus"></i>

                Create Account

            </button>


        </form>


        <!-- =================================
             LOGIN LINK
        ================================== -->

        <p class="login-link">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </p>


    </div>

</section>


<?php

include "includes/footer.php";

?>