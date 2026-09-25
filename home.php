<?php

session_start();

if (!isset($_SESSION["citizen_id"])) {
    header("Location: login.php");
    exit;
}

$citizen_id = $_SESSION["citizen_id"];

include "db.php";


/* ================================================= */
/* SUPPORT BUTTON */
/* ================================================= */

if (isset($_GET["support"])) {

    $feed_id = mysqli_real_escape_string(
        $conn,
        $_GET["support"]
    );


    /* CHECK WHETHER THIS CITIZEN ALREADY SUPPORTED */

    $check = "SELECT *
              FROM complaint_support
              WHERE complaint_id='$feed_id'
              AND citizen_id='$citizen_id'";

    $check_result = mysqli_query(
        $conn,
        $check
    );


    if (!$check_result) {
        die("Support Check Error: " . mysqli_error($conn));
    }


    /* INSERT SUPPORT ONLY ONCE */

    if (mysqli_num_rows($check_result) == 0) {

        $support_sql = "INSERT INTO complaint_support
                        (complaint_id, citizen_id)
                        VALUES ('$feed_id', '$citizen_id')";

        $support_result = mysqli_query(
            $conn,
            $support_sql
        );


        if (!$support_result) {
            die("Support Error: " . mysqli_error($conn));
        }
    }


    /*
     * Return to home page.
     * JavaScript below will restore the previous
     * scroll position.
     */

    header("Location: home.php");
    exit;
}

/* ================================================= */
/* GET COMPLAINTS */
/* ================================================= */

$complaint_sql = "
    SELECT
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
    FROM complaints
";

/* ================================================= */
/* GET CIVIC POSTS */
/* ================================================= */

$post_sql = "
    SELECT
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
    FROM civic_posts
";

/* ================================================= */
/* COMBINE FEED */
/* ================================================= */

$sql = "
    ($complaint_sql)
    UNION ALL
    ($post_sql)
    ORDER BY created_at DESC
";


$result = mysqli_query(
    $conn,
    $sql
);


if (!$result) {
    die("Feed Error: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>

<html>

<head>

    <title>CivicVoice Home</title>

    <link rel="stylesheet" href="style.css">

    <style>

        /* ================================================= */
        /* HOME PAGE */
        /* ================================================= */

        body {
    background-color: #E8E2D5;
    margin: 0;
}


        /* ================================================= */
        /* HEADER */
        /* ================================================= */

        .civic-header {
            background-color: white;

            padding: 15px 30px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            border-bottom: 2px solid #C9A227;

            position: sticky;

            top: 0;

            z-index: 1000;
        }
.civic-logo {
    width: 300px !important;
    height: auto !important;
    overflow: visible !important;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    margin: 0 !important;
}

.civic-logo img {
    width: 300px !important;
    height: auto !important;
    max-width: none !important;
    display: block !important;
    object-fit: contain !important;
    overflow: visible !important;
    border: none !important;
    box-shadow: none !important;
}

.civic-nav a {
    background-color: #C9A227;
    color: #1F2937;
    text-decoration: none;
    font-weight: 600;
    padding: 9px 14px;
    border-radius: 8px;
    transition: 0.2s ease;
}

.civic-nav a:hover {
    background-color: #C9A227;
    color: #000000;
}

        /* ================================================= */
        /* WELCOME */
        /* ================================================= */
.welcome-section {
    max-width: 700px;
    margin: 30px auto 20px;
    padding: 28px 30px;
    background-color: #ffffff;
    border-radius: 18px;
    border: 2px solid #166534;
    border-top: 6px solid #166534;
    box-shadow: 0 6px 18px rgba(22, 101, 52, 0.12);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.welcome-section h2 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #166534;
    font-size: 26px;
    font-weight: 700;
    letter-spacing: 0.2px;
    font-family: Georgia, "Times New Roman", serif;
}
.welcome-section p {
    margin: 0;
    color: #666666;
    font-size: 16px;
    line-height: 1.6;
    font-family: "Trebuchet MS", Arial, sans-serif;
}


        /* ================================================= */
        /* FEED TITLE */
        /* ================================================= */
.feed-title {
    max-width: 700px;
    margin: 28px auto 16px;
    color: #166534;
    font-size: 23px;
    font-weight: 700;
    padding-left: 5px;
    letter-spacing: 0.2px;
}

        /* ================================================= */
        /* FEED CARD */
        /* ================================================= */

        .feed-card {
            width: calc(100% - 40px);

            max-width: 700px;

            margin: 20px auto;

            padding: 20px;

            background-color: white;

            border: 1px solid #ddd;

            border-radius: 12px;

            box-sizing: border-box;

            scroll-margin-top: 100px;
        }

.feed-card {
    background: #ffffff;
    border: 1px solid #ddd6c8;
    border-radius: 16px;
    padding: 18px;
    margin-bottom: 22px;
    box-shadow: 0 5px 16px rgba(31, 41, 55, 0.08);
    overflow: hidden;
    transition: 0.2s ease;
}

.feed-card:hover {
    box-shadow: 0 8px 22px rgba(31, 41, 55, 0.12);
    transform: translateY(-2px);
}
.feed-card h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
    font-weight: 700;
    color: #166534;
    line-height: 1.4;
}
.feed-info {
    margin: 10px 0;
    padding: 0 5px;
    line-height: 1.65;
    color: #374151;
    font-size: 15px;
}


        /* ================================================= */
        /* FEED ACTIONS */
        /* ================================================= */

        .feed-actions {
            margin-top: 18px;

            padding-top: 15px;

            border-top: 1px solid #eee;

            display: flex;

            gap: 10px;

            flex-wrap: wrap;
        }
<style>

        .feed-actions a {
            text-decoration: none;
        }

.feed-actions button {
    background-color: #166534 !important;
    color: #ffffff !important;
    border: 1px solid #166534 !important;
    padding: 10px 16px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: 0.2s ease;
}
        .feed-actions button:hover {
    background-color: #14532D !important;
    color: #ffffff !important;
    border-color: #14532D !important;
}

        /* ================================================= */
        /* IMAGE CAROUSEL */
        /* ================================================= */

        .carousel {
            width: 600px;

            max-width: 100%;

            overflow: hidden;

            position: relative;

            touch-action: pan-y;

            cursor: grab;

            user-select: none;

            background-color: #f5f5f5;

            margin: 18px auto 5px;

            border-radius: 14px;
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
    min-width: 100%;
    box-sizing: border-box;
}


.slide img {
    display: block;
    width: 100%;
    height: 450px;
    object-fit: cover;
    border-radius: 12px;
    pointer-events: none;
    user-select: none;
    -webkit-user-drag: none;
}
        /* ================================================= */
        /* IMAGE COUNTER */
        /* ================================================= */

        .image-counter {
            width: 600px;

            max-width: 100%;

            text-align: center;

            margin: 8px auto;

            font-weight: bold;

            color: #555;
        }


        /* ================================================= */
        /* VIDEO */
        /* ================================================= */

        .civic-video {
            width: 600px;

            max-width: 100%;

            max-height: 600px;

            display: block;

            margin: 18px auto;

            border-radius: 12px;

            background-color: black;
        }


        /* ================================================= */
        /* BOTTOM NAVIGATION */
        /* ================================================= */

        .bottom-navigation {
            width: calc(100% - 40px);

            max-width: 700px;

            margin: 30px auto;

            padding: 20px;

            text-align: center;

            background-color: white;

            border-top: 1px solid #ddd;

            box-sizing: border-box;
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
            .feed-title {
                margin-left: 10px;

                margin-right: 10px;
            }


            .feed-card {
                width: calc(100% - 20px);

                margin-left: 10px;

                margin-right: 10px;

                padding: 15px;
            }


            .bottom-navigation {
                width: calc(100% - 20px);
            }


            .slide img {
                height: 350px;
            }

        }
.civic-page-layout {
    display: flex;
    align-items: flex-start;
    gap: 25px;
    max-width: 1250px;
    margin: 25px auto;
    padding: 0 20px;
}

.civic-sidebar {
    width: 210px;
    flex-shrink: 0;
    background: #ffffff;
    border: 1px solid #166534;
    border-radius: 16px;
    padding: 15px;
    box-shadow: 0 5px 16px rgba(22, 101, 52, 0.10);
    position: sticky;
    top: 90px;
}
.sidebar-title {
    font-family: Georgia, "Times New Roman", serif;
    font-size: 15px;
    font-weight: 700;
    color: #166534;
    padding: 5px 14px 12px;
    letter-spacing: 0.5px;
}
.civic-sidebar a {
    display: block;
    text-decoration: none;
    color: #1F2937;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 15px;
    font-weight: 600;
    padding: 13px 14px;
    margin-bottom: 5px;
    border-radius: 10px;
    transition: all 0.2s ease;
}
.civic-sidebar a:hover {
    background: #F5EED8;
    color: #166534;
    border-left: 3px solid #C9A227;
    transform: translateX(3px);
}

.civic-sidebar a:first-of-type {
    background: #166534;
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(22, 101, 52, 0.20);
}
.civic-main-content {
    flex: 1;
    min-width: 0;
}
   .civic-sidebar a {
    display: flex;
    align-items: center;
    gap: 10px;
}
  .civic-main-content {
    flex: 1;
    max-width: 750px;
}
  .civic-sidebar a {
    margin-bottom: 8px;
    padding: 14px 15px;
}
  </style>

</head>


<body>


<!-- ================================================= -->
<!-- CIVICVOICE HEADER -->
<!-- ================================================= -->

<header class="civic-header">

    <div class="civic-logo">
    <img src="logo.jpg" alt="CivicVoice Logo">
</div>

    <nav class="civic-nav">
<a href="index.php">🏠 Home</a>


        <a href="submit_complaint.php">
    📝 Submit Complaint
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


<div class="civic-page-layout">

    <aside class="civic-sidebar">

        <div class="sidebar-title">
            CivicVoice Menu
        </div>

        <a href="home.php">
            🏠 Home
        </a>

        <a href="profile.php">
            👤 My Profile
        </a>

        <a href="submit_complaint.php">
    📝 Submit Complaint
</a>

        <a href="my_complaints.php">
            📋 My Issues
        </a>

    </aside>

    <main class="civic-main-content">

<!-- WELCOME -->
<!-- ================================================= -->
<!-- WELCOME -->
<!-- ================================================= -->

<div class="welcome-section">

    <h2>
        Welcome, <?php echo htmlspecialchars($_SESSION["citizen_name"]); ?>
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
/* GET CITIZEN PROFILE INFORMATION */
/* ================================================= */

$profile_citizen_id = (int)$row["citizen_id"];

$profile_sql = "
    SELECT citizen_name, profile_image
    FROM citizens
    WHERE citizen_id='$profile_citizen_id'
    LIMIT 1
";

$profile_result = mysqli_query($conn, $profile_sql);

$profile_data = mysqli_fetch_assoc($profile_result);

$profile_name = "";
$profile_image = "";

if ($profile_data) {
    $profile_name = $profile_data["citizen_name"];
    $profile_image = $profile_data["profile_image"];
}
        /* ================================================= */
        /* DEPARTMENT */
        /* ================================================= */

        $department_sql = "
            SELECT department_name
            FROM departments
            WHERE department_id='" . $row["department_id"] . "'
        ";


        $department_result = mysqli_query(
            $conn,
            $department_sql
        );


        $department_data = mysqli_fetch_assoc(
            $department_result
        );


        $department_name = "";


        if ($department_data) {

            $department_name =
                $department_data["department_name"];

        }


        /* ================================================= */
        /* SUPPORT COUNT */
        /* ================================================= */

        $support_count_sql = "
            SELECT COUNT(*) AS total_support
            FROM complaint_support
            WHERE complaint_id='$feed_id'
        ";


        $support_count_result = mysqli_query(
            $conn,
            $support_count_sql
        );


        $support_data = mysqli_fetch_assoc(
            $support_count_result
        );


        $support_count =
            $support_data["total_support"];


        /* ================================================= */
        /* COMMENT COUNT */
        /* ================================================= */

        $comment_count_sql = "
            SELECT COUNT(*) AS total_comments
            FROM complaint_comments
            WHERE complaint_id='$feed_id'
        ";


        $comment_count_result = mysqli_query(
            $conn,
            $comment_count_sql
        );


        $comment_data = mysqli_fetch_assoc(
            $comment_count_result
        );


        $comment_count =
            $comment_data["total_comments"];


        /* ================================================= */
        /* GET IMAGES */
        /* ================================================= */

        $image_paths = array();


        if ($row["feed_type"] == "Post") {


            $actual_post_id =
                substr($feed_id, 1);


            $image_sql = "
                SELECT image_path
                FROM civic_post_images
                WHERE post_id='$actual_post_id'
                ORDER BY image_id ASC
            ";


        } else {


            $image_sql = "
                SELECT image_path
                FROM complaint_images
                WHERE complaint_id='$feed_id'
                ORDER BY image_id ASC
            ";

        }


        $image_result = mysqli_query(
            $conn,
            $image_sql
        );


        if ($image_result) {


            while (
                $image_row =
                mysqli_fetch_assoc($image_result)
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


?>

<!-- ================================================= -->
<!-- ONE FEED CARD -->
<!-- ================================================= -->

<div
    class="feed-card"
    id="post_<?php echo htmlspecialchars($feed_id); ?>"
>

<!-- ================================================= -->
<!-- CITIZEN PROFILE -->
<!-- ================================================= -->

<a
    href="public_profile.php?citizen_id=<?php echo (int)$profile_citizen_id; ?>"
    class="citizen-feed-profile"
>

    <?php if (!empty($profile_image)) { ?>

        <img
            src="<?php echo htmlspecialchars($profile_image); ?>"
            alt="Profile Photo"
            class="citizen-feed-profile-image"
        >

    <?php } else { ?>

        <div class="citizen-feed-profile-placeholder">
            👤
        </div>

    <?php } ?>

    <div class="citizen-feed-profile-details">

        <strong>
            <?php echo htmlspecialchars($profile_name); ?>
        </strong>

        <span>
            View Profile
        </span>

    </div>

</a>


<?php

/* ================================================= */
/* FEED TYPE */
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

        <?php echo htmlspecialchars($feed_id); ?>

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

        <?php echo htmlspecialchars($feed_id); ?>

    </p>


<?php

}

?>


<!-- ================================================= -->
<!-- AREA -->
<!-- ================================================= -->

<p class="feed-info">

    📍

    <strong>
        Area:
    </strong>

    <?php echo htmlspecialchars($row["area_name"]); ?>

</p>


<!-- ================================================= -->
<!-- DEPARTMENT -->
<!-- ================================================= -->

<p class="feed-info">

    🏛️

    <strong>
        Department:
    </strong>

    <?php echo htmlspecialchars($department_name); ?>

</p>


<!-- ================================================= -->
<!-- ISSUE -->
<!-- ================================================= -->

<p class="feed-info">

    📝

    <strong>
        Issue:
    </strong>

    <?php echo nl2br(
        htmlspecialchars($row["issue_description"])
    ); ?>

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
/* IMAGE CAROUSEL */
/* ================================================= */

if (count($image_paths) > 0) {


    $carousel_id =
        "carousel_" .
        preg_replace(
            "/[^a-zA-Z0-9_]/",
            "_",
            $feed_id
        );

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


    foreach ($image_paths as $image_path) {


?>


        <div class="slide">


            <img
                src="<?php echo htmlspecialchars($image_path); ?>"
                draggable="false"
                alt="Civic issue image"
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
/* IMAGE SLIDER JAVASCRIPT */
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


    function updateCounter() {


        if (counter) {


            counter.innerHTML =
                (currentImage + 1) +
                " / " +
                totalImages;

        }

    }


    function setSlide(index, animate) {


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


    if (totalImages <= 1) {

        return;

    }


    /* ================================================= */
    /* POINTER DOWN */
    /* ================================================= */

    carousel.addEventListener(
        "pointerdown",
        function (event) {


            startX =
                event.clientX;


            currentX =
                event.clientX;


            startPosition =
                currentImage * -100;


            isDragging =
                true;


            carousel.classList.add(
                "dragging"
            );


            slider.style.transition =
                "none";


            try {


                carousel.setPointerCapture(
                    event.pointerId
                );


            } catch (error) {

            }

        }
    );


    /* ================================================= */
    /* POINTER MOVE */
    /* ================================================= */

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


            var width =
                carousel.offsetWidth;


            if (width <= 0) {

                return;

            }


            var movement =
                (difference / width) * 100;


            var position =
                startPosition + movement;


            /* FIRST IMAGE */

            if (
                currentImage == 0 &&
                difference > 0
            ) {


                movement =
                    movement * 0.35;


                position =
                    startPosition + movement;

            }


            /* LAST IMAGE */

            if (
                currentImage ==
                totalImages - 1 &&
                difference < 0
            ) {


                movement =
                    movement * 0.35;


                position =
                    startPosition + movement;

            }


            slider.style.transform =
                "translateX(" +
                position +
                "%)";

        }
    );


    /* ================================================= */
    /* POINTER UP */
    /* ================================================= */

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


            setSlide(
                currentImage,
                true
            );


            try {


                carousel.releasePointerCapture(
                    event.pointerId
                );


            } catch (error) {

            }

        }
    );


    /* ================================================= */
    /* POINTER CANCEL */
    /* ================================================= */

    carousel.addEventListener(
        "pointercancel",
        function () {


            if (!isDragging) {

                return;

            }


            isDragging =
                false;


            carousel.classList.remove(
                "dragging"
            );


            setSlide(
                currentImage,
                true
            );

        }
    );


    /* ================================================= */
    /* INITIAL SLIDE */
    /* ================================================= */

    setSlide(
        0,
        false
    );


})();

</script>


<?php

}




/* ================================================= */
/* VIDEO */
/* ================================================= */

if (!empty($row["post_video"])) {


?>


<video
    class="civic-video"
    controls
    playsinline
>


    <source
        src="<?php echo htmlspecialchars($row["post_video"]); ?>"
        type="video/mp4"
    >


    Your browser does not support video playback.


</video>


<?php

}


/* ================================================= */
/* STATUS */
/* ================================================= */

?>


<p class="feed-info">

    📌

    <strong>
        Status:
    </strong>

    <?php echo htmlspecialchars($row["status"]); ?>

</p>


<!-- ================================================= -->
<!-- ACTION BUTTONS -->
<!-- ================================================= -->

<div class="feed-actions">


    <!-- SUPPORT -->

    <a
        href="home.php?support=<?php echo urlencode($feed_id); ?>"
        onclick="sessionStorage.setItem('civicvoice_scroll', window.scrollY);"
    >

        <button type="button">

            👍 Support <?php echo $support_count; ?>

        </button>

    </a>


    <!-- COMMENT -->

    <a
        href="comments.php?complaint_id=<?php echo urlencode($feed_id); ?>"
    >

        <button type="button">

            💬 Comment <?php echo $comment_count; ?>

        </button>

    </a>


</div>


</div>


<?php

    }

 }else {


?>


<!-- ================================================= -->
<!-- NO POSTS -->
<!-- ================================================= -->

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
</main>
</div>

<div class="bottom-navigation">


    <button
        type="button"
        onclick="window.location.href='dashboard.php'"
    >

        Dashboard

    </button>


    <button
        type="button"
        onclick="window.location.href='logout.php'"
    >

        Logout

    </button>


</div>


<!-- ================================================= -->
<!-- RESTORE SCROLL POSITION AFTER SUPPORT -->
<!-- ================================================= -->

<script>

window.addEventListener("load", function () {


    var savedScroll =
        sessionStorage.getItem(
            "civicvoice_scroll"
        );


    if (savedScroll !== null) {


        window.scrollTo(
            0,
            parseInt(savedScroll)
        );


        sessionStorage.removeItem(
            "civicvoice_scroll"
        );

    }

});

</script>


</body>

</html>