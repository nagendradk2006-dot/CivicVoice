<?php

session_start();

if (!isset($_SESSION["citizen_id"])) {
    header("Location: login.php");
    exit;
}

$citizen_id = $_SESSION["citizen_id"];

include "db.php";


/* ================================================= */
/* SUPPORT BUTTON PROCESSING */
/* ================================================= */

if (isset($_GET["support"])) {

    $feed_id = $_GET["support"];

    $check = "SELECT * FROM complaint_support
              WHERE complaint_id='$feed_id'
              AND citizen_id='$citizen_id'";

    $check_result = mysqli_query($conn, $check);

    if (mysqli_num_rows($check_result) == 0) {

        $support_sql = "INSERT INTO complaint_support
                        (complaint_id, citizen_id)
                        VALUES ('$feed_id', '$citizen_id')";

        mysqli_query($conn, $support_sql);
    }

    header("Location: home.php");
    exit;
}


/* ================================================= */
/* GET COMPLAINTS */
/* ================================================= */

$complaint_sql = "SELECT
                    complaint_id AS feed_id,
                    'Complaint' AS feed_type,
                    citizen_id,
                    area_name,
                    department_id,
                    issue_description,
                    issue_image AS post_image,
                    NULL AS post_video,
                    status,
                    created_at
                  FROM complaints";


/* ================================================= */
/* GET CIVIC POSTS */
/* ================================================= */

$post_sql = "SELECT
                CONCAT('P', post_id) AS feed_id,
                'Post' AS feed_type,
                citizen_id,
                area_name,
                department_id,
                issue_description,
                post_image,
                post_video,
                'Posted' AS status,
                created_at
             FROM civic_posts";


/* ================================================= */
/* COMBINE COMPLAINTS AND POSTS */
/* ================================================= */

$sql = "($complaint_sql)
        UNION ALL
        ($post_sql)
        ORDER BY created_at DESC";


$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html>

<head>

    <link rel="stylesheet" href="style.css">

    <title>CivicVoice Home</title>

    <style>

        /* ================================================= */
        /* HOME PAGE */
        /* ================================================= */

        body {
            background-color: #f4f6f8;
        }


        /* ================================================= */
        /* CIVICVOICE HEADER */
        /* ================================================= */

        .civic-header {
            background-color: white;
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #ddd;
            position: sticky;
            top: 0;
            z-index: 1000;
        }


        .civic-logo {
            font-size: 24px;
            font-weight: bold;
            color: #1f3c88;
        }


        .civic-nav {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }


        .civic-nav a {
            padding: 8px 12px;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
        }


        .civic-nav a:hover {
            background-color: #f0f2f5;
            text-decoration: none;
        }


        /* ================================================= */
        /* WELCOME SECTION */
        /* ================================================= */

        .welcome-section {
            max-width: 700px;
            margin: 30px auto 15px;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            border: 1px solid #ddd;
        }


        .welcome-section h2 {
            margin-top: 0;
        }


        .welcome-section p {
            color: #666;
        }


        /* ================================================= */
        /* FEED TITLE */
        /* ================================================= */

        .feed-title {
            max-width: 700px;
            margin: 20px auto;
            color: #1f3c88;
        }


        /* ================================================= */
        /* FEED CARD */
        /* ================================================= */

        .feed-card {
            max-width: 700px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 10px;
        }


        .feed-card h3 {
            margin-top: 0;
        }


        .feed-info {
            margin: 8px 0;
        }


        .feed-actions {
            margin-top: 15px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }


        .feed-actions button {
            background-color: #f0f2f5;
        }


        .feed-actions button:hover {
            background-color: #e2e5e9;
        }


        /* ================================================= */
        /* IMAGE CAROUSEL */
        /* ================================================= */

        .carousel {

            width: 300px;

            max-width: 100%;

            overflow: hidden;

            position: relative;

            touch-action: pan-y;

            cursor: grab;

            user-select: none;

            background: #f5f5f5;

            margin: 15px auto;

        }


        .carousel.dragging {

            cursor: grabbing;

        }


        .slider {

            display: flex;

            width: 100%;

            transition: transform 0.35s ease;

            will-change: transform;

        }


        .slide {

            flex: 0 0 100%;

            width: 100%;

        }


        .slide img {

            width: 300px;

            height: 300px;

            object-fit: cover;

            display: block;

            pointer-events: none;

            user-select: none;

            -webkit-user-drag: none;

        }


        /* ================================================= */
        /* IMAGE COUNTER */
        /* ================================================= */

        .image-counter {

            width: 300px;

            max-width: 100%;

            text-align: center;

            margin: 8px auto;

            font-weight: bold;

        }


        /* ================================================= */
        /* VIDEO */
        /* ================================================= */

        .civic-video {

            width: 300px;

            max-width: 100%;

            display: block;

            margin: 15px auto;

        }


        /* ================================================= */
        /* BOTTOM NAVIGATION */
        /* ================================================= */

        .bottom-navigation {
            max-width: 700px;
            margin: 30px auto;
            padding: 20px;
            text-align: center;
            background-color: white;
            border-top: 1px solid #ddd;
        }


        /* ================================================= */
        /* MOBILE */
        /* ================================================= */

        @media (max-width: 700px) {

            .civic-header {
                padding: 15px;
                flex-direction: column;
                gap: 12px;
            }

            .civic-nav {
                justify-content: center;
            }

            .welcome-section,
            .feed-title,
            .feed-card,
            .bottom-navigation {
                margin-left: 10px;
                margin-right: 10px;
            }

        }

    </style>

</head>


<body>


<!-- ================================================= -->
<!-- CIVICVOICE HEADER -->
<!-- ================================================= -->

<header class="civic-header">

    <div class="civic-logo">
        🏛️ CivicVoice
    </div>


    <nav class="civic-nav">

        <a href="home.php">
            🏠 Home
        </a>


        <a href="create_post.php">
            ➕ Create Post
        </a>


        <a href="profile.php">
            👤 My Profile
        </a>


        <a href="dashboard.php">
            Dashboard
        </a>


        <a href="logout.php">
            Logout
        </a>

    </nav>

</header>


<!-- ================================================= -->
<!-- WELCOME SECTION -->
<!-- ================================================= -->

<div class="welcome-section">

    <h2>
        Welcome, Citizen <?php echo $citizen_id; ?>
    </h2>


    <p>
        Stay informed. Support civic issues. Make your community better.
    </p>

</div>


<!-- ================================================= -->
<!-- HOME FEED TITLE -->
<!-- ================================================= -->

<h2 class="feed-title">

    🏠 Home Feed

</h2>


<?php


/* ================================================= */
/* DISPLAY FEED */
/* ================================================= */

if (mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {

        $feed_id = $row["feed_id"];


        /* ================================================= */
        /* GET DEPARTMENT NAME */
        /* ================================================= */

        $department_sql =
            "SELECT department_name
             FROM departments
             WHERE department_id='"
             . $row["department_id"] . "'";


        $department_result =
            mysqli_query(
                $conn,
                $department_sql
            );


        $department_data =
            mysqli_fetch_assoc(
                $department_result
            );


        $department_name =
            $department_data["department_name"];


        /* ================================================= */
        /* SUPPORT COUNT */
        /* ================================================= */

        $support_count_sql =
            "SELECT COUNT(*) AS total_support
             FROM complaint_support
             WHERE complaint_id='$feed_id'";


        $support_count_result =
            mysqli_query(
                $conn,
                $support_count_sql
            );


        $support_data =
            mysqli_fetch_assoc(
                $support_count_result
            );


        $support_count =
            $support_data["total_support"];


        /* ================================================= */
        /* COMMENT COUNT */
        /* ================================================= */

        $comment_count_sql =
            "SELECT COUNT(*) AS total_comments
             FROM complaint_comments
             WHERE complaint_id='$feed_id'";


        $comment_count_result =
            mysqli_query(
                $conn,
                $comment_count_sql
            );


        $comment_data =
            mysqli_fetch_assoc(
                $comment_count_result
            );


        $comment_count =
            $comment_data["total_comments"];

?>


<!-- ================================================= -->
<!-- FEED CARD -->
<!-- ================================================= -->

<div class="feed-card">


<?php


/* ================================================= */
/* COMPLAINT OR POST */
/* ================================================= */

if ($row["feed_type"] == "Complaint") {


?>

<h3>

    🚨 Civic Complaint

</h3>


<p class="feed-info">

    🆔

    <strong>
        Complaint ID:
    </strong>

    <?php echo $feed_id; ?>

</p>


<?php


} else {


?>

<h3>

    📢 Civic Post

</h3>


<p class="feed-info">

    🆔

    <strong>
        Post ID:
    </strong>

    <?php echo $feed_id; ?>

</p>


<?php

}

?>


<p class="feed-info">

    📍

    <strong>
        Area:
    </strong>

    <?php echo $row["area_name"]; ?>

</p>


<p class="feed-info">

    🏛️

    <strong>
        Department:
    </strong>

    <?php echo $department_name; ?>

</p>


<p class="feed-info">

    📝

    <strong>
        Issue:
    </strong>

    <?php echo $row["issue_description"]; ?>

</p>


<!-- ================================================= -->
<!-- POSTED DATE -->
<!-- ================================================= -->

<p class="feed-info">

    🕒

    <strong>
        Posted On:
    </strong>

    <?php

    echo date(
        "d F Y, h:i A",
        strtotime($row["created_at"])
    );

    ?>

</p>


<?php


/* ================================================= */
/* GET IMAGE PATHS */
/* ================================================= */

$image_paths = [];


if ($row["feed_type"] == "Post") {

    $actual_post_id =
        substr(
            $feed_id,
            1
        );


    $image_sql =
        "SELECT image_path
         FROM civic_post_images
         WHERE post_id='$actual_post_id'
         ORDER BY image_id ASC";


} else {


    $image_sql =
        "SELECT image_path
         FROM complaint_images
         WHERE complaint_id='$feed_id'
         ORDER BY image_id ASC";

}


$image_result =
    mysqli_query(
        $conn,
        $image_sql
    );


if ($image_result) {

    while (
        $image_row =
        mysqli_fetch_assoc(
            $image_result
        )
    ) {

        $image_paths[] =
            $image_row["image_path"];

    }

}


/* ================================================= */
/* FALLBACK IMAGE */
/* ================================================= */

if (
    count($image_paths) == 0 &&
    !empty($row["post_image"])
) {

    $image_paths[] =
        $row["post_image"];

}


/* ================================================= */
/* DISPLAY IMAGE CAROUSEL */
/* ================================================= */

if (count($image_paths) > 0) {


    $carousel_id =
        "carousel_" .
        $feed_id;

?>


<div
    class="carousel"
    id="<?php echo $carousel_id; ?>"
>


    <div
        class="slider"
        id="<?php echo $carousel_id; ?>_slider"
    >


<?php


    foreach (
        $image_paths
        as $image_path
    ) {


?>


        <div class="slide">


            <img
                src="<?php echo $image_path; ?>"
                draggable="false"
            >


        </div>


<?php

    }

?>


    </div>


</div>


<?php


/* ================================================= */
/* IMAGE COUNTER */
/* ================================================= */

if (count($image_paths) > 1) {


?>


<div
    class="image-counter"
    id="<?php echo $carousel_id; ?>_counter"
>

    1 / <?php echo count($image_paths); ?>

</div>


<?php

}


/* ================================================= */
/* CREATE SLIDER */
/* ================================================= */

?>


<script>

(function () {

    var carousel =
        document.getElementById(
            "<?php echo $carousel_id; ?>"
        );


    var slider =
        document.getElementById(
            "<?php echo $carousel_id; ?>_slider"
        );


    var counter =
        document.getElementById(
            "<?php echo $carousel_id; ?>_counter"
        );


    var totalImages =
        <?php echo count($image_paths); ?>;


    var currentImage = 0;


    var startX = 0;


    var currentX = 0;


    var startPosition = 0;


    var isDragging = false;


    var moved = false;


    function updateCounter() {

        if (counter) {

            counter.innerHTML =
                (currentImage + 1) +
                " / " +
                totalImages;

        }

    }


    function setSlide(
        index,
        animate
    ) {

        currentImage = index;


        if (animate) {

            slider.style.transition =
                "transform 0.35s ease";

        } else {

            slider.style.transition =
                "none";

        }


        slider.style.transform =
            "translateX(-" +
            (currentImage * 100) +
            "%)";


        updateCounter();

    }


    carousel.addEventListener(
        "pointerdown",
        function (event) {

            if (totalImages <= 1) {

                return;

            }


            startX =
                event.clientX;


            currentX =
                event.clientX;


            startPosition =
                currentImage * -100;


            isDragging =
                true;


            moved =
                false;


            carousel.classList.add(
                "dragging"
            );


            slider.style.transition =
                "none";


            carousel.setPointerCapture(
                event.pointerId
            );

        }
    );


    carousel.addEventListener(
        "pointermove",
        function (event) {

            if (!isDragging) {

                return;

            }


            currentX =
                event.clientX;


            var difference =
                currentX - startX;


            if (Math.abs(difference) > 5) {

                moved = true;

            }


            var width =
                carousel.offsetWidth;


            var movement =
                (
                    difference /
                    width
                ) * 100;


            var position =
                startPosition +
                movement;


            if (
                currentImage == 0 &&
                difference > 0
            ) {

                movement =
                    movement * 0.35;


                position =
                    startPosition +
                    movement;

            }


            if (
                currentImage ==
                totalImages - 1 &&
                difference < 0
            ) {

                movement =
                    movement * 0.35;


                position =
                    startPosition +
                    movement;

            }


            slider.style.transform =
                "translateX(" +
                position +
                "%)";

        }
    );


    carousel.addEventListener(
        "pointerup",
        function (event) {

            if (!isDragging) {

                return;

            }


            isDragging =
                false;


            carousel.classList.remove(
                "dragging"
            );


            var difference =
                currentX - startX;


            var width =
                carousel.offsetWidth;


            var swipeDistance =
                Math.abs(difference);


            var threshold =
                width * 0.20;


            if (
                swipeDistance >= threshold
            ) {


                if (
                    difference < 0 &&
                    currentImage <
                    totalImages - 1
                ) {

                    currentImage++;

                }


                else if (
                    difference > 0 &&
                    currentImage > 0
                ) {

                    currentImage--;

                }

            }


            slider.style.transition =
                "transform 0.35s ease";


            slider.style.transform =
                "translateX(-" +
                (currentImage * 100) +
                "%)";


            updateCounter();


            try {

                carousel.releasePointerCapture(
                    event.pointerId
                );

            } catch (error) {

            }

        }
    );


    carousel.addEventListener(
        "pointercancel",
        function (event) {

            if (!isDragging) {

                return;

            }


            isDragging =
                false;


            carousel.classList.remove(
                "dragging"
            );


            slider.style.transition =
                "transform 0.35s ease";


            slider.style.transform =
                "translateX(-" +
                (currentImage * 100) +
                "%)";

        }
    );


})();

</script>


<?php

}

}


/* ================================================= */
/* VIDEO */
/* ================================================= */

if (!empty($row["post_video"])) {


?>


<video
    class="civic-video"
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

?>


<!-- ================================================= -->
<!-- STATUS -->
<!-- ================================================= -->

<p class="feed-info">

    📌

    <strong>
        Status:
    </strong>

    <?php echo $row["status"]; ?>

</p>


<!-- ================================================= -->
<!-- FEED ACTIONS -->
<!-- ================================================= -->

<div class="feed-actions">


    <!-- SUPPORT -->

    <a
        href="home.php?support=<?php echo $feed_id; ?>"
    >

        <button>

            👍 Support <?php echo $support_count; ?>

        </button>

    </a>


    <!-- COMMENT -->

    <a
        href="comments.php?complaint_id=<?php echo $feed_id; ?>"
    >

        <button>

            💬 Comment <?php echo $comment_count; ?>

        </button>

    </a>


    <!-- SHARE -->

    <button
        onclick="shareComplaint(
            '<?php echo $feed_id; ?>'
        )"
    >

        🔗 Share

    </button>


</div>


</div>


<?php


    }

 else {


?>


<div class="feed-card">

    <p>
        No civic posts available.
    </p>

</div>


<?php

}

?>


<!-- ================================================= -->
<!-- BOTTOM NAVIGATION -->
<!-- ================================================= -->

<div class="bottom-navigation">


    <button
        onclick="window.location.href='dashboard.php'"
    >

        Dashboard

    </button>


    <button
        onclick="window.location.href='logout.php'"
    >

        Logout

    </button>


</div>


<script>


/* ================================================= */
/* SHARE FUNCTION */
/* ================================================= */

function shareComplaint(feedId) {


    var shareUrl =
        window.location.origin +
        "/CivicVoice/comments.php?complaint_id=" +
        feedId;


    if (navigator.share) {


        navigator.share({

            title:
                "CivicVoice Civic Issue",

            text:
                "Check this civic issue on CivicVoice",

            url:
                shareUrl

        });


    } else {


        navigator.clipboard.writeText(
            shareUrl
        );


        alert(
            "Post link copied!"
        );

    }

}

</script>


</body>

</html>