<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

include "../config/db.php";

$message = "";
$message_type = "";


/* =========================================
   FOOD ID CHECK
========================================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: foods.php");
    exit;
}

$food_id = (int)$_GET['id'];


/* =========================================
   GET FOOD
========================================= */

$food_sql = "
    SELECT
        id,
        food_name,
        description,
        price,
        image,
        status
    FROM food_items
    WHERE id = '$food_id'
    LIMIT 1
";

$food_result = mysqli_query($conn, $food_sql);

if (!$food_result || mysqli_num_rows($food_result) == 0) {
    header("Location: foods.php");
    exit;
}

$food = mysqli_fetch_assoc($food_result);


/* =========================================
   UPDATE FOOD
========================================= */

if (isset($_POST['update_food'])) {

    $food_name = trim($_POST['food_name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $status = isset($_POST['status']) ? 1 : 0;

    if ($food_name == "" || $price == "") {

        $message = "Please fill all required fields.";
        $message_type = "error";

    }
    elseif (!is_numeric($price) || $price < 0) {

        $message = "Please enter a valid price.";
        $message_type = "error";

    }
    else {

        $food_name = mysqli_real_escape_string(
            $conn,
            $food_name
        );

        $description = mysqli_real_escape_string(
            $conn,
            $description
        );

        $price = (float)$price;

        $image_name = $food['image'];


        /* =================================
           NEW IMAGE
        ================================= */

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] == 0
        ) {

            $original_name = $_FILES['image']['name'];
            $tmp_name = $_FILES['image']['tmp_name'];

            $extension = strtolower(
                pathinfo(
                    $original_name,
                    PATHINFO_EXTENSION
                )
            );

            $allowed = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];

            if (!in_array($extension, $allowed)) {

                $message = "Only JPG, JPEG, PNG and WEBP images are allowed.";
                $message_type = "error";

            }
            else {

                $upload_dir = "../uploads/events/";

                $new_image =
                    "food_" .
                    time() .
                    "_" .
                    rand(1000, 9999) .
                    "." .
                    $extension;

                if (
                    move_uploaded_file(
                        $tmp_name,
                        $upload_dir . $new_image
                    )
                ) {

                    /* DELETE OLD IMAGE */

                    if (
                        !empty($food['image']) &&
                        file_exists(
                            $upload_dir . $food['image']
                        )
                    ) {

                        unlink(
                            $upload_dir . $food['image']
                        );

                    }

                    $image_name = $new_image;

                }
                else {

                    $message = "Unable to upload image.";
                    $message_type = "error";

                }

            }

        }


        /* =================================
           UPDATE
        ================================= */

        if ($message_type != "error") {

            $update_sql = "
                UPDATE food_items
                SET
                    food_name = '$food_name',
                    description = '$description',
                    price = '$price',
                    image = '$image_name',
                    status = '$status'
                WHERE id = '$food_id'
            ";

            if (mysqli_query($conn, $update_sql)) {

                header("Location: foods.php?updated=1");
                exit;

            }
            else {

                $message = "Unable to update food item.";
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
    href="../assets/css/admin-foods.css"
>


<section class="admin-food-section">

    <div class="admin-food-container">


        <!-- =====================================
             BACK
        ====================================== -->

        <a
            href="foods.php"
            class="back-btn"
        >

            <i class="fas fa-arrow-left"></i>

            Back to Food Management

        </a>


        <!-- =====================================
             HEADING
        ====================================== -->

        <div class="admin-food-heading">

            <div>

                <span>
                    ADMIN PANEL
                </span>

                <h1>
                    Edit Food
                </h1>

                <p>
                    Update food item information and availability.
                </p>

            </div>

        </div>


        <!-- MESSAGE -->

        <?php if ($message != "") { ?>

            <div
                class="admin-food-message <?php echo $message_type; ?>"
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

        <div class="food-add-card edit-food-card">


            <div class="food-card-heading">

                <div>

                    <span>
                        UPDATE ITEM
                    </span>

                    <h2>
                        Edit Food Details
                    </h2>

                </div>

                <i class="fas fa-utensils"></i>

            </div>


            <form
                method="POST"
                enctype="multipart/form-data"
                class="food-form"
            >


                <!-- FOOD NAME -->

                <div class="food-form-group">

                    <label>
                        Food Name
                    </label>

                    <input
                        type="text"
                        name="food_name"
                        value="<?php
                            echo htmlspecialchars(
                                $food['food_name']
                            );
                        ?>"
                        maxlength="100"
                        required
                    >

                </div>


                <!-- DESCRIPTION -->

                <div class="food-form-group">

                    <label>
                        Description
                    </label>

                    <textarea
                        name="description"
                        rows="4"
                        placeholder="Enter food description"
                    ><?php
                        echo htmlspecialchars(
                            $food['description']
                        );
                    ?></textarea>

                </div>


                <!-- PRICE -->

                <div class="food-form-group">

                    <label>
                        Price
                    </label>

                    <input
                        type="number"
                        name="price"
                        value="<?php
                            echo htmlspecialchars(
                                $food['price']
                            );
                        ?>"
                        min="0"
                        step="0.01"
                        required
                    >

                </div>


                <!-- CURRENT IMAGE -->

                <div class="food-form-group">

                    <label>
                        Current Image
                    </label>

                    <?php if (!empty($food['image'])) { ?>

                        <div class="current-food-image">

                            <img
                                src="../uploads/events/<?php
                                    echo htmlspecialchars(
                                        $food['image']
                                    );
                                ?>"
                                alt="<?php
                                    echo htmlspecialchars(
                                        $food['food_name']
                                    );
                                ?>"
                            >

                        </div>

                    <?php } else { ?>

                        <div class="food-no-image edit-no-image">

                            <i class="fas fa-utensils"></i>

                        </div>

                    <?php } ?>

                </div>


                <!-- NEW IMAGE -->

                <div class="food-form-group">

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


                <!-- STATUS -->

                <div class="food-form-group">

                    <label class="food-status-check">

                        <input
                            type="checkbox"
                            name="status"
                            value="1"
                            <?php
                            if ((int)$food['status'] == 1) {
                                echo "checked";
                            }
                            ?>
                        >

                        <span>
                            Food item is Active
                        </span>

                    </label>

                </div>


                <!-- ACTIONS -->

                <div class="food-form-actions">

                    <a
                        href="foods.php"
                        class="cancel-btn"
                    >

                        <i class="fas fa-times-circle"></i>

                        Cancel

                    </a>


                    <button
                        type="submit"
                        name="update_food"
                        class="add-food-btn"
                    >

                        <i class="fas fa-save"></i>

                        Update Food

                    </button>

                </div>


            </form>

        </div>


    </div>

</section>


<?php

include "../includes/footer.php";

?>