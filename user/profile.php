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

$user_id = (int) $_SESSION['user_id'];

$message = "";
$message_type = "";


/* =========================================
   UPDATE PROFILE
========================================= */

if (isset($_POST['update_profile'])) {

    $full_name = trim($_POST['full_name'] ?? "");
    $mobile    = trim($_POST['mobile'] ?? "");


    /* VALIDATION */

    if ($full_name === "" || $mobile === "") {

        $message = "Please fill all required fields.";
        $message_type = "error";

    }
    elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {

        $message = "Mobile number must contain exactly 10 digits.";
        $message_type = "error";

    }
    else {

        $full_name_db = mysqli_real_escape_string(
            $conn,
            $full_name
        );

        $mobile_db = mysqli_real_escape_string(
            $conn,
            $mobile
        );


        $update_sql = "
            UPDATE users
            SET
                full_name = '$full_name_db',
                mobile = '$mobile_db'
            WHERE id = '$user_id'
            LIMIT 1
        ";


        if (mysqli_query($conn, $update_sql)) {

            /* Update session name also */

            $_SESSION['user_name'] = $full_name;

            $message = "Profile updated successfully.";
            $message_type = "success";

        }
        else {

            $message = "Unable to update profile.";
            $message_type = "error";

        }

    }

}


/* =========================================
   GET USER DETAILS
========================================= */

$user_sql = "
    SELECT
        id,
        full_name,
        email,
        mobile,
        created_at
    FROM users
    WHERE id = '$user_id'
    LIMIT 1
";


$user_result = mysqli_query(
    $conn,
    $user_sql
);


if (!$user_result) {

    die(
        "Database Error: " .
        htmlspecialchars(mysqli_error($conn))
    );

}


if (mysqli_num_rows($user_result) == 0) {

    session_destroy();

    header("Location: ../login.php");
    exit;

}


$user = mysqli_fetch_assoc(
    $user_result
);


/* =========================================
   HEADER
========================================= */

include "../includes/header.php";
include "../includes/navbar.php";

?>


<link
    rel="stylesheet"
    href="../assets/css/profile.css"
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
     PROFILE SECTION
========================================= -->

<section class="profile-section">

    <div class="profile-container">


        <!-- =====================================
             HEADING
        ====================================== -->

        <div class="profile-heading">

            <span>
                USER ACCOUNT
            </span>

            <h1>
                My Profile
            </h1>

            <p>
                Manage your personal information and account details.
            </p>

        </div>


        <!-- =====================================
             MESSAGE
        ====================================== -->

        <?php if ($message !== "") { ?>

            <div
                class="profile-message
                <?php echo htmlspecialchars($message_type); ?>"
            >

                <?php if ($message_type === "success") { ?>

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
             PROFILE CARD
        ====================================== -->

        <div class="profile-card">


            <!-- =================================
                 PROFILE TOP
            ================================== -->

            <div class="profile-top">


                <!-- AVATAR -->

                <div class="profile-avatar">

                    <i class="fas fa-user"></i>

                </div>


                <!-- USER INFO -->

                <div class="profile-user-info">

                    <h2>

                        <?php
                        echo htmlspecialchars(
                            $user['full_name']
                        );
                        ?>

                    </h2>


                    <p>

                        <i class="fas fa-envelope"></i>

                        <?php
                        echo htmlspecialchars(
                            $user['email']
                        );
                        ?>

                    </p>

                </div>

            </div>


            <!-- =================================
                 PROFILE FORM
            ================================== -->

            <form
                method="POST"
                action="profile.php"
                class="profile-form"
            >


                <!-- =================================
                     FULL NAME
                ================================== -->

                <div class="form-group">

                    <label>

                        <i class="fas fa-user"></i>

                        Full Name

                    </label>


                    <input
                        type="text"
                        name="full_name"
                        value="<?php
                            echo htmlspecialchars(
                                $user['full_name']
                            );
                        ?>"
                        placeholder="Enter your full name"
                        maxlength="100"
                        required
                    >

                </div>


                <!-- =================================
                     EMAIL
                ================================== -->

                <div class="form-group">

                    <label>

                        <i class="fas fa-envelope"></i>

                        Email Address

                    </label>


                    <input
                        type="email"
                        value="<?php
                            echo htmlspecialchars(
                                $user['email']
                            );
                        ?>"
                        readonly
                    >


                    <small class="field-note">

                        Email address cannot be changed.

                    </small>

                </div>


                <!-- =================================
                     MOBILE
                ================================== -->

                <div class="form-group">

                    <label>

                        <i class="fas fa-phone"></i>

                        Mobile Number

                    </label>


                    <input
                        type="text"
                        name="mobile"
                        value="<?php
                            echo htmlspecialchars(
                                $user['mobile']
                            );
                        ?>"
                        placeholder="Enter 10 digit mobile number"
                        maxlength="10"
                        pattern="[0-9]{10}"
                        inputmode="numeric"
                        required
                    >

                </div>


                <!-- =================================
                     ACCOUNT INFORMATION
                ================================== -->

                <div class="account-info">


                    <!-- ACCOUNT CREATED -->

                    <div>

                        <span>
                            Account Created
                        </span>


                        <strong>

                            <?php

                            if (
                                !empty($user['created_at']) &&
                                strtotime($user['created_at'])
                            ) {

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $user['created_at']
                                    )
                                );

                            }
                            else {

                                echo "N/A";

                            }

                            ?>

                        </strong>

                    </div>


                    <!-- ACCOUNT ID -->

                    <div>

                        <span>
                            Account ID
                        </span>


                        <strong>

                            #<?php
                            echo (int)$user['id'];
                            ?>

                        </strong>

                    </div>


                </div>


                <!-- =================================
                     UPDATE BUTTON
                ================================== -->

                <div class="profile-actions">

                    <button
                        type="submit"
                        name="update_profile"
                        class="update-profile-btn"
                    >

                        <i class="fas fa-save"></i>

                        Update Profile

                    </button>

                </div>


            </form>


        </div>


    </div>

</section>


<?php

include "../includes/footer.php";

?>