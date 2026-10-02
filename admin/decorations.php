<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

include("../config/db.php");

$message = "";

/* =========================
   DELETE DECORATION
========================= */
if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $delete = mysqli_query(
        $conn,
        "DELETE FROM decorations WHERE id = $id"
    );

    if ($delete) {
        $message = "Decoration deleted successfully.";
    } else {
        $message = "Unable to delete decoration.";
    }
}


/* =========================
   TOGGLE STATUS
========================= */
if (isset($_GET['toggle'])) {

    $id = (int)$_GET['toggle'];

    $toggle = mysqli_query(
        $conn,
        "UPDATE decorations
         SET status = IF(status = 1, 0, 1)
         WHERE id = $id"
    );

    if ($toggle) {
        $message = "Decoration status updated.";
    }
}


/* =========================
   FETCH DECORATIONS
========================= */
$decorations = mysqli_query(
    $conn,
    "SELECT *
     FROM decorations
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Decoration Management - Event Crafters</title>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Common CSS -->
    <link rel="stylesheet"
          href="../assets/css/common.css">

    <style>

        .decoration-page {
            padding: 50px 5%;
        }

        .decoration-header {
            margin-bottom: 30px;
        }

        .decoration-header h1 {
            font-family: "Cinzel", serif;
            font-size: 34px;
            color: #ffffff;
            margin-bottom: 8px;
        }

        .decoration-header p {
            color: #9ca6ba;
            margin: 0;
        }

        .decoration-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .back-btn {
            color: #35D6FF;
            text-decoration: none;
            font-weight: 600;
        }

        .back-btn:hover {
            color: #ffffff;
            text-decoration: none;
        }

        .add-decoration-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 20px;
            border-radius: 10px;
            background: linear-gradient(
                135deg,
                #9B5DE5,
                #35D6FF
            );
            color: #ffffff;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 8px 25px rgba(53,214,255,.15);
            transition: .3s ease;
        }

        .add-decoration-btn:hover {
            color: #ffffff;
            text-decoration: none;
            transform: translateY(-3px);
            box-shadow:
                0 12px 30px rgba(53,214,255,.25);
        }

        .decoration-table-box {
            background: rgba(17,24,43,.88);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 18px;
            padding: 20px;
            overflow-x: auto;
        }

        .decoration-table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        .decoration-table th {
            color: #35D6FF;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .7px;
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .decoration-table td {
            color: #d8deeb;
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,.06);
            vertical-align: middle;
        }

        .decoration-table tr:last-child td {
            border-bottom: none;
        }

        .decoration-image {
            width: 65px;
            height: 55px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,.1);
        }

        .no-image {
            width: 65px;
            height: 55px;
            border-radius: 10px;
            background: #11182b;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #68738b;
        }

        .category-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            background: rgba(155,93,229,.12);
            border: 1px solid rgba(155,93,229,.25);
            color: #c79cff;
            font-size: 12px;
        }

        .price-text {
            color: #35D6FF;
            font-weight: 600;
        }

        .status-active {
            color: #52e2a2;
            font-weight: 600;
        }

        .status-inactive {
            color: #ff8b8b;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 7px;
        }

        .action-btn {
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            text-decoration: none;
            transition: .3s ease;
        }

        .edit-btn {
            background: rgba(53,214,255,.1);
            color: #35D6FF;
        }

        .edit-btn:hover {
            background: #35D6FF;
            color: #07101c;
            transform: translateY(-2px);
        }

        .toggle-btn {
            background: rgba(155,93,229,.1);
            color: #b783ff;
        }

        .toggle-btn:hover {
            background: #9B5DE5;
            color: #ffffff;
            transform: translateY(-2px);
        }

        .delete-btn {
            background: rgba(255,80,100,.1);
            color: #ff7080;
        }

        .delete-btn:hover {
            background: #ff5064;
            color: #ffffff;
            transform: translateY(-2px);
        }

        .alert-message {
            background: rgba(53,214,255,.08);
            border: 1px solid rgba(53,214,255,.2);
            color: #35D6FF;
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }

        .empty-decoration {
            text-align: center;
            padding: 60px 20px;
            color: #8d97aa;
        }

        .empty-decoration i {
            font-size: 42px;
            color: #35D6FF;
            margin-bottom: 15px;
        }

        @media(max-width:768px) {

            .decoration-page {
                padding: 35px 4%;
            }

            .decoration-header h1 {
                font-size: 28px;
            }

        }

    </style>

</head>

<body>

<div class="decoration-page">

    <div class="decoration-top">

        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>

        <a href="add-decoration.php"
           class="add-decoration-btn">

            <i class="fas fa-plus"></i>
            Add Decoration

        </a>

    </div>


    <div class="decoration-header">

        <h1>Decoration Management</h1>

        <p>
            Manage event decoration packages and services.
        </p>

    </div>


    <?php if ($message != "") { ?>

        <div class="alert-message">

            <i class="fas fa-circle-check"></i>

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php } ?>


    <div class="decoration-table-box">

        <?php if ($decorations && mysqli_num_rows($decorations) > 0) { ?>

            <table class="decoration-table">

                <thead>

                    <tr>

                        <th>Image</th>

                        <th>Decoration</th>

                        <th>Category</th>

                        <th>Price</th>

                        <th>Status</th>

                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                <?php while ($row = mysqli_fetch_assoc($decorations)) { ?>

                    <tr>

                        <td>

                            <?php if (!empty($row['image'])) { ?>

                                <img
                                    src="../uploads/events/<?php echo htmlspecialchars($row['image']); ?>"
                                    class="decoration-image"
                                    alt="Decoration">

                            <?php } else { ?>

                                <div class="no-image">

                                    <i class="fas fa-image"></i>

                                </div>

                            <?php } ?>

                        </td>


                        <td>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $row['decoration_name']
                                );
                                ?>
                            </strong>

                            <?php if (!empty($row['description'])) { ?>

                                <br>

                                <small style="color:#7f8aa1;">

                                    <?php
                                    echo htmlspecialchars(
                                        $row['description']
                                    );
                                    ?>

                                </small>

                            <?php } ?>

                        </td>


                        <td>

                            <span class="category-badge">

                                <?php
                                echo htmlspecialchars(
                                    $row['category']
                                );
                                ?>

                            </span>

                        </td>


                        <td>

                            <span class="price-text">

                                ₹<?php
                                echo number_format(
                                    (float)$row['price'],
                                    2
                                );
                                ?>

                            </span>

                        </td>


                        <td>

                            <?php if ($row['status'] == 1) { ?>

                                <span class="status-active">
                                    <i class="fas fa-circle"></i>
                                    Active
                                </span>

                            <?php } else { ?>

                                <span class="status-inactive">
                                    <i class="fas fa-circle"></i>
                                    Inactive
                                </span>

                            <?php } ?>

                        </td>


                        <td>

                            <div class="action-buttons">

                                <a
                                    href="edit-decoration.php?id=<?php echo $row['id']; ?>"
                                    class="action-btn edit-btn"
                                    title="Edit">

                                    <i class="fas fa-pen"></i>

                                </a>


                                <a
                                    href="decorations.php?toggle=<?php echo $row['id']; ?>"
                                    class="action-btn toggle-btn"
                                    title="Change Status">

                                    <i class="fas fa-power-off"></i>

                                </a>


                                <a
                                    href="decorations.php?delete=<?php echo $row['id']; ?>"
                                    class="action-btn delete-btn"
                                    title="Delete"
                                    onclick="return confirm('Are you sure you want to delete this decoration?');">

                                    <i class="fas fa-trash"></i>

                                </a>

                            </div>

                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        <?php } else { ?>

            <div class="empty-decoration">

                <i class="fas fa-wand-magic-sparkles"></i>

                <h3>No Decorations Found</h3>

                <p>
                    Add your first decoration package.
                </p>

            </div>

        <?php } ?>

    </div>

</div>

</body>
</html>