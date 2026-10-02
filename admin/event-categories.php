<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

include "../config/db.php";

$message = "";
$message_type = "";


/* =========================================================
   INSERT
========================================================= */

if (isset($_POST['add_category'])) {

    $category = trim($_POST['category_name']);
    $subcategory = trim($_POST['subcategory_name']);

    if ($category == "" || $subcategory == "") {

        $message = "Please enter category and subcategory.";
        $message_type = "error";

    } else {

        $category = mysqli_real_escape_string($conn, $category);
        $subcategory = mysqli_real_escape_string($conn, $subcategory);

        $check = mysqli_query(
            $conn,
            "SELECT id FROM event_categories
             WHERE category_name = '$category'
             AND subcategory_name = '$subcategory'
             LIMIT 1"
        );

        if (mysqli_num_rows($check) > 0) {

            $message = "This category and subcategory already exists.";
            $message_type = "error";

        } else {

            $insert = mysqli_query(
                $conn,
                "INSERT INTO event_categories
                (category_name, subcategory_name, status)
                VALUES
                ('$category', '$subcategory', 1)"
            );

            if ($insert) {

                header("Location: event-categories.php?success=added");
                exit;

            } else {

                $message = "Unable to add category.";
                $message_type = "error";
            }
        }
    }
}


/* =========================================================
   UPDATE
========================================================= */

if (isset($_POST['update_category'])) {

    $id = (int)$_POST['category_id'];

    $category = trim($_POST['category_name']);
    $subcategory = trim($_POST['subcategory_name']);

    if ($id <= 0 || $category == "" || $subcategory == "") {

        $message = "Please enter valid category details.";
        $message_type = "error";

    } else {

        $category = mysqli_real_escape_string($conn, $category);
        $subcategory = mysqli_real_escape_string($conn, $subcategory);

        $check = mysqli_query(
            $conn,
            "SELECT id FROM event_categories
             WHERE category_name = '$category'
             AND subcategory_name = '$subcategory'
             AND id != $id
             LIMIT 1"
        );

        if (mysqli_num_rows($check) > 0) {

            $message = "This category and subcategory already exists.";
            $message_type = "error";

        } else {

            $update = mysqli_query(
                $conn,
                "UPDATE event_categories
                 SET
                    category_name = '$category',
                    subcategory_name = '$subcategory'
                 WHERE id = $id"
            );

            if ($update) {

                header("Location: event-categories.php?success=updated");
                exit;

            } else {

                $message = "Unable to update category.";
                $message_type = "error";
            }
        }
    }
}


/* =========================================================
   DELETE
========================================================= */

if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    if ($id > 0) {

        mysqli_query(
            $conn,
            "DELETE FROM event_categories WHERE id = $id"
        );
    }

    header("Location: event-categories.php?success=deleted");
    exit;
}


/* =========================================================
   ACTIVE / INACTIVE
========================================================= */

if (isset($_GET['toggle'])) {

    $id = (int)$_GET['toggle'];

    if ($id > 0) {

        mysqli_query(
            $conn,
            "UPDATE event_categories
             SET status = IF(status = 1, 0, 1)
             WHERE id = $id"
        );
    }

    header("Location: event-categories.php");
    exit;
}


/* =========================================================
   SUCCESS MESSAGE
========================================================= */

if (isset($_GET['success'])) {

    if ($_GET['success'] == "added") {

        $message = "Category added successfully.";
        $message_type = "success";

    } elseif ($_GET['success'] == "updated") {

        $message = "Category updated successfully.";
        $message_type = "success";

    } elseif ($_GET['success'] == "deleted") {

        $message = "Category deleted successfully.";
        $message_type = "success";
    }
}


/* =========================================================
   EDIT DATA
========================================================= */

$edit_id = 0;
$edit_category = "";
$edit_subcategory = "";

if (isset($_GET['edit'])) {

    $edit_id = (int)$_GET['edit'];

    if ($edit_id > 0) {

        $edit_query = mysqli_query(
            $conn,
            "SELECT *
             FROM event_categories
             WHERE id = $edit_id
             LIMIT 1"
        );

        if ($edit_query && mysqli_num_rows($edit_query) > 0) {

            $edit_data = mysqli_fetch_assoc($edit_query);

            $edit_category = $edit_data['category_name'];
            $edit_subcategory = $edit_data['subcategory_name'];
        }
    }
}


/* =========================================================
   FETCH ALL CATEGORIES
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT *
     FROM event_categories
     ORDER BY category_name ASC, subcategory_name ASC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Event Categories | Event Crafters</title>

    <!-- Google Fonts -->

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
          rel="stylesheet">


    <!-- Font Awesome -->

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">


   

</head>
<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 0;
    background: #080c18;
    color: #ffffff;
    font-family: "Poppins", sans-serif;
}


/* =========================
   MAIN SECTION
========================= */

.category-section {
    min-height: 100vh;
    padding: 60px 6% 90px;
}

.category-container {
    width: 100%;
    max-width: 1200px;
    margin: auto;
}


/* =========================
   BACK BUTTON
========================= */

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;

    margin-bottom: 28px;

    color: #35D6FF;

    font-size: 14px;
    font-weight: 600;

    text-decoration: none;

    transition: .3s ease;
}

.back-btn i {
    transition: .3s ease;
}

.back-btn:hover {
    color: #ffffff;
    text-decoration: none;
}

.back-btn:hover i {
    transform: translateX(-4px);
}


/* =========================
   PAGE HEADING
========================= */

.page-heading {
    margin-bottom: 35px;
}

.page-heading span {
    display: block;

    margin-bottom: 8px;

    color: #35D6FF;

    font-size: 12px;
    font-weight: 600;

    letter-spacing: 2px;
}

.page-heading h1 {
    margin: 0 0 10px;

    font-family: "Cinzel", serif;

    font-size: 38px;
    font-weight: 600;

    color: #ffffff;
}

.page-heading p {
    margin: 0;

    color: #8992A8;

    font-size: 14px;
}


/* =========================
   MESSAGE
========================= */

.message {
    padding: 14px 18px;

    margin-bottom: 25px;

    border-radius: 10px;

    font-size: 13px;
    font-weight: 500;
}

.message.success {
    color: #35D6FF;

    background: rgba(53, 214, 255, .08);

    border: 1px solid rgba(53, 214, 255, .2);
}

.message.error {
    color: #ff8b9e;

    background: rgba(255, 80, 110, .08);

    border: 1px solid rgba(255, 80, 110, .2);
}


/* =========================
   FORM CARD
========================= */

.category-card {
    padding: 28px;

    margin-bottom: 30px;

    background: rgba(15, 21, 38, .95);

    border: 1px solid rgba(255, 255, 255, .08);

    border-radius: 18px;

    box-shadow: 0 20px 50px rgba(0, 0, 0, .25);
}

.category-card-title {
    display: flex;
    align-items: center;
    gap: 10px;

    margin-bottom: 22px;
}

.category-card-title i {
    color: #35D6FF;
    font-size: 18px;
}

.category-card-title h3 {
    margin: 0;

    font-family: "Cinzel", serif;

    font-size: 20px;
    font-weight: 600;
}


/* =========================
   FORM
========================= */

.category-form {
    display: grid;

    grid-template-columns: 1fr 1fr auto;

    gap: 18px;

    align-items: end;
}

.form-group {
    margin: 0;
}

.form-group label {
    display: block;

    margin-bottom: 8px;

    color: #cbd2df;

    font-size: 13px;
    font-weight: 500;
}

.form-control {
    width: 100%;

    height: 48px;

    padding: 0 15px;

    background: #0b1020;

    border: 1px solid rgba(255, 255, 255, .1);

    border-radius: 10px;

    outline: none;

    color: #ffffff;

    font-family: "Poppins", sans-serif;

    font-size: 13px;

    transition: .3s ease;
}

.form-control::placeholder {
    color: #626c80;
}

.form-control:focus {
    border-color: #35D6FF;

    box-shadow: 0 0 15px rgba(53, 214, 255, .12);
}


/* =========================
   ADD / UPDATE BUTTON
========================= */

.submit-btn {
    height: 48px;

    padding: 0 25px;

    border: none;

    border-radius: 10px;

    background: linear-gradient(
        135deg,
        #9B5DE5,
        #35D6FF
    );

    color: #ffffff;

    font-family: "Poppins", sans-serif;

    font-size: 13px;
    font-weight: 600;

    cursor: pointer;

    white-space: nowrap;

    transition: .3s ease;
}

.submit-btn:hover {
    transform: translateY(-2px);

    box-shadow:
        0 8px 25px rgba(53, 214, 255, .2);
}


/* =========================
   CANCEL BUTTON
========================= */

.cancel-btn {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    height: 48px;

    padding: 0 20px;

    margin-left: 8px;

    border: 1px solid rgba(255, 255, 255, .12);

    border-radius: 10px;

    color: #b8c0d0;

    font-size: 13px;
    font-weight: 500;

    text-decoration: none;

    transition: .3s ease;
}

.cancel-btn:hover {
    color: #ffffff;

    border-color: #35D6FF;

    text-decoration: none;
}


/* =========================
   TABLE
========================= */

.table-wrapper {
    width: 100%;

    overflow-x: auto;
}

.category-table {
    width: 100%;

    border-collapse: collapse;

    min-width: 700px;
}

.category-table th {
    padding: 15px;

    text-align: left;

    color: #35D6FF;

    font-size: 11px;
    font-weight: 600;

    letter-spacing: 1px;

    text-transform: uppercase;

    border-bottom: 1px solid rgba(255, 255, 255, .1);
}

.category-table td {
    padding: 16px 15px;

    color: #cbd2df;

    font-size: 13px;

    border-bottom: 1px solid rgba(255, 255, 255, .06);

    vertical-align: middle;
}

.category-table tbody tr {
    transition: .25s ease;
}

.category-table tbody tr:hover {
    background: rgba(53, 214, 255, .025);
}

.number-cell {
    width: 60px;

    color: #69748a !important;
}

.category-name {
    color: #ffffff !important;

    font-weight: 600;
}

.subcategory-name {
    color: #aeb7c9 !important;
}


/* =========================
   STATUS
========================= */

.status-badge {
    display: inline-flex;

    align-items: center;

    padding: 5px 12px;

    border-radius: 30px;

    font-size: 10px;
    font-weight: 600;
}

.status-badge.active {
    color: #35D6FF;

    background: rgba(53, 214, 255, .08);

    border: 1px solid rgba(53, 214, 255, .2);
}

.status-badge.inactive {
    color: #ff8b9e;

    background: rgba(255, 80, 110, .08);

    border: 1px solid rgba(255, 80, 110, .2);
}


/* =========================
   ACTION BUTTONS
========================= */

.action-buttons {
    display: flex;

    align-items: center;

    gap: 7px;
}

.action-btn {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    width: 35px;
    height: 35px;

    border-radius: 9px;

    text-decoration: none;

    transition: .25s ease;
}

.action-btn:hover {
    transform: translateY(-2px);

    text-decoration: none;
}

.edit-btn {
    color: #35D6FF;

    background: rgba(53, 214, 255, .08);
}

.edit-btn:hover {
    color: #ffffff;

    background: #35D6FF;
}

.toggle-btn {
    color: #9B5DE5;

    background: rgba(155, 93, 229, .1);
}

.toggle-btn:hover {
    color: #ffffff;

    background: #9B5DE5;
}

.delete-btn {
    color: #ff7d91;

    background: rgba(255, 80, 110, .08);
}

.delete-btn:hover {
    color: #ffffff;

    background: #e05268;
}


/* =========================
   EMPTY
========================= */

.empty-state {
    padding: 55px 20px;

    text-align: center;
}

.empty-state i {
    margin-bottom: 15px;

    color: #35D6FF;

    font-size: 40px;
}

.empty-state h4 {
    margin: 0 0 8px;

    font-family: "Cinzel", serif;

    font-size: 21px;
}

.empty-state p {
    margin: 0;

    color: #788297;

    font-size: 13px;
}


/* =========================
   RESPONSIVE
========================= */

@media (max-width: 768px) {

    .category-section {
        padding: 45px 5% 70px;
    }

    .page-heading h1 {
        font-size: 30px;
    }

    .category-form {
        grid-template-columns: 1fr;
    }

    .submit-btn {
        width: 100%;
    }

    .cancel-btn {
        margin: 10px 0 0;
        width: 100%;
    }

    .category-card {
        padding: 22px;
    }

}

</style>

<body>


<section class="category-section">

    <div class="category-container">


        <!-- BACK -->

        <a href="dashboard.php" class="back-btn">

            <i class="fas fa-arrow-left"></i>

            Back to Dashboard

        </a>


        <!-- HEADING -->

        <div class="page-heading">

            <span>EVENT MANAGEMENT</span>

            <h1>Event Categories</h1>

            <p>
                Manage event categories and subcategories from one place.
            </p>

        </div>


        <!-- MESSAGE -->

        <?php if ($message != "") { ?>

            <div class="message <?php echo $message_type; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php } ?>


        <!-- ADD / UPDATE -->

        <div class="category-card">


            <div class="category-card-title">

                <i class="<?php echo $edit_id > 0
                    ? 'fas fa-edit'
                    : 'fas fa-plus-circle'; ?>">
                </i>

                <h3>

                    <?php
                    echo $edit_id > 0
                        ? "Update Category"
                        : "Add Category & Subcategory";
                    ?>

                </h3>

            </div>


            <form method="POST"
                  class="category-form">


                <?php if ($edit_id > 0) { ?>

                    <input type="hidden"
                           name="category_id"
                           value="<?php echo $edit_id; ?>">

                <?php } ?>


                <!-- CATEGORY -->

                <div class="form-group">

                    <label>
                        Category Name
                    </label>

                    <input
                        type="text"
                        name="category_name"
                        class="form-control"
                        placeholder="Example: Wedding"
                        value="<?php echo htmlspecialchars($edit_category); ?>"
                        required
                    >

                </div>


                <!-- SUBCATEGORY -->

                <div class="form-group">

                    <label>
                        Subcategory Name
                    </label>

                    <input
                        type="text"
                        name="subcategory_name"
                        class="form-control"
                        placeholder="Example: Royal"
                        value="<?php echo htmlspecialchars($edit_subcategory); ?>"
                        required
                    >

                </div>


                <!-- BUTTON -->

                <div>

                    <?php if ($edit_id > 0) { ?>

                        <button
                            type="submit"
                            name="update_category"
                            class="submit-btn"
                        >

                            <i class="fas fa-save"></i>

                            Update

                        </button>


                        <a
                            href="event-categories.php"
                            class="cancel-btn"
                        >

                            Cancel

                        </a>

                    <?php } else { ?>

                        <button
                            type="submit"
                            name="add_category"
                            class="submit-btn"
                        >

                            <i class="fas fa-plus"></i>

                            Add

                        </button>

                    <?php } ?>

                </div>


            </form>

        </div>


        <!-- LIST -->

        <div class="category-card">


            <div class="category-card-title">

                <i class="fas fa-layer-group"></i>

                <h3>
                    Category List
                </h3>

            </div>


            <div class="table-wrapper">

                <table class="category-table">


                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Category</th>

                            <th>Subcategory</th>

                            <th>Status</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    $count = 1;

                    if ($result && mysqli_num_rows($result) > 0) {

                        while ($row = mysqli_fetch_assoc($result)) {

                    ?>


                        <tr>


                            <td class="number-cell">

                                <?php echo $count++; ?>

                            </td>


                            <td class="category-name">

                                <?php
                                echo htmlspecialchars(
                                    $row['category_name']
                                );
                                ?>

                            </td>


                            <td class="subcategory-name">

                                <?php
                                echo htmlspecialchars(
                                    $row['subcategory_name']
                                );
                                ?>

                            </td>


                            <td>

                                <?php if ($row['status'] == 1) { ?>

                                    <span class="status-badge active">

                                        Active

                                    </span>

                                <?php } else { ?>

                                    <span class="status-badge inactive">

                                        Inactive

                                    </span>

                                <?php } ?>

                            </td>


                            <td>

                                <div class="action-buttons">


                                    <!-- EDIT -->

                                    <a
                                        href="event-categories.php?edit=<?php echo $row['id']; ?>"
                                        class="action-btn edit-btn"
                                        title="Edit"
                                    >

                                        <i class="fas fa-edit"></i>

                                    </a>


                                    <!-- TOGGLE -->

                                    <a
                                        href="event-categories.php?toggle=<?php echo $row['id']; ?>"
                                        class="action-btn toggle-btn"
                                        title="Active / Inactive"
                                    >

                                        <i class="fas fa-power-off"></i>

                                    </a>


                                    <!-- DELETE -->

                                    <a
                                        href="event-categories.php?delete=<?php echo $row['id']; ?>"
                                        class="action-btn delete-btn"
                                        title="Delete"
                                        onclick="return confirm('Are you sure you want to delete this category?');"
                                    >

                                        <i class="fas fa-trash"></i>

                                    </a>


                                </div>

                            </td>


                        </tr>


                    <?php

                        }

                    } else {

                    ?>


                        <tr>

                            <td colspan="5">

                                <div class="empty-state">

                                    <i class="fas fa-layer-group"></i>

                                    <h4>
                                        No Categories Found
                                    </h4>

                                    <p>
                                        Add your first event category and subcategory.
                                    </p>

                                </div>

                            </td>

                        </tr>


                    <?php } ?>


                    </tbody>

                </table>

            </div>


        </div>


    </div>

</section>


</body>

</html>