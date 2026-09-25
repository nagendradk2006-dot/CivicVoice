<?php

session_start();

if (!isset($_SESSION["citizen_id"])) {
    header("Location: login.php");
    exit;
}

include "db.php";

/* =================================================
   GET CITIZEN ID
   ================================================= */

if (!isset($_GET["citizen_id"]) || !is_numeric($_GET["citizen_id"])) {
    header("Location: home.php");
    exit;
}

$profile_citizen_id = (int) $_GET["citizen_id"];


/* =================================================
   GET CITIZEN PROFILE
   ================================================= */

$profile_sql = "
    SELECT
        citizen_id,
        citizen_name,
        profile_image
    FROM citizens
    WHERE citizen_id='$profile_citizen_id'
    LIMIT 1
";

$profile_result = mysqli_query($conn, $profile_sql);

if (!$profile_result || mysqli_num_rows($profile_result) == 0) {
    header("Location: home.php");
    exit;
}

$profile = mysqli_fetch_assoc($profile_result);

$profile_name = $profile["citizen_name"];
$profile_image = $profile["profile_image"];


/* =================================================
   GET COMPLAINT COUNT
   ================================================= */

$count_sql = "
    SELECT COUNT(*) AS total_complaints
    FROM complaints
    WHERE citizen_id='$profile_citizen_id'
";

$count_result = mysqli_query($conn, $count_sql);

$count_data = mysqli_fetch_assoc($count_result);

$total_complaints = $count_data["total_complaints"];


/* =================================================
   GET THIS CITIZEN'S COMPLAINTS
   ================================================= */

$complaint_sql = "
    SELECT
        complaint_id,
        area_name,
        department_id,
        issue_description,
        status,
        created_at
    FROM complaints
    WHERE citizen_id='$profile_citizen_id'
    ORDER BY created_at DESC
";

$complaint_result = mysqli_query($conn, $complaint_sql);

if (!$complaint_result) {
    die("Complaint Error: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    <?php echo htmlspecialchars($profile_name); ?> - CivicVoice
</title>

<link rel="stylesheet" href="style.css">

<style>

/* =================================================
   PAGE
   ================================================= */

body {
    margin: 0;
    background-color: #E8E2D5;
    color: #1F2937;
    font-family: Arial, sans-serif;
}


/* =================================================
   HEADER
   ================================================= */

.public-header {
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

.public-logo img {
    width: 250px;
    height: auto;
    display: block;
}

.public-nav {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.public-nav a {
    background-color: #C9A227;
    color: #1F2937;
    text-decoration: none;
    font-weight: 600;
    padding: 9px 14px;
    border-radius: 8px;
}


/* =================================================
   PROFILE HEADER
   ================================================= */

.public-profile {
    width: calc(100% - 40px);
    max-width: 700px;
    margin: 30px auto 25px;
    padding: 18px 20px;
    background-color: white;
    border: 2px solid #166534;
    border-top: 6px solid #166534;
    border-radius: 18px;
    box-sizing: border-box;
    text-align: center;
    box-shadow: 0 6px 18px rgba(22, 101, 52, 0.12);
}

.public-profile-image {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #C9A227;
    display: block;
    margin: 0 auto 10px;
}

.public-profile-placeholder {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background-color: #166534;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    margin: 0 auto 10px;
    border: 3px solid #C9A227;
}
.public-profile h1 {
    margin: 8px 0;
    color: #166534;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 28px;
}

.public-profile-count {
    margin: 10px 0 0;
    color: #555555;
    font-size: 15px;
    font-weight: 600;
}


/* =================================================
   BACK BUTTON
   ================================================= */

.back-home {
    display: block;
    width: 220px;
    margin: 0 auto 25px;
    padding: 11px 18px;
    background-color: #333333;
    color: white;
    text-align: center;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 700;
    box-sizing: border-box;
}

.back-home:hover {
    background-color: #111111;
}


/* =================================================
   COMPLAINT TITLE
   ================================================= */

.profile-complaints-title {
    width: calc(100% - 40px);
    max-width: 700px;
    margin: 25px auto 15px;
    color: #166534;
    font-size: 23px;
    font-weight: 700;
}


/* =================================================
   COMPLAINT CARD
   ================================================= */

.public-complaint-card {
    width: calc(100% - 40px);
    max-width: 700px;
    margin: 20px auto;
    padding: 20px;
    background-color: white;
    border: 1px solid #ddd6c8;
    border-radius: 16px;
    box-sizing: border-box;
    box-shadow: 0 5px 16px rgba(31, 41, 55, 0.08);
}

.public-complaint-card h3 {
    margin: 0 0 15px;
    color: #166534;
    font-size: 19px;
}


/* =================================================
   COMPLAINT INFORMATION
   ================================================= */

.complaint-info {
    margin: 10px 0;
    line-height: 1.6;
    color: #374151;
    font-size: 15px;
}


/* =================================================
   IMAGE CAROUSEL
   ================================================= */

.public-carousel {
    width: 100%;
    height: 400px;
    margin: 18px auto 5px;
    overflow: hidden;
    position: relative;
    background-color: white;
    border: 2px solid #C9A227;
    border-radius: 14px;
    touch-action: pan-y;
    cursor: grab;
    user-select: none;
}

.public-carousel.dragging {
    cursor: grabbing;
}

.public-slider {
    display: flex;
    width: 100%;
    height: 100%;
    transition: transform 0.35s ease;
}

.public-slide {
    flex: 0 0 100%;
    width: 100%;
    min-width: 100%;
    height: 100%;
    box-sizing: border-box;
}

.public-slide img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: contain;
    background-color: white;
    pointer-events: none;
    user-select: none;
    -webkit-user-drag: none;
}


/* =================================================
   IMAGE COUNTER
   ================================================= */

.public-image-counter {
    text-align: center;
    margin: 8px auto;
    font-weight: bold;
    color: #555555;
}


/* =================================================
   NO COMPLAINTS
   ================================================= */

.no-complaints {
    width: calc(100% - 40px);
    max-width: 700px;
    margin: 20px auto;
    padding: 30px 20px;
    background-color: white;
    border: 1px solid #C9A227;
    border-radius: 15px;
    text-align: center;
    color: #555555;
    box-sizing: border-box;
}


/* =================================================
   MOBILE
   ================================================= */

@media (max-width: 700px) {

    .public-header {
        padding: 15px;
        flex-direction: column;
        gap: 12px;
    }

    .public-logo img {
        width: 220px;
    }

    .public-profile,
    .public-complaint-card,
    .profile-complaints-title {
        width: calc(100% - 20px);
    }

    .public-carousel {
        height: 320px;
    }

}

</style>

</head>


<body>


<!-- =================================================
     HEADER
     ================================================= -->

<header class="public-header">

    <div class="public-logo">

        <img
            src="logo.jpg"
            alt="CivicVoice Logo"
        >

    </div>


    <nav class="public-nav">

        <a href="home.php">
            🏠 Home
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


<!-- =================================================
     PUBLIC PROFILE
     ================================================= -->

<div class="public-profile">


<?php if (!empty($profile_image)) { ?>

    <img
        src="<?php echo htmlspecialchars($profile_image); ?>"
        alt="Profile Photo"
        class="public-profile-image"
    >

<?php } else { ?>

    <div class="public-profile-placeholder">
        👤
    </div>

<?php } ?>


    <h1>
        <?php echo htmlspecialchars($profile_name); ?>
    </h1>


    <p class="public-profile-count">

        <?php echo $total_complaints; ?>

        Complaint<?php echo ($total_complaints == 1) ? "" : "s"; ?>

    </p>

</div>


<!-- =================================================
     BACK TO HOME
     ================================================= -->

<a
    href="home.php"
    class="back-home"
>
    ← Back to Home
</a>


<!-- =================================================
     COMPLAINT TITLE
     ================================================= -->

<div class="profile-complaints-title">

    📋 Complaints posted by
    <?php echo htmlspecialchars($profile_name); ?>

</div>


<?php

/* =================================================
   DISPLAY COMPLAINTS
   ================================================= */

if (mysqli_num_rows($complaint_result) > 0) {

    while ($complaint = mysqli_fetch_assoc($complaint_result)) {

        $complaint_id = $complaint["complaint_id"];


        /* =================================================
           GET DEPARTMENT
           ================================================= */

        $department_name = "";

        $department_sql = "
            SELECT department_name
            FROM departments
            WHERE department_id='" .
            (int)$complaint["department_id"] . "'
            LIMIT 1
        ";

        $department_result = mysqli_query(
            $conn,
            $department_sql
        );

        if ($department_result) {

            $department_data =
                mysqli_fetch_assoc(
                    $department_result
                );

            if ($department_data) {

                $department_name =
                    $department_data["department_name"];

            }

        }


        /* =================================================
           GET COMPLAINT IMAGES
           ================================================= */

        $image_paths = array();

        $image_sql = "
            SELECT image_path
            FROM complaint_images
            WHERE complaint_id='$complaint_id'
            ORDER BY image_id ASC
        ";

        $image_result = mysqli_query(
            $conn,
            $image_sql
        );

        if ($image_result) {

            while (
                $image_row =
                mysqli_fetch_assoc($image_result)
            ) {

                if (!empty($image_row["image_path"])) {

                    $image_paths[] =
                        $image_row["image_path"];

                }

            }

        }

?>


<!-- =================================================
     ONE PUBLIC COMPLAINT
     ================================================= -->

<div class="public-complaint-card">


    <h3>
        🚨 Civic Complaint
    </h3>


    <p class="complaint-info">

        🆔

        <strong>
            Complaint ID:
        </strong>

        <?php echo htmlspecialchars($complaint_id); ?>

    </p>


    <p class="complaint-info">

        📍

        <strong>
            Area:
        </strong>

        <?php echo htmlspecialchars($complaint["area_name"]); ?>

    </p>


    <p class="complaint-info">

        🏛️

        <strong>
            Department:
        </strong>

        <?php echo htmlspecialchars($department_name); ?>

    </p>


    <p class="complaint-info">

        📝

        <strong>
            Issue:
        </strong>

        <?php echo nl2br(
            htmlspecialchars(
                $complaint["issue_description"]
            )
        ); ?>

    </p>


    <p class="complaint-info">

        📌

        <strong>
            Status:
        </strong>

        <?php echo htmlspecialchars($complaint["status"]); ?>

    </p>


    <p class="complaint-info">

        🕒

        <strong>
            Posted On:
        </strong>

        <?php

        echo date(
            "d F Y, h:i A",
            strtotime(
                $complaint["created_at"]
            )
        );

        ?>

    </p>


<?php

/* =================================================
   COMPLAINT IMAGES
   ================================================= */

if (count($image_paths) > 0) {

    $carousel_id =
        "public_carousel_" .
        preg_replace(
            "/[^a-zA-Z0-9_]/",
            "_",
            $complaint_id
        );

?>


<div
    class="public-carousel"
    id="<?php echo $carousel_id; ?>"
>


    <div
        class="public-slider"
        id="<?php echo $carousel_id; ?>_slider"
    >


<?php

    foreach ($image_paths as $image_path) {

?>


        <div class="public-slide">

            <img
                src="<?php echo htmlspecialchars($image_path); ?>"
                draggable="false"
                alt="Complaint Image"
            >

        </div>


<?php

    }

?>


    </div>

</div>


<?php

if (count($image_paths) > 1) {

?>


<div class="public-image-counter">

    <span id="<?php echo $carousel_id; ?>_counter">
        1
    </span>

    /
    <?php echo count($image_paths); ?>

</div>


<?php

}


/* =================================================
   CAROUSEL JAVASCRIPT
   ================================================= */

if (count($image_paths) > 1) {

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

    var isDragging = false;


    function updateCounter() {

        if (counter) {

            counter.innerHTML =
                currentImage + 1;

        }

    }


    function setSlide(index) {

        currentImage = index;

        slider.style.transform =
            "translateX(-" +
            (currentImage * 100) +
            "%)";

        updateCounter();

    }


    carousel.addEventListener(
        "pointerdown",
        function (event) {

            startX =
                event.clientX;

            currentX =
                event.clientX;

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
                (currentImage * -100) +
                movement;

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

            var threshold =
                carousel.offsetWidth * 0.20;


            if (
                Math.abs(difference) >=
                threshold
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

            setSlide(
                currentImage
            );


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
        function () {

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

            setSlide(
                currentImage
            );

        }
    );


    setSlide(0);

})();

</script>


<?php

}

}

?>


</div>


<?php

    }

} else {

?>


<div class="no-complaints">

    <h3>
        No Complaints
    </h3>

    <p>
        This citizen has not posted any complaints yet.
    </p>

</div>


<?php

}

?>


</body>

</html>