<a href="dashboard.php" class="back-btn">
    <i class="fas fa-arrow-left"></i>
   Back To Dashboard
</a>
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
   ADD FOOD
========================================= */

if (isset($_POST['add_food'])) {

    $food_name = trim($_POST['food_name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);

    if ($food_name == "" || $price == "") {

        $message = "Please fill all required fields.";
        $message_type = "error";

    }
    elseif (!is_numeric($price) || $price < 0) {

        $message = "Please enter a valid price.";
        $message_type = "error";

    }
    else {

        $food_name = mysqli_real_escape_string($conn, $food_name);
        $description = mysqli_real_escape_string($conn, $description);
        $price = (float)$price;

        $image_name = "";


        /* =====================================
           IMAGE UPLOAD
        ===================================== */

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] == 0
        ) {

            $upload_dir = "../uploads/events/";


            /* Create folder if not exists */

            if (!is_dir($upload_dir)) {

                mkdir(
                    $upload_dir,
                    0777,
                    true
                );

            }


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

                $message =
                    "Only JPG, JPEG, PNG and WEBP images are allowed.";

                $message_type = "error";

            }
            else {

                $image_name =
                    "food_" .
                    time() .
                    "_" .
                    rand(1000, 9999) .
                    "." .
                    $extension;


                $target_file =
                    $upload_dir . $image_name;


                if (
                    !move_uploaded_file(
                        $tmp_name,
                        $target_file
                    )
                ) {

                    $message =
                        "Food image upload failed.";

                    $message_type = "error";

                    $image_name = "";

                }

            }

        }


        /* =====================================
           INSERT FOOD
        ===================================== */

        if ($message_type != "error") {

            $sql = "
                INSERT INTO food_items
                (
                    food_name,
                    description,
                    price,
                    image,
                    status
                )
                VALUES
                (
                    '$food_name',
                    '$description',
                    '$price',
                    '$image_name',
                    1
                )
            ";


            if (mysqli_query($conn, $sql)) {

                $message =
                    "Food item added successfully.";

                $message_type = "success";

            }
            else {

                $message =
                    "Unable to add food item.";

                $message_type = "error";

            }

        }

    }

}


/* =========================================
   DELETE FOOD
========================================= */

if (isset($_GET['delete'])) {

    $food_id = (int)$_GET['delete'];


    if ($food_id > 0) {

        /* Get image */

        $image_sql = "
            SELECT image
            FROM food_items
            WHERE id = '$food_id'
            LIMIT 1
        ";


        $image_result = mysqli_query(
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


        /* Delete database record */

        $delete_sql = "
            DELETE FROM food_items
            WHERE id = '$food_id'
        ";


        if (mysqli_query($conn, $delete_sql)) {


            /* Delete image */

            if (
                !empty($image) &&
                file_exists(
                    "../uploads/events/" . $image
                )
            ) {

                unlink(
                    "../uploads/events/" . $image
                );

            }


            header(
                "Location: foods.php?deleted=1"
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
        "Food item deleted successfully.";

    $message_type = "success";

}


/* =========================================
   TOGGLE STATUS
========================================= */

if (isset($_GET['toggle'])) {

    $food_id = (int)$_GET['toggle'];


    if ($food_id > 0) {

        $toggle_sql = "
            UPDATE food_items
            SET status =
                CASE
                    WHEN status = 1 THEN 0
                    ELSE 1
                END
            WHERE id = '$food_id'
        ";


        mysqli_query(
            $conn,
            $toggle_sql
        );


        header(
            "Location: foods.php"
        );

        exit;

    }

}


/* =========================================
   GET FOOD ITEMS
========================================= */

$food_sql = "
    SELECT
        id,
        food_name,
        description,
        price,
        image,
        status,
        created_at
    FROM food_items
    ORDER BY id DESC
";


$food_result = mysqli_query(
    $conn,
    $food_sql
);


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


        <!-- PAGE HEADING -->

        <div class="admin-food-heading">

            <div>

                <span>
                    ADMIN PANEL
                </span>

                <h1>
                    Food Management
                </h1>

                <p>
                    Add and manage food items available for event bookings.
                </p>

            </div>

        </div>


        <!-- MESSAGE -->

        <?php if ($message != "") { ?>

            <div class="admin-food-message <?php echo $message_type; ?>">

                <?php if ($message_type == "success") { ?>

                    <i class="fas fa-circle-check"></i>

                <?php } else { ?>

                    <i class="fas fa-circle-exclamation"></i>

                <?php } ?>

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php } ?>


        <!-- ADD FOOD -->

        <div class="food-add-card">

            <div class="food-card-heading">

                <div>

                    <span>
                        NEW ITEM
                    </span>

                    <h2>
                        Add Food
                    </h2>

                </div>

                <i class="fas fa-utensils"></i>

            </div>


            <form
                method="POST"
                enctype="multipart/form-data"
                class="food-form"
            >


                <div class="food-form-group">

                    <label>
                        Food Name
                    </label>

                    <input
                        type="text"
                        name="food_name"
                        placeholder="Enter food name"
                        maxlength="100"
                        required
                    >

                </div>


                <div class="food-form-group">

                    <label>
                        Description
                    </label>

                    <textarea
                        name="description"
                        rows="4"
                        placeholder="Enter food description"
                    ></textarea>

                </div>


                <div class="food-form-group">

                    <label>
                        Price
                    </label>

                    <input
                        type="number"
                        name="price"
                        placeholder="Enter price"
                        min="0"
                        step="0.01"
                        required
                    >

                </div>


                <div class="food-form-group">

                    <label>
                        Food Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small>
                        JPG, JPEG, PNG or WEBP
                    </small>

                </div>


                <div class="food-form-actions">

                    <button
                        type="submit"
                        name="add_food"
                        class="add-food-btn"
                    >

                        <i class="fas fa-plus"></i>

                        Add Food

                    </button>

                </div>


            </form>

        </div>


        <!-- FOOD LIST -->

        <div class="food-list-card">

            <div class="food-card-heading">

                <div>

                    <span>
                        AVAILABLE ITEMS
                    </span>

                    <h2>
                        Food Items
                    </h2>

                </div>

                <i class="fas fa-bowl-food"></i>

            </div>


            <?php if (
                $food_result &&
                mysqli_num_rows($food_result) > 0
            ) { ?>


                <div class="food-table-wrapper">

                    <table class="food-table">

                        <thead>

                            <tr>

                                <th>Image</th>
                                <th>Food Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php while (
                            $food =
                            mysqli_fetch_assoc(
                                $food_result
                            )
                        ) {

                            $food_status =
                                (int)$food['status'];

                        ?>

                            <tr>


                                <!-- IMAGE -->

                                <td>

                                    <?php if (
                                        !empty(
                                            $food['image']
                                        )
                                    ) { ?>

                                        <img
                                            src="../uploads/events/<?php
                                                echo htmlspecialchars(
                                                    $food['image']
                                                );
                                            ?>"
                                            class="food-table-image"
                                            alt="<?php
                                                echo htmlspecialchars(
                                                    $food['food_name']
                                                );
                                            ?>"
                                        >

                                    <?php } else { ?>

                                        <div class="food-no-image">

                                            <i class="fas fa-utensils"></i>

                                        </div>

                                    <?php } ?>

                                </td>


                                <!-- NAME -->

                                <td>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $food['food_name']
                                        );
                                        ?>

                                    </strong>

                                </td>


                                <!-- DESCRIPTION -->

                                <td>

                                    <p class="food-description">

                                        <?php
                                        echo htmlspecialchars(
                                            $food['description']
                                        );
                                        ?>

                                    </p>

                                </td>


                                <!-- PRICE -->

                                <td>

                                    <strong class="food-price">

                                        ₹<?php
                                        echo number_format(
                                            (float)$food['price'],
                                            2
                                        );
                                        ?>

                                    </strong>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <?php if (
                                        $food_status == 1
                                    ) { ?>

                                        <span class="food-active">

                                            <i class="fas fa-circle"></i>

                                            Active

                                        </span>

                                    <?php } else { ?>

                                        <span class="food-inactive">

                                            <i class="fas fa-circle"></i>

                                            Inactive

                                        </span>

                                    <?php } ?>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="food-actions">

                                        <a
                                            href="foods.php?toggle=<?php
                                                echo (int)$food['id'];
                                            ?>"
                                            class="food-toggle-btn"
                                        >

                                            <?php if (
                                                $food_status == 1
                                            ) { ?>

                                                <i class="fas fa-eye-slash"></i>
                                                Disable

                                            <?php } else { ?>

                                                <i class="fas fa-eye"></i>
                                                Enable

                                            <?php } ?>

                                        </a>


                                        <a
                                            href="edit-food.php?id=<?php
                                                echo (int)$food['id'];
                                            ?>"
                                            class="food-edit-btn"
                                        >

                                            <i class="fas fa-pen"></i>
                                            Edit

                                        </a>


                                        <a
                                            href="foods.php?delete=<?php
                                                echo (int)$food['id'];
                                            ?>"
                                            class="food-delete-btn"
                                            onclick="return confirm('Are you sure you want to delete this food item?');"
                                        >

                                            <i class="fas fa-trash"></i>
                                            Delete

                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php } ?>

                        </tbody>

                    </table>

                </div>


            <?php } else { ?>


                <div class="food-empty">

                    <div>
                        <i class="fas fa-utensils"></i>
                    </div>

                    <h3>
                        No Food Items
                    </h3>

                    <p>
                        Add your first food item using the form above.
                    </p>

                </div>


            <?php } ?>


        </div>

    </div>

</section>


<?php

include "../includes/footer.php";

?>