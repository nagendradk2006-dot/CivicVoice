<?php

session_start();

include "db.php";


/* ================================================= */
/* CHECK ADMIN LOGIN */
/* ================================================= */

if (!isset($_SESSION["admin_id"])) {

    header("Location: admin_login.php");

    exit;

}


/* ================================================= */
/* GET ALL COMPLAINTS */
/* ================================================= */

$complaint_sql = "SELECT

                    complaints.complaint_id AS feed_id,

                    'Complaint' AS feed_type,

                    complaints.citizen_id,

                    complaints.constituency_name,

                    complaints.ward_number,

                    complaints.area_name,

                    complaints.pincode,

                    complaints.issue_description,

                    complaints.issue_image,

                    '' AS post_video,

                    complaints.status,

                    complaints.created_at,

                    departments.department_name

                  FROM complaints

                  INNER JOIN departments

                  ON complaints.department_id =
                     departments.department_id";


/* ================================================= */
/* GET ALL CIVIC POSTS */
/* ================================================= */

$post_sql = "SELECT

                CONCAT('P', civic_posts.post_id) AS feed_id,

                'Post' AS feed_type,

                civic_posts.citizen_id,

                civic_posts.constituency_name,

                civic_posts.ward_number,

                civic_posts.area_name,

                '' AS pincode,

                civic_posts.issue_description,

                civic_posts.post_image AS issue_image,

                civic_posts.post_video,

                'Posted' AS status,

                civic_posts.created_at,

                departments.department_name

             FROM civic_posts

             INNER JOIN departments

             ON civic_posts.department_id =
                departments.department_id";


/* ================================================= */
/* COMBINE COMPLAINTS AND POSTS */
/* ================================================= */

$sql = "($complaint_sql)

        UNION ALL

        ($post_sql)

        ORDER BY created_at DESC";


$result = mysqli_query($conn, $sql);


if (!$result) {

    die(
        "Database query failed: "
        . mysqli_error($conn)
    );

}

?>


<!DOCTYPE html>

<html>

<head>
<link rel="stylesheet" href="style.css">
    <title>CivicVoice Admin Dashboard</title>


    <style>


        /* ========================================= */
        /* IMAGE SLIDER */
        /* ========================================= */


        .carousel {

            width: 500px;

            max-width: 100%;

            overflow: hidden;

            position: relative;

            border: 1px solid #ccc;

            background: #f5f5f5;

            cursor: grab;

            touch-action: pan-y;

            user-select: none;

        }


        .carousel:active {

            cursor: grabbing;

        }


        .carousel-track {

            display: flex;

            transition:
                transform 0.4s ease;

            will-change: transform;

        }


        .carousel-slide {

            min-width: 100%;

            width: 100%;

        }


        .carousel-slide img {

            width: 100%;

            height: 350px;

            object-fit: contain;

            display: block;

            pointer-events: none;

        }


        /* ========================================= */
        /* IMAGE NUMBER */
        /* ========================================= */


        .image-count {

            width: 500px;

            max-width: 100%;

            text-align: center;

            margin-top: 8px;

            font-weight: bold;

        }


        /* ========================================= */
        /* VIDEO */
        /* ========================================= */


        .post-video {

            width: 500px;

            max-width: 100%;

        }


    </style>


</head>


<body>


<h2>

    🏛️ CivicVoice Admin Dashboard

</h2>


<p>

    Welcome,

    <strong>

        <?php

        echo $_SESSION["admin_name"];

        ?>

    </strong>

</p>


<hr>


<h3>

    📋 CivicVoice Posts & Complaints

</h3>


<?php


/* ================================================= */
/* CHECK DATA */
/* ================================================= */


if (mysqli_num_rows($result) > 0) {


    while ($row = mysqli_fetch_assoc($result)) {


?>


<hr>


<?php


/* ================================================= */
/* COMPLAINT OR POST */
/* ================================================= */


if ($row["feed_type"] == "Complaint") {


?>


<h3>

    🚨 Civic Complaint

</h3>


<p>

    🆔

    <strong>

        Complaint ID:

    </strong>

    <?php

    echo $row["feed_id"];

    ?>

</p>


<?php


} else {


?>


<h3>

    📢 Civic Post

</h3>


<p>

    🆔

    <strong>

        Post ID:

    </strong>

    <?php

    echo $row["feed_id"];

    ?>

</p>


<?php


}

?>


<p>

    👤

    <strong>

        Citizen ID:

    </strong>

    <?php

    echo $row["citizen_id"];

    ?>

</p>


<p>

    📍

    <strong>

        Constituency:

    </strong>

    <?php

    echo $row["constituency_name"];

    ?>

</p>


<p>

    🏘️

    <strong>

        Ward Number:

    </strong>

    <?php

    echo $row["ward_number"];

    ?>

</p>


<p>

    📌

    <strong>

        Area:

    </strong>

    <?php

    echo $row["area_name"];

    ?>

</p>


<?php


/* ================================================= */
/* PIN CODE */
/* ================================================= */


if ($row["feed_type"] == "Complaint") {


?>


<p>

    📮

    <strong>

        PIN Code:

    </strong>

    <?php

    echo $row["pincode"];

    ?>

</p>


<?php


}

?>


<p>

    🏛️

    <strong>

        Department:

    </strong>

    <?php

    echo $row["department_name"];

    ?>

</p>


<p>

    📝

    <strong>

        Issue:

    </strong>

    <?php

    echo $row["issue_description"];

    ?>

</p>


<?php


/* ================================================= */
/* COMPLAINT IMAGES */
/* ================================================= */


if ($row["feed_type"] == "Complaint") {


?>


<p>

    📷

    <strong>

        Evidence Images:

    </strong>

</p>


<?php


$image_sql = "SELECT image_path

              FROM complaint_images

              WHERE complaint_id='"
              . $row["feed_id"] . "'

              ORDER BY image_id ASC";


$image_result = mysqli_query(
    $conn,
    $image_sql
);


$complaint_images = [];


if (

    $image_result &&

    mysqli_num_rows($image_result) > 0

) {


    while (

        $image_row =
        mysqli_fetch_assoc($image_result)

    ) {


        $complaint_images[] =
            $image_row["image_path"];


    }


}


/* Use first image from complaints table if needed */


if (

    count($complaint_images) == 0 &&

    !empty($row["issue_image"])

) {


    $complaint_images[] =
        $row["issue_image"];


}


/* Display complaint images */


if (count($complaint_images) > 0) {


    $carousel_id =
        "complaint_" .
        $row["feed_id"];


?>


<div
    class="carousel"
    id="<?php echo $carousel_id; ?>"
>


    <div
        class="carousel-track"
        id="<?php echo $carousel_id; ?>_track"
    >


<?php


    foreach (
        $complaint_images
        as $image
    ) {


?>


        <div class="carousel-slide">


            <img
                src="<?php echo $image; ?>"
                draggable="false"
            >


        </div>


<?php


    }


?>


    </div>


</div>


<?php


if (count($complaint_images) > 1) {


?>


<div
    class="image-count"
    id="<?php echo $carousel_id; ?>_count"
>

    1 / <?php echo count($complaint_images); ?>

</div>


<script>


createSlider(

    "<?php echo $carousel_id; ?>",

    <?php echo count($complaint_images); ?>

);


</script>


<?php


}


} else {


?>


<p>

    No evidence image available.

</p>


<?php


}


}


/* ================================================= */
/* CIVIC POST IMAGES */
/* ================================================= */


if ($row["feed_type"] == "Post") {


    $actual_post_id =
        substr(
            $row["feed_id"],
            1
        );


    $image_sql = "SELECT image_path

                  FROM civic_post_images

                  WHERE post_id='$actual_post_id'

                  ORDER BY image_id ASC";


    $image_result =
        mysqli_query(
            $conn,
            $image_sql
        );


    $post_images = [];


    if (

        $image_result &&

        mysqli_num_rows($image_result) > 0

    ) {


        while (

            $image_row =
            mysqli_fetch_assoc($image_result)

        ) {


            $post_images[] =
                $image_row["image_path"];


        }


    }


    /* Fallback to first image */


    if (

        count($post_images) == 0 &&

        !empty($row["issue_image"])

    ) {


        $post_images[] =
            $row["issue_image"];


    }


    /* ================================================= */
    /* DISPLAY POST IMAGE SLIDER */
    /* ================================================= */


    if (count($post_images) > 0) {


        $carousel_id =
            "post_" .
            $actual_post_id;


?>


<p>

    📷

    <strong>

        Post Images:

    </strong>

</p>


<div
    class="carousel"
    id="<?php echo $carousel_id; ?>"
>


    <div
        class="carousel-track"
        id="<?php echo $carousel_id; ?>_track"
    >


<?php


        foreach (
            $post_images
            as $image
        ) {


?>


        <div class="carousel-slide">


            <img
                src="<?php echo $image; ?>"
                draggable="false"
            >


        </div>


<?php


        }


?>


    </div>


</div>


<?php


/* Display image counter only for multiple images */


if (count($post_images) > 1) {


?>


<div
    class="image-count"
    id="<?php echo $carousel_id; ?>_count"
>

    1 / <?php echo count($post_images); ?>

</div>


<script>


createSlider(

    "<?php echo $carousel_id; ?>",

    <?php echo count($post_images); ?>

);


</script>


<?php


}


}


/* ================================================= */
/* VIDEO */
/* ================================================= */


if (!empty($row["post_video"])) {


?>


<p>

    🎥

    <strong>

        Video / Reel:

    </strong>

</p>


<video
    class="post-video"
    controls
>


    <source
        src="<?php echo $row["post_video"]; ?>"
        type="video/mp4"
    >


    Your browser does not support
    video playback.


</video>


<?php


}


}


?>


<p>

    📌

    <strong>

        Status:

    </strong>

    <?php

    echo $row["status"];

    ?>

</p>


<p>

    📅

    <strong>

        Posted On:

    </strong>

    <?php

    echo $row["created_at"];

    ?>

</p>


<?php


/* ================================================= */
/* UPDATE COMPLAINT */
/* ================================================= */


if ($row["feed_type"] == "Complaint") {


?>


<br>


<a
    href="admin_update_complaint.php?complaint_id=<?php echo $row["feed_id"]; ?>"
>


    <button>

        📝 Update Complaint

    </button>


</a>


<?php


}


    }


} else {


?>


<p>

    No civic posts or complaints found.

</p>


<?php


}


?>


<hr>


<br>


<button
    onclick="window.location.href='admin_logout.php'"
>

    Logout

</button>


<script>


/* ================================================= */
/* SLIDER SYSTEM */
/* ================================================= */


var sliders = {};


/* ================================================= */
/* CREATE SLIDER */
/* ================================================= */


function createSlider(
    id,
    total
) {


    var carousel =
        document.getElementById(id);


    var track =
        document.getElementById(
            id + "_track"
        );


    if (!carousel || !track) {

        return;

    }


    sliders[id] = {

        current: 0,

        total: total,

        startX: 0,

        endX: 0,

        dragging: false

    };


    /* ============================================= */
    /* START DRAG */
    /* ============================================= */


    carousel.addEventListener(
        "pointerdown",
        function(event) {


            sliders[id].startX =
                event.clientX;


            sliders[id].dragging =
                true;


            track.style.transition =
                "none";


            carousel.setPointerCapture(
                event.pointerId
            );


        }
    );


    /* ============================================= */
    /* DRAG IMAGE */
    /* ============================================= */


    carousel.addEventListener(
        "pointermove",
        function(event) {


            if (
                !sliders[id].dragging
            ) {

                return;

            }


            var currentX =
                event.clientX;


            var difference =
                currentX -
                sliders[id].startX;


            var percentage =
                (
                    difference /
                    carousel.offsetWidth
                ) *
                100;


            var position =
                (
                    sliders[id].current *
                    -100
                ) + percentage;


            track.style.transform =
                "translateX(" +
                position +
                "%)";


        }
    );


    /* ============================================= */
    /* END DRAG */
    /* ============================================= */


    carousel.addEventListener(
        "pointerup",
        function(event) {


            if (
                !sliders[id].dragging
            ) {

                return;

            }


            sliders[id].dragging =
                false;


            sliders[id].endX =
                event.clientX;


            var difference =
                sliders[id].endX -
                sliders[id].startX;


            track.style.transition =
                "transform 0.4s ease";


            /* Swipe left */


            if (difference < -50) {


                nextSlide(id);


            }


            /* Swipe right */


            else if (difference > 50) {


                previousSlide(id);


            }


            /* Small movement */


            else {


                showSlide(id);


            }


        }
    );


    /* ============================================= */
    /* CANCEL DRAG */
    /* ============================================= */


    carousel.addEventListener(
        "pointercancel",
        function() {


            sliders[id].dragging =
                false;


            track.style.transition =
                "transform 0.4s ease";


            showSlide(id);


        }
    );


}


/* ================================================= */
/* SHOW SLIDE */
/* ================================================= */


function showSlide(id) {


    var slider =
        sliders[id];


    var track =
        document.getElementById(
            id + "_track"
        );


    var count =
        document.getElementById(
            id + "_count"
        );


    if (!slider || !track) {

        return;

    }


    track.style.transform =
        "translateX(-" +
        (
            slider.current * 100
        ) +
        "%)";


    if (count) {


        count.innerHTML =
            (
                slider.current + 1
            ) +
            " / " +
            slider.total;


    }


}


/* ================================================= */
/* NEXT SLIDE */
/* ================================================= */


function nextSlide(id) {


    var slider =
        sliders[id];


    if (!slider) {

        return;

    }


    slider.current++;


    if (
        slider.current >=
        slider.total
    ) {


        slider.current = 0;


    }


    showSlide(id);


}


/* ================================================= */
/* PREVIOUS SLIDE */
/* ================================================= */


function previousSlide(id) {


    var slider =
        sliders[id];


    if (!slider) {

        return;

    }


    slider.current--;


    if (
        slider.current < 0
    ) {


        slider.current =
            slider.total - 1;


    }


    showSlide(id);


}


</script>


</body>

</html>