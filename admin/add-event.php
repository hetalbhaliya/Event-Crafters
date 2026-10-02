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

$message = "";
$message_type = "";


/* =========================================
   ADD EVENT
========================================= */

if (isset($_POST['add_event'])) {

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
           IMAGE CHECK
        ================================== */

        if (
            !isset($_FILES['image']) ||
            $_FILES['image']['error'] != 0
        ) {

            $message = "Please select an event image.";
            $message_type = "error";

        }

        else {

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

            if (!in_array(
                $image_ext,
                $allowed_extensions
            )) {

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

                /* =================================
                   UPLOAD FOLDER
                ================================== */

                $upload_folder = "../uploads/events/";


                if (!is_dir($upload_folder)) {

                    mkdir(
                        $upload_folder,
                        0777,
                        true
                    );
                }


                /* =================================
                   UNIQUE IMAGE NAME
                ================================== */

                $new_image_name =
                    time() .
                    "_" .
                    uniqid() .
                    "." .
                    $image_ext;


                $image_path =
                    $upload_folder .
                    $new_image_name;


                /* =================================
                   MOVE IMAGE
                ================================== */

                if (move_uploaded_file(
                    $image_tmp,
                    $image_path
                )) {

                    /* =================================
                       INSERT EVENT
                    ================================== */

                    $sql = "INSERT INTO events
                            (
                                event_name,
                                category,
                                subcategory,
                                event_date,
                                event_time,
                                venue,
                                price,
                                image
                            )
                            VALUES
                            (
                                '$event_name',
                                '$category',
                                '$subcategory',
                                '$event_date',
                                '$event_time',
                                '$venue',
                                '$price',
                                '$new_image_name'
                            )";


                    if (mysqli_query(
                        $conn,
                        $sql
                    )) {

                        $message =
                            "Event added successfully.";

                        $message_type = "success";


                        /* =============================
                           CLEAR FORM
                        ============================= */

                        $_POST = array();

                    }

                    else {

                        /* =============================
                           DELETE IMAGE IF DB FAILED
                        ============================= */

                        if (file_exists($image_path)) {

                            unlink($image_path);
                        }


                        $message =
                            "Failed to add event.";

                        $message_type = "error";
                    }

                }

                else {

                    $message =
                        "Failed to upload image.";

                    $message_type = "error";
                }
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
    href="../assets/css/admin-add-event.css"
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
     ADD EVENT SECTION
========================================= -->

<section class="admin-add-event-section">

    <div class="admin-add-event-container">


        <!-- =====================================
             HEADING
        ====================================== -->

        <div class="admin-add-event-heading">

            <span>
                ADMIN PANEL
            </span>

            <h1>
                Add New Event
            </h1>

            <p>
                Create a new event for Event Crafters.
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

        <div class="admin-add-event-card">

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
                        placeholder="Enter event name"
                        value="<?php
                        echo isset($_POST['event_name'])
                            ? htmlspecialchars(
                                $_POST['event_name']
                            )
                            : '';
                        ?>"
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
                                        isset(
                                            $_POST['category']
                                        ) &&
                                        $_POST['category']
                                        == $cat
                                    )
                                    ? "selected"
                                    : "";

                            ?>

                                <option
                                    value="<?php
                                    echo htmlspecialchars($cat);
                                    ?>"
                                    <?php echo $selected; ?>
                                >

                                    <?php
                                    echo htmlspecialchars($cat);
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
                            echo isset($_POST['event_date'])
                                ? htmlspecialchars(
                                    $_POST['event_date']
                                )
                                : '';
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
                            echo isset($_POST['event_time'])
                                ? htmlspecialchars(
                                    $_POST['event_time']
                                )
                                : '';
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
                        placeholder="Enter event venue"
                        value="<?php
                        echo isset($_POST['venue'])
                            ? htmlspecialchars(
                                $_POST['venue']
                            )
                            : '';
                        ?>"
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
                            placeholder="Enter event price"
                            value="<?php
                            echo isset($_POST['price'])
                                ? htmlspecialchars(
                                    $_POST['price']
                                )
                                : '';
                            ?>"
                            required
                        >

                    </div>

                </div>


                <!-- =================================
                     EVENT IMAGE
                ================================== -->

                <div class="form-group">

                    <label>
                        Event Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >

                    <small>
                        JPG, JPEG, PNG or WEBP — Maximum 5 MB
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
                        name="add_event"
                        class="save-event-btn"
                    >

                        <i class="fas fa-plus"></i>

                        Add Event

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
   LOAD PREVIOUS VALUE
========================================= */

loadSubcategories(
    <?php
    echo json_encode(
        $_POST['category'] ?? ''
    );
    ?>,
    <?php
    echo json_encode(
        $_POST['subcategory'] ?? ''
    );
    ?>
);

</script>


<?php

include "../includes/footer.php";

?>