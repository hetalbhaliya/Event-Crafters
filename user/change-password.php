<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

$user_id = (int) $_SESSION['user_id'];

$message = "";
$message_type = "";


/* =========================================
   CHANGE PASSWORD
========================================= */

if (isset($_POST['change_password'])) {

    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);


    /* =========================================
       CHECK EMPTY FIELDS
    ========================================= */

    if (
        $current_password == "" ||
        $new_password == "" ||
        $confirm_password == ""
    ) {

        $message = "Please fill all password fields.";
        $message_type = "error";

    }


    /* =========================================
       CHECK NEW PASSWORD
    ========================================= */

    elseif (strlen($new_password) < 6) {

        $message = "New password must be at least 6 characters.";
        $message_type = "error";

    }


    /* =========================================
       CHECK CONFIRM PASSWORD
    ========================================= */

    elseif ($new_password !== $confirm_password) {

        $message = "New password and confirm password do not match.";
        $message_type = "error";

    }


    /* =========================================
       GET CURRENT PASSWORD
    ========================================= */

    else {

        $sql = "
            SELECT password
            FROM users
            WHERE id = '$user_id'
            LIMIT 1
        ";

        $result = mysqli_query($conn, $sql);


        if ($result && mysqli_num_rows($result) == 1) {

            $user = mysqli_fetch_assoc($result);

            $stored_password = $user['password'];


            /* =========================================
               CHECK CURRENT PASSWORD
            ========================================= */

            if ($current_password !== $stored_password) {

                $message = "Current password is incorrect.";
                $message_type = "error";

            }


            /* =========================================
               SAME PASSWORD CHECK
            ========================================= */

            elseif ($current_password === $new_password) {

                $message = "New password must be different from current password.";
                $message_type = "error";

            }


            /* =========================================
               UPDATE PASSWORD
            ========================================= */

            else {

                $new_password_safe = mysqli_real_escape_string(
                    $conn,
                    $new_password
                );


                $update_sql = "
                    UPDATE users
                    SET password = '$new_password_safe'
                    WHERE id = '$user_id'
                ";


                if (mysqli_query($conn, $update_sql)) {

                    $message = "Password changed successfully.";
                    $message_type = "success";

                }
                else {

                    $message = "Unable to change password. Please try again.";
                    $message_type = "error";

                }

            }

        }
        else {

            $message = "User account not found.";
            $message_type = "error";

        }

    }

}


include "../includes/header.php";
include "../includes/navbar.php";

?>


<link rel="stylesheet" href="../assets/css/change-password.css">


<!-- =========================================
     BACK BUTTON
========================================= -->

<a href="javascript:history.back()" class="back-btn">

    <i class="fas fa-arrow-left"></i>

    Back

</a>


<!-- =========================================
     CHANGE PASSWORD SECTION
========================================= -->

<section class="change-password-section">

    <div class="change-password-container">


        <!-- =====================================
             HEADING
        ====================================== -->

        <div class="change-password-heading">

            <span>
                USER ACCOUNT
            </span>

            <h1>
                Change Password
            </h1>

            <p>
                Keep your account secure by updating your password.
            </p>

        </div>


        <!-- =====================================
             MESSAGE
        ====================================== -->

        <?php if ($message != "") { ?>

            <div class="password-message <?php echo $message_type; ?>">

                <?php if ($message_type == "success") { ?>

                    <i class="fas fa-circle-check"></i>

                <?php } else { ?>

                    <i class="fas fa-circle-exclamation"></i>

                <?php } ?>


                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php } ?>


        <!-- =====================================
             PASSWORD CARD
        ====================================== -->

        <div class="change-password-card">


            <!-- ICON -->

            <div class="password-icon">

                <i class="fas fa-lock"></i>

            </div>


            <h2>
                Update Your Password
            </h2>


            <p class="password-description">

                Enter your current password and create a new one.

            </p>


            <!-- =================================
                 FORM
            ================================== -->

            <form
                method="POST"
                action="change-password.php"
                class="change-password-form"
            >


                <!-- =================================
                     CURRENT PASSWORD
                ================================== -->

                <div class="form-group">

                    <label>

                        <i class="fas fa-lock"></i>

                        Current Password

                    </label>


                    <div class="password-input">

                        <input
                            type="password"
                            name="current_password"
                            id="current_password"
                            placeholder="Enter current password"
                            required
                        >


                        <button
                            type="button"
                            class="show-password"
                            onclick="togglePassword(
                                'current_password',
                                this
                            )"
                        >

                            <i class="fas fa-eye"></i>

                        </button>

                    </div>

                </div>


                <!-- =================================
                     NEW PASSWORD
                ================================== -->

                <div class="form-group">

                    <label>

                        <i class="fas fa-key"></i>

                        New Password

                    </label>


                    <div class="password-input">

                        <input
                            type="password"
                            name="new_password"
                            id="new_password"
                            placeholder="Enter new password"
                            minlength="6"
                            required
                        >


                        <button
                            type="button"
                            class="show-password"
                            onclick="togglePassword(
                                'new_password',
                                this
                            )"
                        >

                            <i class="fas fa-eye"></i>

                        </button>

                    </div>


                    <small>
                        Password must contain at least 6 characters.
                    </small>

                </div>


                <!-- =================================
                     CONFIRM PASSWORD
                ================================== -->

                <div class="form-group">

                    <label>

                        <i class="fas fa-key"></i>

                        Confirm New Password

                    </label>


                    <div class="password-input">

                        <input
                            type="password"
                            name="confirm_password"
                            id="confirm_password"
                            placeholder="Confirm new password"
                            minlength="6"
                            required
                        >


                        <button
                            type="button"
                            class="show-password"
                            onclick="togglePassword(
                                'confirm_password',
                                this
                            )"
                        >

                            <i class="fas fa-eye"></i>

                        </button>

                    </div>

                </div>


                <!-- =================================
                     BUTTON
                ================================== -->

                <button
                    type="submit"
                    name="change_password"
                    class="change-password-btn"
                >

                    <i class="fas fa-shield-halved"></i>

                    Change Password

                </button>


            </form>


        </div>

    </div>

</section>


<!-- =========================================
     SHOW / HIDE PASSWORD
========================================= -->

<script>

function togglePassword(fieldId, button) {

    var field = document.getElementById(fieldId);

    var icon = button.querySelector("i");


    if (field.type === "password") {

        field.type = "text";

        icon.classList.remove("fa-eye");

        icon.classList.add("fa-eye-slash");

    }
    else {

        field.type = "password";

        icon.classList.remove("fa-eye-slash");

        icon.classList.add("fa-eye");

    }

}

</script>


<?php

include "../includes/footer.php";

?>