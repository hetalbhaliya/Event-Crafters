<?php

session_start();

/* =========================================
   ADMIN LOGIN CHECK
========================================= */

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}


/* =========================================
   DATABASE
========================================= */

include "../config/db.php";


/* =========================================
   EVENT ID
========================================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: events.php");
    exit;
}

$event_id = (int)$_GET['id'];


/* =========================================
   GET EVENT
========================================= */

$sql = "SELECT *
        FROM events
        WHERE id='$event_id'
        LIMIT 1";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: events.php");
    exit;
}

$event = mysqli_fetch_assoc($result);


$message = "";
$message_type = "";


/* =========================================
   UPDATE EVENT
========================================= */

if (isset($_POST['update_event'])) {

    /* =====================================
       GET FORM DATA
    ====================================== */

    $event_name = trim($_POST['event_name']);
    $category = trim($_POST['category']);
    $subcategory = trim($_POST['subcategory'] ?? "");
    $event_date = trim($_POST['event_date']);
    $event_time = trim($_POST['event_time']);
    $venue = trim($_POST['venue']);
    $price = trim($_POST['price']);


    /* =====================================
       VALIDATION
    ====================================== */

    if (
        $event_name == "" ||
        $category == "" ||
        $subcategory == "" ||
        $event_date == "" ||
        $event_time == "" ||
        $venue == "" ||
        $price == ""
    ) {

        $message = "Please fill all fields.";
        $message_type = "error";

    }

    elseif (!is_numeric($price) || $price < 0) {

        $message = "Please enter a valid event price.";
        $message_type = "error";

    }

    else {

        /* =================================
           ESCAPE DATA
        ================================== */

        $event_name = mysqli_real_escape_string(
            $conn,
            $event_name
        );

        $category = mysqli_real_escape_string(
            $conn,
            $category
        );

        $subcategory = mysqli_real_escape_string(
            $conn,
            $subcategory
        );

        $event_date = mysqli_real_escape_string(
            $conn,
            $event_date
        );

        $event_time = mysqli_real_escape_string(
            $conn,
            $event_time
        );

        $venue = mysqli_real_escape_string(
            $conn,
            $venue
        );

        $price = (float)$price;


        /* =================================
           OLD IMAGE
        ================================== */

        $old_image = $event['image'];

        $new_image_name = $old_image;


        /* =================================
           NEW IMAGE CHECK
        ================================== */

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] == 0
        ) {

            $image_name = $_FILES['image']['name'];
            $image_tmp = $_FILES['image']['tmp_name'];
            $image_size = $_FILES['image']['size'];


            /* =================================
               IMAGE EXTENSION
            ================================== */

            $image_ext = strtolower(
                pathinfo(
                    $image_name,
                    PATHINFO_EXTENSION
                )
            );


            $allowed_extensions = array(
                "jpg",
                "jpeg",
                "png",
                "webp"
            );


            /* =================================
               IMAGE VALIDATION
            ================================== */

            if (
                !in_array(
                    $image_ext,
                    $allowed_extensions
                )
            ) {

                $message =
                    "Only JPG, JPEG, PNG and WEBP images are allowed.";

                $message_type = "error";

            }

            elseif ($image_size > 5 * 1024 * 1024) {

                $message =
                    "Image size must be less than 5 MB.";

                $message_type = "error";

            }

            else {

                /* =============================
                   UPLOAD FOLDER
                ============================== */

                $upload_folder = "../uploads/events/";


                if (!is_dir($upload_folder)) {

                    mkdir(
                        $upload_folder,
                        0777,
                        true
                    );
                }


                /* =============================
                   NEW IMAGE NAME
                ============================== */

                $new_image_name =
                    time() .
                    "_" .
                    uniqid() .
                    "." .
                    $image_ext;


                $new_image_path =
                    $upload_folder .
                    $new_image_name;


                /* =============================
                   MOVE NEW IMAGE
                ============================== */

                if (!move_uploaded_file(
                    $image_tmp,
                    $new_image_path
                )) {

                    $message =
                        "Failed to upload new image.";

                    $message_type = "error";
                }
            }
        }


        /* =====================================
           UPDATE DATABASE
        ====================================== */

        if ($message_type != "error") {

            $update_sql = "UPDATE events SET

                            event_name='$event_name',
                            category='$category',
                            subcategory='$subcategory',
                            event_date='$event_date',
                            event_time='$event_time',
                            venue='$venue',
                            price='$price',
                            image='$new_image_name'

                           WHERE id='$event_id'";


            if (mysqli_query(
                $conn,
                $update_sql
            )) {

                /* =============================
                   DELETE OLD IMAGE
                ============================== */

                if (
                    $new_image_name != $old_image &&
                    !empty($old_image)
                ) {

                    $old_image_path =
                        "../uploads/events/" .
                        $old_image;


                    if (file_exists(
                        $old_image_path
                    )) {

                        unlink($old_image_path);
                    }
                }


                $message =
                    "Event updated successfully.";

                $message_type = "success";


                /* =============================
                   REFRESH EVENT DATA
                ============================== */

                $event['event_name'] = $event_name;
                $event['category'] = $category;
                $event['subcategory'] = $subcategory;
                $event['event_date'] = $event_date;
                $event['event_time'] = $event_time;
                $event['venue'] = $venue;
                $event['price'] = $price;
                $event['image'] = $new_image_name;

            }

            else {

                /* =============================
                   DELETE NEW IMAGE IF DB FAILED
                ============================== */

                if (
                    $new_image_name != $old_image &&
                    file_exists(
                        "../uploads/events/" .
                        $new_image_name
                    )
                ) {

                    unlink(
                        "../uploads/events/" .
                        $new_image_name
                    );
                }


                $message =
                    "Failed to update event.";

                $message_type = "error";
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
    href="../assets/css/admin-edit-event.css"
>


<!-- =========================================
     BACK BUTTON
========================================= -->

<a
    href="events.php"
    class="admin-back-btn"
>

    <i class="fas fa-arrow-left"></i>

    Back to Events

</a>


<!-- =========================================
     EDIT EVENT SECTION
========================================= -->

<section class="admin-edit-event-section">

    <div class="admin-edit-event-container">


        <!-- =====================================
             HEADING
        ====================================== -->

        <div class="admin-edit-event-heading">

            <span>
                ADMIN PANEL
            </span>

            <h1>
                Edit Event
            </h1>

            <p>
                Update event information and details.
            </p>

        </div>


        <!-- =====================================
             MESSAGE
        ====================================== -->

        <?php if ($message != "") { ?>

            <div
                class="admin-event-message
                <?php echo $message_type; ?>"
            >

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php } ?>


        <!-- =====================================
             FORM CARD
        ====================================== -->

        <div class="admin-edit-event-card">

            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <!-- =================================
                     EVENT NAME
                ================================== -->

                <div class="form-group">

                    <label>
                        Event Name
                    </label>

                    <input
                        type="text"
                        name="event_name"
                        value="<?php
                        echo htmlspecialchars(
                            $event['event_name']
                        );
                        ?>"
                        placeholder="Enter event name"
                        required
                    >

                </div>


                <!-- =================================
                     CATEGORY + SUBCATEGORY
                ================================== -->

                <div class="form-row">


                    <!-- CATEGORY -->

                    <div class="form-group">

                        <label>
                            Category
                        </label>

                        <select
                            name="category"
                            id="category"
                            required
                        >

                            <option value="">
                                Select Category
                            </option>

                            <?php

                            $categories = array(
                                "Wedding",
                                "Birthday",
                                "Anniversary",
                                "Corporate",
                                "Baby Shower",
                                "Engagement",
                                "Festival",
                                "Other"
                            );


                            foreach (
                                $categories
                                as $cat
                            ) {

                                $selected =
                                    (
                                        $event['category']
                                        == $cat
                                    )
                                    ? "selected"
                                    : "";

                            ?>

                                <option
                                    value="<?php
                                    echo htmlspecialchars(
                                        $cat
                                    );
                                    ?>"
                                    <?php echo $selected; ?>
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $cat
                                    );
                                    ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>


                    <!-- SUBCATEGORY -->

                    <div class="form-group">

                        <label>
                            Sub-category
                        </label>

                        <select
                            name="subcategory"
                            id="subcategory"
                            required
                        >

                            <option value="">
                                Select Sub-category
                            </option>

                        </select>

                    </div>

                </div>


                <!-- =================================
                     DATE + TIME
                ================================== -->

                <div class="form-row">

                    <div class="form-group">

                        <label>
                            Event Date
                        </label>

                        <input
                            type="date"
                            name="event_date"
                            value="<?php
                            echo htmlspecialchars(
                                $event['event_date']
                            );
                            ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Event Time
                        </label>

                        <input
                            type="time"
                            name="event_time"
                            value="<?php
                            echo htmlspecialchars(
                                $event['event_time']
                            );
                            ?>"
                            required
                        >

                    </div>

                </div>


                <!-- =================================
                     VENUE
                ================================== -->

                <div class="form-group">

                    <label>
                        Venue
                    </label>

                    <input
                        type="text"
                        name="venue"
                        value="<?php
                        echo htmlspecialchars(
                            $event['venue']
                        );
                        ?>"
                        placeholder="Enter event venue"
                        required
                    >

                </div>


                <!-- =================================
                     EVENT PRICE
                ================================== -->

                <div class="form-group">

                    <label>
                        Event Price
                    </label>

                    <div class="price-input">

                        <span>₹</span>

                        <input
                            type="number"
                            name="price"
                            min="0"
                            step="1"
                            value="<?php
                            echo htmlspecialchars(
                                $event['price']
                            );
                            ?>"
                            placeholder="Enter event price"
                            required
                        >

                    </div>

                </div>


                <!-- =================================
                     CURRENT IMAGE
                ================================== -->

                <div class="form-group">

                    <label>
                        Current Event Image
                    </label>

                    <div class="current-event-image">

                        <?php

                        if (
                            !empty($event['image']) &&
                            file_exists(
                                "../uploads/events/" .
                                $event['image']
                            )
                        ) {

                        ?>

                            <img
                                src="../uploads/events/<?php
                                echo htmlspecialchars(
                                    $event['image']
                                );
                                ?>"
                                alt="Current Event"
                            >

                        <?php

                        }
                        else {

                        ?>

                            <div class="no-current-image">

                                <i class="fas fa-image"></i>

                                <span>
                                    No image available
                                </span>

                            </div>

                        <?php

                        }

                        ?>

                    </div>

                </div>


                <!-- =================================
                     NEW IMAGE
                ================================== -->

                <div class="form-group">

                    <label>
                        Change Event Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small>
                        Leave empty if you don't want to change the image.
                    </small>

                </div>


                <!-- =================================
                     BUTTONS
                ================================== -->

                <div class="event-form-buttons">

                    <a
                        href="events.php"
                        class="cancel-event-btn"
                    >

                        Cancel

                    </a>


                    <button
                        type="submit"
                        name="update_event"
                        class="save-event-btn"
                    >

                        <i class="fas fa-save"></i>

                        Update Event

                    </button>

                </div>


            </form>

        </div>

    </div>

</section>


<!-- =========================================
     SUBCATEGORY SCRIPT
========================================= -->

<script>

const category =
    document.getElementById("category");

const subcategory =
    document.getElementById("subcategory");


const subcategories = {

    "Wedding": [
        "Floral",
        "Traditional",
        "Royal",
        "Luxury"
    ],

    "Birthday": [
        "Balloon",
        "Kids Theme",
        "Cartoon",
        "Princess"
    ],

    "Anniversary": [
        "Romantic",
        "Candle",
        "Floral"
    ],

    "Corporate": [
        "Stage",
        "Conference",
        "Branding"
    ],

    "Baby Shower": [
        "Baby Boy",
        "Baby Girl",
        "Neutral"
    ],

    "Engagement": [
        "Floral",
        "Ring Ceremony",
        "Royal"
    ],

    "Festival": [
        "Diwali",
        "Navratri",
        "Holi"
    ],

    "Other": [
        "General"
    ]

};


/* =========================================
   LOAD SUBCATEGORIES
========================================= */

function loadSubcategories(
    selectedCategory,
    selectedSubcategory = ""
) {

    subcategory.innerHTML =
        '<option value="">Select Sub-category</option>';


    if (
        subcategories[selectedCategory]
    ) {

        subcategories[selectedCategory]
            .forEach(function(item) {

                const option =
                    document.createElement("option");

                option.value = item;

                option.textContent = item;


                if (
                    item === selectedSubcategory
                ) {

                    option.selected = true;

                }


                subcategory.appendChild(option);

            });

    }

}


/* =========================================
   CATEGORY CHANGE
========================================= */

category.addEventListener(
    "change",
    function() {

        loadSubcategories(
            this.value
        );

    }
);


/* =========================================
   LOAD EXISTING SUBCATEGORY
========================================= */

loadSubcategories(
    <?php
    echo json_encode(
        $event['category']
    );
    ?>,
    <?php
    echo json_encode(
        $event['subcategory'] ?? ""
    );
    ?>
);

</script>


<?php

include "../includes/footer.php";

?>