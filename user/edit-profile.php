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
   MESSAGE
========================================= */

$message = "";
$message_type = "";


/* =========================================
   GET CURRENT USER
========================================= */

$sql = "
    SELECT
        id,
        full_name,
        email,
        mobile
    FROM users
    WHERE id='$user_id'
    LIMIT 1
";

$result = mysqli_query($conn, $sql);


if(!$result || mysqli_num_rows($result) == 0)
{
    session_destroy();

    header("Location: ../login.php");
    exit;
}


$user = mysqli_fetch_assoc($result);


/* =========================================
   UPDATE PROFILE
========================================= */

if(isset($_POST['update_profile']))
{

    /* =====================================
       GET FORM DATA
    ===================================== */

    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $mobile    = trim($_POST['mobile']);


    /* =====================================
       VALIDATION
    ===================================== */

    if(
        $full_name == "" ||
        $email == "" ||
        $mobile == ""
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

        $message =
            "Please enter a valid email address.";

        $message_type = "error";

    }

    elseif(!preg_match(
        '/^[0-9]{10}$/',
        $mobile
    ))
    {

        $message =
            "Mobile number must contain exactly 10 digits.";

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


        /* =================================
           CHECK EMAIL
           EXCEPT CURRENT USER
        ================================= */

        $check_sql = "
            SELECT id
            FROM users
            WHERE email='$email'
            AND id!='$user_id'
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
                "This email address is already registered.";

            $message_type = "error";

        }

        else
        {

            /* =============================
               UPDATE USER
            ============================== */

            $update_sql = "
                UPDATE users
                SET
                    full_name='$full_name',
                    email='$email',
                    mobile='$mobile'
                WHERE id='$user_id'
            ";


            if(mysqli_query(
                $conn,
                $update_sql
            ))
            {

                /* =============================
                   UPDATE SESSION
                ============================== */

                $_SESSION['user_name'] =
                    $full_name;

                $_SESSION['user_email'] =
                    $email;


                /* =============================
                   UPDATE DISPLAY DATA
                ============================== */

                $user['full_name'] =
                    $full_name;

                $user['email'] =
                    $email;

                $user['mobile'] =
                    $mobile;


                /* =============================
                   SUCCESS
                ============================== */

                $message =
                    "Profile updated successfully.";

                $message_type = "success";

            }

            else
            {

                $message =
                    "Failed to update profile. Please try again.";

                $message_type = "error";

            }

        }

    }

}


/* =========================================
   HEADER
========================================= */

include "../includes/header.php";

include "../includes/navbar.php";

?>


<link
    rel="stylesheet"
    href="../assets/css/edit-profile.css"
>


<!-- =========================================
     BACK BUTTON
========================================= -->

<a
    href="profile.php"
    class="back-btn"
>

    <i class="fas fa-arrow-left"></i>

    Back to Profile

</a>


<!-- =========================================
     EDIT PROFILE SECTION
========================================= -->

<section class="edit-profile-section">


    <div class="edit-profile-container">


        <!-- =====================================
             HEADING
        ====================================== -->

        <div class="edit-profile-heading">

            <span>
                USER ACCOUNT
            </span>

            <h1>
                Edit Profile
            </h1>

            <p>
                Update your personal information and account details.
            </p>

        </div>


        <!-- =====================================
             MESSAGE
        ====================================== -->

        <?php if($message != "") { ?>

            <div
                class="edit-profile-message
                <?php echo $message_type; ?>"
            >

                <?php

                echo htmlspecialchars(
                    $message
                );

                ?>

            </div>

        <?php } ?>


        <!-- =====================================
             PROFILE CARD
        ====================================== -->

        <div class="edit-profile-card">


            <!-- =================================
                 PROFILE ICON
            ================================== -->

            <div class="edit-profile-top">

                <div class="edit-profile-avatar">

                    <i class="fas fa-user"></i>

                </div>


                <div>

                    <h2>
                        Personal Information
                    </h2>

                    <p>
                        Keep your account information up to date.
                    </p>

                </div>

            </div>


            <!-- =================================
                 FORM
            ================================== -->

            <form
                method="POST"
                action=""
            >


                <!-- =============================
                     FULL NAME
                ============================== -->

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
                        required
                    >

                </div>


                <!-- =============================
                     EMAIL
                ============================== -->

                <div class="form-group">

                    <label>

                        <i class="fas fa-envelope"></i>

                        Email Address

                    </label>


                    <input
                        type="email"
                        name="email"
                        value="<?php

                        echo htmlspecialchars(
                            $user['email']
                        );

                        ?>"
                        placeholder="Enter your email"
                        required
                    >

                </div>


                <!-- =============================
                     MOBILE
                ============================== -->

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
                        maxlength="10"
                        pattern="[0-9]{10}"
                        inputmode="numeric"
                        placeholder="Enter 10 digit mobile number"
                        required
                    >

                </div>


                <!-- =============================
                     PASSWORD NOTE
                ============================== -->

                <div class="password-note">

                    <i class="fas fa-lock"></i>

                    <div>

                        <strong>
                            Want to change your password?
                        </strong>

                        <p>
                            Use the Change Password option from your account.
                        </p>

                    </div>

                </div>


                <!-- =============================
                     BUTTONS
                ============================== -->

                <div class="edit-profile-actions">


                    <a
                        href="profile.php"
                        class="cancel-profile-btn"
                    >

                        Cancel

                    </a>


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