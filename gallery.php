<?php

include "config/db.php";
include "includes/header.php";
include "includes/navbar.php";

$result = mysqli_query(
    $conn,
    "SELECT * FROM gallery ORDER BY id DESC"
);

?>

<link rel="stylesheet" href="assets/css/gallery.css">


<!-- =========================
     BACK BUTTON
========================= -->

<a href="javascript:history.back()" class="back-btn">

    <i class="fas fa-arrow-left"></i>

    Back

</a>


<!-- =========================
     GALLERY HEADER
========================= -->

<section class="gallery-header">

    <div class="section-heading">

        <h1>OUR GALLERY</h1>

        <p>
            Explore beautiful moments created by Event Crafters.
        </p>

    </div>

</section>


<!-- =========================
     GALLERY
========================= -->

<section class="gallery-section">

    <div class="gallery-container">

        <?php

        if($result && mysqli_num_rows($result) > 0)
        {

            while($row = mysqli_fetch_assoc($result))
            {

        ?>

        <div class="gallery-item">


            <div class="gallery-image">

                <img
                    src="uploads/gallery/<?php
                    echo htmlspecialchars($row['image']);
                    ?>"
                    alt="<?php
                    echo htmlspecialchars($row['title']);
                    ?>"
                >

            </div>


            <div class="gallery-overlay">

                <h3>

                    <?php
                    echo htmlspecialchars($row['title']);
                    ?>

                </h3>

            </div>


        </div>

        <?php

            }

        }
        else
        {

        ?>

        <div class="no-gallery">

            <i class="fas fa-images"></i>

            <h3>
                No Gallery Images Available
            </h3>

            <p>
                Gallery images will appear here.
            </p>

        </div>

        <?php

        }

        ?>

    </div>

</section>


<?php

include "includes/footer.php";

?>