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
   MESSAGE
========================================= */

$message = "";
$message_type = "";


/* =========================================
   GALLERY ID CHECK
========================================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: gallery.php");
    exit;

}

$gallery_id = (int)$_GET['id'];


/* =========================================
   GET GALLERY IMAGE
========================================= */

$sql = "
    SELECT
        id,
        title,
        image,
        created_at
    FROM gallery
    WHERE id = '$gallery_id'
    LIMIT 1
";

$result = mysqli_query($conn, $sql);


if (!$result || mysqli_num_rows($result) == 0) {

    header("Location: gallery.php");
    exit;

}


$gallery = mysqli_fetch_assoc($result);


/* =========================================
   UPDATE GALLERY
========================================= */

if (isset($_POST['update_gallery'])) {

    $title = trim($_POST['title']);


    /* =====================================
       VALIDATION
    ===================================== */

    if ($title == "") {

        $message = "Please enter image title.";
        $message_type = "error";

    }
    else {

        $title = mysqli_real_escape_string(
            $conn,
            $title
        );


        /* =====================================
           OLD IMAGE
        ===================================== */

        $image_name = $gallery['image'];


        /* =====================================
           NEW IMAGE CHECK
        ===================================== */

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] == 0
        ) {


            $original_name =
                $_FILES['image']['name'];

            $tmp_name =
                $_FILES['image']['tmp_name'];


            $extension =
                strtolower(
                    pathinfo(
                        $original_name,
                        PATHINFO_EXTENSION
                    )
                );


            $allowed = array(
                "jpg",
                "jpeg",
                "png",
                "webp"
            );


            /* =================================
               CHECK EXTENSION
            ================================= */

            if (!in_array($extension, $allowed)) {

                $message =
                    "Only JPG, JPEG, PNG and WEBP images are allowed.";

                $message_type = "error";

            }
            else {


                /* =================================
                   UPLOAD DIRECTORY
                ================================= */

                $upload_dir =
                    "../uploads/gallery/";


                if (!is_dir($upload_dir)) {

                    mkdir(
                        $upload_dir,
                        0777,
                        true
                    );

                }


                /* =================================
                   NEW IMAGE NAME
                ================================= */

                $new_image =
                    "gallery_" .
                    time() .
                    "_" .
                    rand(1000, 9999) .
                    "." .
                    $extension;


                /* =================================
                   UPLOAD
                ================================= */

                if (
                    move_uploaded_file(
                        $tmp_name,
                        $upload_dir . $new_image
                    )
                ) {


                    /* =================================
                       DELETE OLD IMAGE
                    ================================= */

                    if (
                        !empty($gallery['image'])
                    ) {

                        $old_image =
                            $upload_dir .
                            $gallery['image'];


                        if (
                            file_exists(
                                $old_image
                            )
                        ) {

                            unlink($old_image);

                        }

                    }


                    $image_name =
                        $new_image;

                }
                else {

                    $message =
                        "Unable to upload new image.";

                    $message_type =
                        "error";

                }

            }

        }


        /* =====================================
           UPDATE DATABASE
        ===================================== */

        if ($message_type != "error") {

            $update_sql = "
                UPDATE gallery
                SET
                    title = '$title',
                    image = '$image_name'
                WHERE id = '$gallery_id'
            ";


            if (
                mysqli_query(
                    $conn,
                    $update_sql
                )
            ) {

                header(
                    "Location: gallery.php?updated=1"
                );

                exit;

            }
            else {

                $message =
                    "Unable to update gallery image.";

                $message_type =
                    "error";

            }

        }

    }

}


/* =========================================
   HEADER
========================================= */

include "../includes/header.php";

?>

<link
    rel="stylesheet"
    href="../assets/css/admin-gallery.css"
>


<!-- =========================================
     EDIT GALLERY SECTION
========================================= -->

<section class="admin-gallery-section">

    <div class="admin-gallery-container">


        <!-- =====================================
             BACK
        ====================================== -->

        <a
            href="gallery.php"
            class="gallery-back-btn"
        >

            <i class="fas fa-arrow-left"></i>

            Back to Gallery

        </a>


        <!-- =====================================
             HEADING
        ====================================== -->

        <div class="admin-gallery-heading">

            <span>
                ADMIN PANEL
            </span>

            <h1>
                Edit Gallery
            </h1>

            <p>
                Update gallery image information.
            </p>

        </div>


        <!-- =====================================
             MESSAGE
        ====================================== -->

        <?php if ($message != "") { ?>

            <div
                class="admin-gallery-message
                <?php echo $message_type; ?>"
            >

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
             EDIT CARD
        ====================================== -->

        <div class="gallery-add-card">


            <div class="gallery-card-heading">

                <div>

                    <span>
                        UPDATE IMAGE
                    </span>

                    <h2>
                        Edit Gallery Details
                    </h2>

                </div>

                <i class="fas fa-images"></i>

            </div>


            <form
                method="POST"
                enctype="multipart/form-data"
                class="gallery-form"
            >


                <!-- =================================
                     TITLE
                ================================== -->

                <div class="gallery-form-group">

                    <label>
                        Image Title
                    </label>

                    <input
                        type="text"
                        name="title"
                        value="<?php
                            echo htmlspecialchars(
                                $gallery['title']
                            );
                        ?>"
                        maxlength="150"
                        required
                    >

                </div>


                <!-- =================================
                     CURRENT IMAGE
                ================================== -->

                <div class="gallery-form-group">

                    <label>
                        Current Image
                    </label>


                    <?php if (
                        !empty(
                            $gallery['image']
                        )
                    ) { ?>

                        <div
                            class="current-gallery-image"
                        >

                            <img
                                src="../uploads/gallery/<?php
                                    echo htmlspecialchars(
                                        $gallery['image']
                                    );
                                ?>"
                                alt="<?php
                                    echo htmlspecialchars(
                                        $gallery['title']
                                    );
                                ?>"
                            >

                        </div>

                    <?php } else { ?>

                        <div
                            class="gallery-no-image"
                        >

                            <i class="fas fa-image"></i>

                        </div>

                    <?php } ?>

                </div>


                <!-- =================================
                     CHANGE IMAGE
                ================================== -->

                <div class="gallery-form-group">

                    <label>
                        Change Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small>
                        Leave empty to keep the current image.
                    </small>

                </div>


                <!-- =================================
                     ACTIONS
                ================================== -->

                <div
                    class="gallery-form-actions"
                >


                    <a
                        href="gallery.php"
                        class="gallery-cancel-btn"
                    >

                        <i class="fas fa-xmark"></i>

                        Cancel

                    </a>


                    <button
                        type="submit"
                        name="update_gallery"
                        class="add-gallery-btn"
                    >

                        <i class="fas fa-save"></i>

                        Update Image

                    </button>


                </div>


            </form>


        </div>


    </div>

</section>


<?php

include "../includes/footer.php";

?>