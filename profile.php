<?php

session_start();
include "db.php";

/* ================================================= */
/* CHECK CITIZEN LOGIN */
/* ================================================= */

if (!isset($_SESSION["citizen_id"])) {
    header("Location: login.php");
    exit;
}

$citizen_id = $_SESSION["citizen_id"];


/* ================================================= */
/* PROFILE IMAGE UPLOAD */
/* ================================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] == 0) {

        $profile_image = $_FILES["profile_image"];

        $extension = strtolower(
            pathinfo($profile_image["name"], PATHINFO_EXTENSION)
        );

        $allowed_extensions = ["jpg", "jpeg", "png", "webp"];

        if (!in_array($extension, $allowed_extensions)) {
            die("Invalid profile image format.");
        }

        $file_name = "profile_" . $citizen_id . "." . $extension;

        $upload_directory = "C:/xampp/htdocs/CivicVoice/profile_images/";

        if (!is_dir($upload_directory)) {
            mkdir($upload_directory, 0777, true);
        }

        $file_path = $upload_directory . $file_name;

        if (move_uploaded_file($profile_image["tmp_name"], $file_path)) {

            $database_path = "profile_images/" . $file_name;

            $sql = "
                UPDATE citizens
                SET profile_image='$database_path'
                WHERE citizen_id='$citizen_id'
            ";

            if (mysqli_query($conn, $sql)) {
                header("Location: profile.php");
                exit;
            }

        } else {

            die("Profile image upload failed.");
        }
    }
}


/* ================================================= */
/* GET CITIZEN DETAILS */
/* ================================================= */

$sql = "
    SELECT citizen_name, username, profile_image
    FROM citizens
    WHERE citizen_id='$citizen_id'
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Citizen Query Error: " . mysqli_error($conn));
}

$citizen = mysqli_fetch_assoc($result);


/* ================================================= */
/* GET CITIZEN COMPLAINTS */
/* ================================================= */

$complaint_sql = "
    SELECT
        complaints.*,
        departments.department_name
    FROM complaints
    INNER JOIN departments
        ON complaints.department_id = departments.department_id
    WHERE complaints.citizen_id='$citizen_id'
    ORDER BY complaints.created_at DESC
";

$complaint_result = mysqli_query($conn, $complaint_sql);

if (!$complaint_result) {
    die("Complaint Query Error: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html>

<head>

    <link rel="stylesheet" href="style.css">

    <title>CivicVoice Profile</title>

    <style>

        /* =========================================================
           PROFILE PAGE
           ========================================================= */

        body {
            margin: 0;
            background-color: #E8E2D5;
            color: #1F2937;
        }


        /* =========================================================
           PROFILE HEADER
           ========================================================= */

        body > h2 {
            max-width: 760px;
            margin: 30px auto 20px;
            padding: 18px;
            background-color: #ffffff;
            border: 2px solid #166534;
            border-top: 6px solid #166534;
            border-radius: 18px;
            box-shadow: 0 6px 18px rgba(22, 101, 52, 0.12);
            color: #166534;
            font-size: 26px;
            font-weight: 700;
            box-sizing: border-box;
            text-align: center;
        }


        /* =========================================================
           PROFILE IMAGE
           ========================================================= */

        .profile-image-container {
            text-align: center;
            margin: 20px auto;
        }

        .profile-image-label {
            display: inline-block;
            cursor: pointer;
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #C9A227;
            box-shadow: 0 5px 15px rgba(31, 41, 55, 0.15);
            transition: 0.2s ease;
        }

        .profile-image:hover {
            opacity: 0.8;
            transform: scale(1.03);
        }

        .no-profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-color: #ffffff;
            border: 4px solid #C9A227;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
        }


        /* =========================================================
           PROFILE NAME
           ========================================================= */

        .profile-name {
            text-align: center;
            color: #166534;
            font-size: 22px;
            margin: 15px 0 5px;
        }


        /* =========================================================
           PROFILE USERNAME
           ========================================================= */

        .profile-username {
            display: block;
            width: fit-content;
            margin: 8px auto 22px;
            padding: 6px 16px;
            color: #166534;
            background-color: #ffffff;
            border: 1px solid #C9A227;
            border-radius: 20px;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 3px 10px rgba(31, 41, 55, 0.08);
        }


        /* =========================================================
           PROFILE IMAGE UPLOAD FORM
           ========================================================= */

        .profile-upload-form {
            max-width: 760px;
            margin: 0 auto 25px;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #ddd6c8;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(31, 41, 55, 0.06);
            box-sizing: border-box;
            text-align: center;
        }

        .profile-upload-form input[type="file"] {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 2px dashed #C9A227;
            border-radius: 10px;
            background-color: #faf9f5;
        }


        /* =========================================================
           MY COMPLAINTS HEADING
           ========================================================= */

        .complaints-heading {
            max-width: 760px;
            margin: 30px auto 20px;
            color: #166534;
            font-size: 23px;
            font-weight: 700;
            text-align: center;
        }


        /* =========================================================
           COMPLAINT CARD
           ========================================================= */

        .profile-post-card {
            max-width: 760px;
            margin: 25px auto;
            padding: 20px;
            background-color: #ffffff;
            border: 2px solid #C9A227;
            border-radius: 16px;
            box-sizing: border-box;
            box-shadow: 0 5px 15px rgba(31, 41, 55, 0.08);
        }

        .profile-post-card hr {
            border: none;
            border-top: 1px solid #ddd6c8;
            margin-bottom: 20px;
        }

        .profile-post-card p {
            line-height: 1.6;
            color: #374151;
            margin: 10px 0;
        }

        .profile-post-card p strong {
            color: #166534;
        }


        /* =========================================================
   COMPLAINT PHOTOS CAROUSEL
   ========================================================= */
.complaint-photos-heading {
    color: #166534;
    font-size: 18px;
    margin-top: 20px;
}

.profile-photo-carousel {
    position: relative;
    width: 100%;
    max-width: 650px;
    height: 350px;
    margin: 15px auto;
    overflow: hidden;
    border-radius: 12px;
    border: 2px solid #C9A227;
    box-sizing: border-box;
    background-color: #f5f5f5;
}

.profile-photo-track {
    display: flex;
    width: 100%;
    height: 100%;
    transition: transform 0.3s ease;
}

.profile-photo-slide {
    min-width: 100%;
    width: 100%;
    height: 100%;
    object-fit: cover;
    flex-shrink: 0;
}

.profile-photo-prev,
.profile-photo-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 50%;
    background-color: rgba(0, 0, 0, 0.55);
    color: white;
    font-size: 22px;
    font-weight: bold;
    cursor: pointer;
    z-index: 5;
}

.profile-photo-prev {
    left: 10px;
}

.profile-photo-next {
    right: 10px;
}

.profile-photo-prev:hover,
.profile-photo-next:hover {
    background-color: rgba(22, 101, 52, 0.9);
}

.profile-photo-counter {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    padding: 5px 12px;
    border-radius: 15px;
    background-color: rgba(0, 0, 0, 0.65);
    color: white;
    font-size: 13px;
    z-index: 5;
}

@media (max-width: 700px) {
    .profile-photo-carousel {
        height: 300px;
    }
}

        /* =========================================================
           ACTION BUTTONS
           ========================================================= */

        .post-actions {
            max-width: 700px;
            margin: 20px auto 5px;
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        .post-actions button {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .edit-post-btn {
            background-color: #166534;
            color: white;
        }

        .edit-post-btn:hover {
            background-color: #14532d;
        }

        .delete-post-btn {
            background-color: #b91c1c;
            color: white;
        }

        .delete-post-btn:hover {
            background-color: #991b1b;
        }


        /* =========================================================
           NO COMPLAINTS MESSAGE
           ========================================================= */

        .no-complaints {
            max-width: 760px;
            margin: 30px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 14px;
            text-align: center;
            color: #666666;
            box-shadow: 0 4px 12px rgba(31, 41, 55, 0.06);
        }


        /* =========================================================
           BACK TO HOME
           ========================================================= */

        .profile-home-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 9999;
        }

        .profile-home-button button {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            background-color: #166534;
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .profile-home-button button:hover {
            background-color: #C9A227;
            color: #1F2937;
        }


        /* =========================================================
           MOBILE
           ========================================================= */

        @media (max-width: 700px) {

            body > h2 {
                margin: 20px 10px;
                padding: 15px;
                font-size: 22px;
            }

            .profile-post-card,
            .profile-upload-form,
            .complaints-heading {
                margin-left: 10px;
                margin-right: 10px;
            }

            .complaint-photo {
                width: 100%;
                height: 300px;
            }

            .post-actions {
                flex-direction: column;
            }

            .post-actions button {
                width: 100%;
            }

            .profile-home-button {
                top: 10px;
                left: 10px;
            }
        }

    </style>

</head>

<body>

    <!-- BACK TO HOME -->

    <div class="profile-home-button">
        <button onclick="window.location.href='home.php'">
            ← Back to Home
        </button>
    </div>


    <!-- PROFILE HEADING -->

    <h2>👤 My Profile</h2>


    <!-- PROFILE IMAGE -->

    <div class="profile-image-container">

        <form
            method="POST"
            enctype="multipart/form-data"
            id="profileForm"
            class="profile-upload-form"
        >

            <label
                for="profileImageInput"
                class="profile-image-label"
            >

                <?php if (!empty($citizen["profile_image"])) { ?>

                    <img
                        src="<?php echo htmlspecialchars($citizen["profile_image"]); ?>?v=<?php echo time(); ?>"
                        class="profile-image"
                        alt="Profile Image"
                    >

                <?php } else { ?>

                    <div class="no-profile-image">
                        👤
                    </div>

                <?php } ?>

            </label>

            <input
                type="file"
                id="profileImageInput"
                name="profile_image"
                accept="image/*"
                hidden
                onchange="document.getElementById('profileForm').submit();"
            >

        </form>

    </div>


    <!-- NAME -->

    <h3 class="profile-name">
        <?php echo htmlspecialchars($citizen["citizen_name"]); ?>
    </h3>


    <!-- USERNAME -->

    <div class="profile-username">
        @<?php echo htmlspecialchars($citizen["username"]); ?>
    </div>


    <!-- MY COMPLAINTS -->

    <h2 class="complaints-heading">
        📋 My Complaints
    </h2>


    <?php if (mysqli_num_rows($complaint_result) > 0) { ?>

        <?php while ($complaint = mysqli_fetch_assoc($complaint_result)) { ?>

            <div class="profile-post-card">

                <hr>

                <p>
                    <strong>Complaint ID:</strong>
                    <?php echo htmlspecialchars($complaint["complaint_id"]); ?>
                </p>

                <p>
                    <strong>Constituency:</strong>
                    <?php echo htmlspecialchars($complaint["constituency_name"]); ?>
                </p>

                <p>
                    <strong>Ward Number:</strong>
                    <?php echo htmlspecialchars($complaint["ward_number"]); ?>
                </p>

                <p>
                    <strong>Area:</strong>
                    <?php echo htmlspecialchars($complaint["area_name"]); ?>
                </p>

                <p>
                    <strong>PIN Code:</strong>
                    <?php echo htmlspecialchars($complaint["pincode"]); ?>
                </p>

                <p>
                    <strong>Department:</strong>
                    <?php echo htmlspecialchars($complaint["department_name"]); ?>
                </p>

                <p>
                    <strong>Issue:</strong>
                    <?php echo nl2br(htmlspecialchars($complaint["issue_description"])); ?>
                </p>

                <p>
                    <strong>Status:</strong>
                    <?php echo htmlspecialchars($complaint["status"]); ?>
                </p>

                <p>
                    <strong>Submitted On:</strong>
                    <?php echo htmlspecialchars($complaint["created_at"]); ?>
                </p>


                <!-- COMPLAINT PHOTOS -->
<!-- COMPLAINT PHOTOS -->
<?php

$complaint_id_safe = mysqli_real_escape_string(
    $conn,
    $complaint["complaint_id"]
);

$complaint_image_sql = "
    SELECT image_path
    FROM complaint_images
    WHERE complaint_id='$complaint_id_safe'
    ORDER BY image_id ASC
";

$complaint_image_result = mysqli_query(
    $conn,
    $complaint_image_sql
);

if ($complaint_image_result && mysqli_num_rows($complaint_image_result) > 0):

    $profile_images = [];

    while ($complaint_image = mysqli_fetch_assoc($complaint_image_result)) {
        $profile_images[] = $complaint_image["image_path"];
    }

?>

    <h3 class="complaint-photos-heading">
        📷 Complaint Photos
    </h3>

    <div class="profile-photo-carousel">

        <div class="profile-photo-track">

            <?php foreach ($profile_images as $image_path): ?>

                <img
                    src="<?php echo htmlspecialchars($image_path); ?>"
                    class="profile-photo-slide"
                    alt="Complaint Photo"
                >

            <?php endforeach; ?>

        </div>

        <?php if (count($profile_images) > 1): ?>

            <button
                type="button"
                class="profile-photo-prev"
                onclick="moveProfilePhoto(this, -1)"
            >
                ‹
            </button>

            <button
                type="button"
                class="profile-photo-next"
                onclick="moveProfilePhoto(this, 1)"
            >
                ›
            </button>

            <div class="profile-photo-counter">
                <span>1</span> / <?php echo count($profile_images); ?>
            </div>

        <?php endif; ?>

    </div>

<?php
elseif (!empty($complaint["issue_image"])):
?>

    <h3 class="complaint-photos-heading">
        📷 Complaint Photo
    </h3>

    <img
        src="<?php echo htmlspecialchars($complaint["issue_image"]); ?>"
        class="profile-photo-slide"
        style="
            display:block;
            width:100%;
            max-width:650px;
            height:350px;
            object-fit:cover;
            margin:15px auto;
            border-radius:12px;
            border:2px solid #C9A227;
            box-sizing:border-box;
        "
        alt="Complaint Photo"
    >

<?php endif; ?>


                <!-- COMPLAINT ACTIONS -->

                <div class="post-actions">

                    <button
                        type="button"
                        class="edit-post-btn"
                        onclick="window.location.href='edit_complaint.php?complaint_id=<?php echo urlencode($complaint["complaint_id"]); ?>'"
                    >
                        ✏️ Update Complaint
                    </button>

                    <button
                        type="button"
                        class="delete-post-btn"
                        onclick="confirmComplaintDelete('<?php echo htmlspecialchars($complaint["complaint_id"], ENT_QUOTES); ?>')"
                    >
                        🗑️ Delete Complaint
                    </button>

                </div>

            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="no-complaints">
            You have not submitted any complaints yet.
        </div>

    <?php } ?>

<script>

    /* =========================================================
       PROFILE COMPLAINT PHOTO CAROUSEL
       ========================================================= */

    function moveProfilePhoto(button, direction) {

        const carousel = button.closest(".profile-photo-carousel");

        const track = carousel.querySelector(".profile-photo-track");

        const slides = carousel.querySelectorAll(".profile-photo-slide");

        const counter = carousel.querySelector(
            ".profile-photo-counter span"
        );

        let currentIndex = parseInt(
            carousel.dataset.currentIndex || "0"
        );

        currentIndex += direction;

        if (currentIndex < 0) {
            currentIndex = slides.length - 1;
        }

        if (currentIndex >= slides.length) {
            currentIndex = 0;
        }

        track.style.transform =
            "translateX(-" + (currentIndex * 100) + "%)";

        carousel.dataset.currentIndex = currentIndex;

        if (counter) {
            counter.textContent = currentIndex + 1;
        }
    }


    /* =========================================================
       COMPLAINT DELETE CONFIRMATION
       ========================================================= */

    function confirmComplaintDelete(complaintId) {

        if (confirm("Are you sure you want to delete this complaint?")) {

            window.location.href =
                "delete_complaint.php?complaint_id=" +
                encodeURIComponent(complaintId);

        }

    }

</script>
</body>

</html>