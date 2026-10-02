<?php

include "config/db.php";
include "includes/header.php";
include "includes/navbar.php";

$message = "";
$message_type = "";

if(isset($_POST['send_message']))
{
    $name = mysqli_real_escape_string(
        $conn,
        $_POST['name']
    );

    $email = mysqli_real_escape_string(
        $conn,
        $_POST['email']
    );

    $subject = mysqli_real_escape_string(
        $conn,
        $_POST['subject']
    );

    $msg = mysqli_real_escape_string(
        $conn,
        $_POST['message']
    );


    if(
        $name != "" &&
        $email != "" &&
        $subject != "" &&
        $msg != ""
    )
    {

        $sql = "INSERT INTO contact_messages
                (name, email, subject, message)
                VALUES
                ('$name', '$email', '$subject', '$msg')";


        if(mysqli_query($conn, $sql))
        {
            $message = "Your message has been sent successfully!";
            $message_type = "success";
        }
        else
        {
            $message = "Something went wrong. Please try again.";
            $message_type = "error";
        }

    }
    else
    {
        $message = "Please fill all fields.";
        $message_type = "error";
    }
}

?>

<link rel="stylesheet" href="assets/css/contact.css">


<!-- =========================
     BACK BUTTON
========================= -->

<a href="javascript:history.back()" class="back-btn">

    <i class="fas fa-arrow-left"></i>

    Back

</a>


<!-- =========================
     CONTACT HEADER
========================= -->

<section class="contact-header">

    <div class="section-heading">

        <h1>CONTACT US</h1>

        <p>
            Have an event in mind? Let's create something memorable.
        </p>

    </div>

</section>


<!-- =========================
     CONTACT SECTION
========================= -->

<section class="contact-section">

    <div class="contact-container">


        <!-- CONTACT INFORMATION -->

        <div class="contact-info">

            <span class="contact-tag">
                GET IN TOUCH
            </span>

            <h2>
                Let's Plan Your
                Perfect Event
            </h2>

            <p>
                We would love to hear from you. Contact Event Crafters
                and share your event requirements with us.
            </p>


            <div class="contact-detail">

                <div class="contact-icon">

                    <i class="fas fa-envelope"></i>

                </div>

                <div>

                    <h4>Email</h4>

                    <p>
                        info@eventcrafters.com
                    </p>

                </div>

            </div>


            <div class="contact-detail">

                <div class="contact-icon">

                    <i class="fas fa-phone"></i>

                </div>

                <div>

                    <h4>Phone</h4>

                    <p>
                        +91 98765 43210
                    </p>

                </div>

            </div>


            <div class="contact-detail">

                <div class="contact-icon">

                    <i class="fas fa-location-dot"></i>

                </div>

                <div>

                    <h4>Location</h4>

                    <p>
                        Surat, Gujarat, India
                    </p>

                </div>

            </div>

        </div>


        <!-- CONTACT FORM -->

        <div class="contact-form-box">


            <?php

            if($message != "")
            {

            ?>

            <div class="form-message <?php echo $message_type; ?>">

                <?php echo $message; ?>

            </div>

            <?php

            }

            ?>


            <form method="POST">


                <div class="form-group">

                    <label>
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        placeholder="Enter your name"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Subject
                    </label>

                    <input
                        type="text"
                        name="subject"
                        placeholder="Enter subject"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Message
                    </label>

                    <textarea
                        name="message"
                        rows="6"
                        placeholder="Write your message..."
                        required
                    ></textarea>

                </div>
                <!-- =========================
     MAP
========================= -->

<div class="contact-map">

<iframe
    src="https://www.google.com/maps?q=Surat,Gujarat,India&output=embed"
    width="100%"
    height="350"
    style="border:0;"
    allowfullscreen=""
    loading="lazy">
</iframe>

</div>


                <button
                    type="submit"
                    name="send_message"
                    class="contact-submit"
                >

                    <i class="fas fa-paper-plane"></i>

                    Send Message

                </button>


            </form>

        </div>


    </div>

</section>


<?php

include "includes/footer.php";

?>