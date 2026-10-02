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
   ADD GALLERY IMAGE
========================================= */

if (isset($_POST['add_gallery'])) {

    $title = trim($_POST['title']);

    if ($title == "") {

        $message = "Please enter gallery title.";
        $message_type = "error";

    }
    elseif (
        !isset($_FILES['image']) ||
        $_FILES['image']['error'] != 0
    ) {

        $message = "Please select an image.";
        $message_type = "error";

    }
    else {

        $title = mysqli_real_escape_string(
            $conn,
            $title
        );


        /* =================================
           IMAGE DETAILS
        ================================= */

        $original_name = $_FILES['image']['name'];
        $tmp_name = $_FILES['image']['tmp_name'];

        $extension = strtolower(
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


        if (!in_array($extension, $allowed)) {

            $message =
                "Only JPG, JPEG, PNG and WEBP images are allowed.";

            $message_type = "error";

        }
        else {

            /* =================================
               UPLOAD DIRECTORY
            ================================= */

            $upload_dir = "../uploads/gallery/";


            /* CREATE DIRECTORY IF NOT EXISTS */

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

            $image_name =
                "gallery_" .
                time() .
                "_" .
                rand(1000, 9999) .
                "." .
                $extension;


            /* =================================
               MOVE IMAGE
            ================================= */

            if (
                move_uploaded_file(
                    $tmp_name,
                    $upload_dir . $image_name
                )
            ) {


                /* =================================
                   INSERT DATABASE
                ================================= */

                $sql = "
                    INSERT INTO gallery
                    (
                        title,
                        image,
                        created_at
                    )
                    VALUES
                    (
                        '$title',
                        '$image_name',
                        NOW()
                    )
                ";


                if (mysqli_query($conn, $sql)) {

                    $message =
                        "Gallery image added successfully.";

                    $message_type = "success";

                }
                else {

                    /* DELETE UPLOADED IMAGE
                       IF DATABASE INSERT FAILS */

                    if (
                        file_exists(
                            $upload_dir . $image_name
                        )
                    ) {

                        unlink(
                            $upload_dir . $image_name
                        );

                    }


                    $message =
                        "Unable to save gallery image.";

                    $message_type = "error";

                }

            }
            else {

                $message =
                    "Unable to upload image.";

                $message_type = "error";

            }

        }

    }

}


/* =========================================
   DELETE GALLERY IMAGE
========================================= */

if (isset($_GET['delete'])) {

    $gallery_id =
        (int)$_GET['delete'];


    if ($gallery_id > 0) {


        /* =================================
           GET IMAGE NAME
        ================================= */

        $image_sql = "
            SELECT image
            FROM gallery
            WHERE id = '$gallery_id'
            LIMIT 1
        ";


        $image_result =
            mysqli_query(
                $conn,
                $image_sql
            );


        $image = "";


        if (
            $image_result &&
            mysqli_num_rows($image_result) > 0
        ) {

            $image_row =
                mysqli_fetch_assoc(
                    $image_result
                );

            $image =
                $image_row['image'];

        }


        /* =================================
           DELETE DATABASE RECORD
        ================================= */

        $delete_sql = "
            DELETE FROM gallery
            WHERE id = '$gallery_id'
        ";


        if (
            mysqli_query(
                $conn,
                $delete_sql
            )
        ) {


            /* =================================
               DELETE IMAGE FILE
            ================================= */

            if (!empty($image)) {

                $image_path =
                    "../uploads/gallery/" .
                    $image;


                if (file_exists($image_path)) {

                    unlink($image_path);

                }

            }


            header(
                "Location: gallery.php?deleted=1"
            );

            exit;

        }

    }

}


/* =========================================
   DELETE MESSAGE
========================================= */

if (isset($_GET['deleted'])) {

    $message =
        "Gallery image deleted successfully.";

    $message_type = "success";

}


/* =========================================
   GET GALLERY
========================================= */

$gallery_sql = "
    SELECT
        id,
        title,
        image,
        created_at
    FROM gallery
    ORDER BY id DESC
";


$gallery_result =
    mysqli_query(
        $conn,
        $gallery_sql
    );


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
     GALLERY SECTION
========================================= -->

<section class="admin-gallery-section">

    <div class="admin-gallery-container">


        <!-- =====================================
             BACK BUTTON
        ====================================== -->

        <a
            href="dashboard.php"
            class="back-btn"
        >

            <i class="fas fa-arrow-left"></i>

            Back to Dashboard

        </a>


        <!-- =====================================
             HEADING
        ====================================== -->

        <div class="admin-gallery-heading">

            <span>
                ADMIN PANEL
            </span>

            <h1>
                Gallery Management
            </h1>

            <p>
                Upload and manage event gallery images.
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
             ADD GALLERY
        ====================================== -->

        <div class="gallery-add-card">


            <div class="gallery-card-heading">

                <div>

                    <span>
                        NEW IMAGE
                    </span>

                    <h2>
                        Add Gallery Image
                    </h2>

                </div>


                <i class="fas fa-images"></i>

            </div>


            <form
                method="POST"
                enctype="multipart/form-data"
                class="gallery-form"
            >


                <!-- TITLE -->

                <div class="gallery-form-group">

                    <label>
                        Image Title
                    </label>

                    <input
                        type="text"
                        name="title"
                        placeholder="Enter image title"
                        maxlength="150"
                        required
                    >

                </div>


                <!-- IMAGE -->

                <div class="gallery-form-group">

                    <label>
                        Gallery Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >

                    <small>
                        JPG, JPEG, PNG or WEBP
                    </small>

                </div>


                <!-- BUTTON -->

                <div class="gallery-form-actions">

                    <button
                        type="submit"
                        name="add_gallery"
                        class="add-gallery-btn"
                    >

                        <i class="fas fa-plus"></i>

                        Add Image

                    </button>

                </div>


            </form>

        </div>


        <!-- =====================================
             GALLERY LIST
        ====================================== -->

        <div class="gallery-list-card">


            <div class="gallery-card-heading">

                <div>

                    <span>
                        AVAILABLE IMAGES
                    </span>

                    <h2>
                        Gallery
                    </h2>

                </div>


                <i class="fas fa-photo-film"></i>

            </div>


            <?php

            if (
                $gallery_result &&
                mysqli_num_rows($gallery_result) > 0
            ) {

            ?>


                <div class="gallery-grid">


                <?php

                while (
                    $gallery =
                    mysqli_fetch_assoc(
                        $gallery_result
                    )
                ) {

                ?>


                    <div class="gallery-admin-card">


                        <!-- IMAGE -->

                        <div class="gallery-admin-image">

                            <?php if (
                                !empty(
                                    $gallery['image']
                                )
                            ) { ?>

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

                            <?php } else { ?>

                                <div class="gallery-no-image">

                                    <i class="fas fa-image"></i>

                                </div>

                            <?php } ?>


                        </div>


                        <!-- CONTENT -->

                        <div class="gallery-admin-body">


                            <h3>

                                <?php

                                echo htmlspecialchars(
                                    $gallery['title']
                                );

                                ?>

                            </h3>


                            <p>

                                <i class="fas fa-calendar"></i>

                                <?php

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $gallery['created_at']
                                    )
                                );

                                ?>

                            </p>


                            <!-- ACTION -->

                            <div class="gallery-admin-actions">


                                <a
                                    href="edit-gallery.php?id=<?php
                                        echo (int)$gallery['id'];
                                    ?>"
                                    class="gallery-edit-btn"
                                >

                                    <i class="fas fa-pen"></i>

                                    Edit

                                </a>


                                <a
                                    href="gallery.php?delete=<?php
                                        echo (int)$gallery['id'];
                                    ?>"
                                    class="gallery-delete-btn"
                                    onclick="
                                        return confirm(
                                            'Are you sure you want to delete this gallery image?'
                                        );
                                    "
                                >

                                    <i class="fas fa-trash"></i>

                                    Delete

                                </a>


                            </div>


                        </div>


                    </div>


                <?php

                }

                ?>


                </div>


            <?php

            }
            else {

            ?>


                <div class="gallery-empty">

                    <div>

                        <i class="fas fa-images"></i>

                    </div>

                    <h3>
                        No Gallery Images
                    </h3>

                    <p>
                        Add your first gallery image using the form above.
                    </p>

                </div>


            <?php

            }

            ?>


        </div>


    </div>

</section>


<?php

include "../includes/footer.php";

?>