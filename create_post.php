
<?php

session_start();
include "db.php";


/* Check citizen login */

if (!isset($_SESSION["citizen_id"])) {

    header("Location: login.php");
    exit;

}

$citizen_id = $_SESSION["citizen_id"];


/* Process form */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $constituency_name = $_POST["constituency_name"];
    $ward_number = $_POST["ward_number"];
    $area_name = $_POST["area_name"];
    $department = $_POST["department"];
    $issue_description = $_POST["issue_description"];
    $post_type = $_POST["post_type"];


    /* Find department ID */

    $department_sql = "SELECT department_id
                       FROM departments
                       WHERE department_name='$department'";

    $department_result = mysqli_query($conn, $department_sql);

    $department_data = mysqli_fetch_assoc($department_result);

    $department_id = $department_data["department_id"];


    /* Get uploaded files */

    $media = $_FILES["post_media"];

    $file_count = count($media["name"]);


    /* =========================
       PHOTO POST
       ========================= */

    if ($post_type == "photo") {


        /* Maximum 10 photos */

        if ($file_count > 10) {

            echo "<h2>You can upload a maximum of 10 photos.</h2>";
            exit;

        }


        if ($file_count < 1) {

            echo "<h2>Please select at least one photo.</h2>";
            exit;

        }


        /* Store uploaded image paths */

        $uploaded_images = [];


        /* Upload every photo */

        for ($i = 0; $i < $file_count; $i++) {


            $extension = pathinfo(
                $media["name"][$i],
                PATHINFO_EXTENSION
            );


            $file_name = "post_"
                       . time()
                       . "_"
                       . uniqid()
                       . "_"
                       . ($i + 1)
                       . "."
                       . $extension;


            $file_path = "Images/" . $file_name;


            /* Move image to Images folder */

            if (move_uploaded_file(
                $media["tmp_name"][$i],
                $file_path
            )) {

                $uploaded_images[] = $file_path;

            }

        }


        /* Check upload */

        if (count($uploaded_images) == 0) {

            echo "<h2>Image upload failed.</h2>";
            exit;

        }


        /* First image for main post table */

        $first_image = $uploaded_images[0];


        /* Create civic post */

        $sql = "INSERT INTO civic_posts
                (
                    citizen_id,
                    constituency_name,
                    ward_number,
                    area_name,
                    department_id,
                    issue_description,
                    post_image
                )
                VALUES
                (
                    '$citizen_id',
                    '$constituency_name',
                    '$ward_number',
                    '$area_name',
                    '$department_id',
                    '$issue_description',
                    '$first_image'
                )";


        if (!mysqli_query($conn, $sql)) {

            echo "Post creation failed: "
               . mysqli_error($conn);

            exit;

        }


        /* Get actual post ID */

        $post_id = mysqli_insert_id($conn);


        /* Save ALL images */

        foreach ($uploaded_images as $image_path) {

            $image_sql = "INSERT INTO civic_post_images
                          (
                              post_id,
                              image_path
                          )
                          VALUES
                          (
                              '$post_id',
                              '$image_path'
                          )";


            mysqli_query($conn, $image_sql);

        }

    }


    /* =========================
       VIDEO / REEL POST
       ========================= */

    else {


        /* Only one video */

        if ($file_count != 1) {

            echo "<h2>Please upload only one video.</h2>";
            exit;

        }


        $extension = pathinfo(
            $media["name"][0],
            PATHINFO_EXTENSION
        );


        $file_name = "video_"
                   . time()
                   . "_"
                   . uniqid()
                   . "."
                   . $extension;


        $file_path = "Images/" . $file_name;


        /* Upload video */

        if (!move_uploaded_file(
            $media["tmp_name"][0],
            $file_path
        )) {

            echo "<h2>Video upload failed.</h2>";
            exit;

        }


        /* Create video post */

        $sql = "INSERT INTO civic_posts
                (
                    citizen_id,
                    constituency_name,
                    ward_number,
                    area_name,
                    department_id,
                    issue_description,
                    post_image,
                    post_video
                )
                VALUES
                (
                    '$citizen_id',
                    '$constituency_name',
                    '$ward_number',
                    '$area_name',
                    '$department_id',
                    '$issue_description',
                    '',
                    '$file_path'
                )";


        if (!mysqli_query($conn, $sql)) {

            echo "Post creation failed: "
               . mysqli_error($conn);

            exit;

        }


        /* Get actual post ID */

        $post_id = mysqli_insert_id($conn);

    }


    /* Display success message */

    $display_post_id = "P" . $post_id;

    echo "
<!DOCTYPE html>
<html>
<head>
        <title>Post Created - CivicVoice</title>
        <link rel='stylesheet' href='style.css'>
   ";
?>
<style>

/* =========================================================
   POST SUCCESS PAGE
   ========================================================= */

body {
    margin: 0;
    min-height: 100vh;
    background: #E8E2D5;
    font-family: Arial, sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
}


/* SUCCESS CARD */

.success-container {
    width: 90%;
    max-width: 600px;
    box-sizing: border-box;
    background: #ffffff;
    padding: 45px 40px;
    border-radius: 20px;
    text-align: center;

    border: 1px solid #ddd6c8;

    box-shadow:
        0 10px 30px rgba(0, 0, 0, 0.12);
}


/* SUCCESS ICON */

.success-icon {
    width: 75px;
    height: 75px;

    margin: 0 auto 22px;

    border-radius: 50%;

    background: #166534;
    color: #ffffff;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 42px;
    font-weight: bold;

    box-shadow:
        0 5px 15px rgba(22, 101, 52, 0.25);
}


/* HEADING */

.success-container h2 {
    margin: 0 0 12px;

    color: #166534;

    font-size: 30px;
    font-weight: 700;
}


/* MESSAGE */

.success-container > p {
    margin: 0 0 28px;

    color: #666666;

    font-size: 16px;
    line-height: 1.6;
}


/* DETAILS BOX */

.success-details {
    width: 100%;
    box-sizing: border-box;

    background: #faf9f5;

    border: 1px solid #ddd6c8;

    border-radius: 12px;

    padding: 18px 20px;

    margin-bottom: 28px;

    text-align: left;
}


/* DETAILS */

.success-details p {
    margin: 8px 0;

    color: #333333;

    font-size: 15px;
}


.success-details strong {
    color: #166534;
}


/* HOME BUTTON */

.success-button {
    display: inline-block;

    padding: 13px 25px;

    background: #166534;

    color: #ffffff;

    text-decoration: none;

    border-radius: 10px;

    font-size: 16px;
    font-weight: 700;

    transition: 0.2s ease;
}


.success-button:hover {
    background: #14532d;

    transform: translateY(-1px);
}


/* MOBILE */

@media (max-width: 600px) {

    body {
        padding: 20px;
        box-sizing: border-box;
    }

    .success-container {
        width: 100%;
        padding: 35px 22px;
        border-radius: 16px;
    }

    .success-icon {
        width: 65px;
        height: 65px;
        font-size: 35px;
    }

    .success-container h2 {
        font-size: 25px;
    }

    .success-container > p {
        font-size: 15px;
    }

    .success-details {
        padding: 15px;
    }

    .success-button {
        width: 100%;
        box-sizing: border-box;
    }

}

</style>
<?php
echo "
</head>
    <body>

        <div class='success-container'>

            <div class='success-icon'>
                ✓
            </div>

            <h2>Post Created Successfully!</h2>

            <p>Your civic post has been published.</p>

            <div class='success-details'>

                <p>
                    <strong>Post ID:</strong>
                    $display_post_id
                </p>

                <p>
                    <strong>Post Type:</strong>
                    $post_type
                </p>

            </div>

            <a href='home.php' class='success-button'>
                Go to Home Feed
            </a>

        </div>

    </body>
    </html>
    ";

    exit;

}

?>


<!DOCTYPE html>

<html>

<head>

    <title>Create Post - CivicVoice</title>

    <link rel="stylesheet" href="style.css">

<style>

/* =========================================================
   1. PAGE BACKGROUND
   ========================================================= */

body {
    margin: 0;
    background-color: #E8E2D5;
    color: #1F2937;
}



/* =========================================================
   2. HEADER
   ========================================================= */

.civic-header {
    background-color: #ffffff;
    padding: 15px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid #C9A227;
    position: sticky;
    top: 0;
    z-index: 1000;
}

/* =========================================================
   3. LOGO
   ========================================================= */

.civic-logo img {
    width: 180px;
    max-width: 180px;
    height: auto;
    display: block;
}
/* =========================================================
   4. NAVIGATION
   ========================================================= */

.civic-nav {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
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
/* =========================================================
   5. CREATE POST BOX
   ========================================================= */

.create-post-container {
    width: 95%;
    max-width: 1100px;
    margin: 35px auto;
    padding: 45px;
    box-sizing: border-box;
    background-color: #ffffff;
    border: 2px solid #166534;
    border-radius: 18px;
    box-shadow: 0 6px 18px rgba(22, 101, 52, 0.12);
}

/* =========================================================
   6. FORM HEADINGS
   ========================================================= */

.create-post-container h1,
.create-post-container h2 {
    margin-top: 0;
    margin-bottom: 20px;
    text-align: center;
    color: #166534;
    font-weight: 700;
}
/* =========================================================
   7. FORM LABELS
   ========================================================= */

.create-post-container label {
    display: block;
    margin-top: 16px;
    margin-bottom: 7px;
    color: #166534;
    font-size: 15px;
    font-weight: 700;
}
/* =========================================================
   8. FORM FIELDS
   ========================================================= */

.create-post-container input[type="text"],
.create-post-container input[type="number"],
.create-post-container input[type="email"],
.create-post-container input[type="file"],
.create-post-container select,
.create-post-container textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 12px 14px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    background-color: #ffffff;
    color: #1F2937;
    font-size: 15px;
    outline: none;
}
/* =========================================================
   9. CREATE PAGE
   ========================================================= */
.create-page {
    width: 100%;
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 30px 40px;
    box-sizing: border-box;
}
/* =========================================================
   10. CREATE POST HEADING
   ========================================================= */

.create-post-heading {
    text-align: center;
    margin-bottom: 30px;
}

.create-post-heading h1 {
    margin: 0 0 8px;
    color: #166534;
    font-size: 28px;
    font-weight: 700;
}

.create-post-heading p {
    margin: 0;
    color: #666666;
    font-size: 15px;
    line-height: 1.6;
}

.create-icon {
    width: 42px;
    height: 42px;
    margin: 0 auto 12px;
    background-color: #C9A227;
    color: #1F2937;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 25px;
    font-weight: 700;
}
/* =========================================================
   11. FORM SECTIONS
   ========================================================= */
.form-section {
    margin-bottom: 30px;
    padding: 30px;
    box-sizing: border-box;
    background-color: #faf9f5;
    border: 1px solid #ddd6c8;
    border-radius: 14px;
}

.form-section h3 {
    margin: 0 0 18px;
    color: #166534;
    font-size: 18px;
    font-weight: 700;
}
/* =========================================================
   12. FORM ROWS
   ========================================================= */

.form-row {
    display: flex;
    gap: 15px;
}

.form-group {
    flex: 1;
}

.form-group label {
    display: block;
    margin-top: 0;
}
/* =========================================================
   13. UPLOAD BOX
   ========================================================= */

.upload-box {
    padding: 20px;
    background-color: #ffffff;
    border: 2px dashed #C9A227;
    border-radius: 12px;
    text-align: center;
}

.upload-box input[type="file"] {
    border: none;
    padding: 10px;
}

.upload-box p {
    margin: 10px 0 0;
    color: #666666;
    font-size: 14px;
}
/* =========================================================
   14. PUBLISH BUTTON
   ========================================================= */

.publish-button {
    width: 100%;
    padding: 14px 20px;
    margin-top: 10px;
    border: none;
    border-radius: 10px;
    background-color: #166534;
    color: #ffffff;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s ease;
}

.publish-button:hover {
    background-color: #14532d;
    transform: translateY(-1px);
}
/* =========================================================
   15. BACK TO HOME
   ========================================================= */

.back-home {
    display: block;
    width: fit-content;
    margin: 20px auto 0;
    color: #166534;
    text-decoration: none;
    font-size: 15px;
    font-weight: 600;
}

.back-home:hover {
    color: #C9A227;
    text-decoration: underline;
}
/* =========================================================
   16. HELPER TEXT
   ========================================================= */

.form-section small {
    display: block;
    margin-top: 8px;
    color: #6b7280;
    font-size: 13px;
    line-height: 1.5;
}
/* =========================================================
   17. MOBILE RESPONSIVE
   ========================================================= */

@media (max-width: 700px) {

    .civic-header {
        padding: 15px;
        flex-direction: column;
        gap: 12px;
    }

    .civic-nav {
        justify-content: center;
    }

    .create-page {
        padding: 0 10px 30px;
    }

    .create-post-container {
        padding: 20px 15px;
        margin: 20px auto;
    }

    .form-row {
        flex-direction: column;
        gap: 0;
    }

    .create-post-heading h1 {
        font-size: 24px;
    }
}
/* =========================================================
   FINAL FIX - MAKE CREATE POST CARD FULL WIDTH
   ========================================================= */

main.create-page > .create-post-container {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 auto !important;
    padding: 45px !important;
    box-sizing: border-box !important;
}

/* Make every form section use the full card width */
main.create-page .form-section {
    width: 100% !important;
    box-sizing: border-box !important;
}

/* Make input fields properly fill the sections */
main.create-page .create-post-container input,
main.create-page .create-post-container select,
main.create-page .create-post-container textarea {
    width: 100% !important;
    box-sizing: border-box !important;
}

/* Larger description area */
main.create-page .create-post-container textarea {
    min-height: 160px !important;
}

/* Larger upload area */
main.create-page .upload-box {
    width: 100% !important;
    box-sizing: border-box !important;
    padding: 35px !important;
}

/* Larger publish button */
main.create-page .publish-button {
    width: 100% !important;
    padding: 18px !important;
}
/* =========================================================
   FIX CREATE POST FORM WIDTH
   ========================================================= */

main.create-page > .create-post-container > form {
    width: 100% !important;
    max-width: none !important;
    min-width: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
}

/* Make all sections inside the form wide */
main.create-page > .create-post-container > form .form-section {
    width: 100% !important;
    max-width: none !important;
    box-sizing: border-box !important;
}

/* Full-width fields */
main.create-page > .create-post-container > form input,
main.create-page > .create-post-container > form select,
main.create-page > .create-post-container > form textarea {
    box-sizing: border-box !important;
    max-width: 100% !important;
}

/* Keep the two location fields side-by-side */
main.create-page > .create-post-container > form .form-row {
    display: flex !important;
    width: 100% !important;
    gap: 25px !important;
}

main.create-page > .create-post-container > form .form-group {
    flex: 1 !important;
    min-width: 0 !important;
}

/* Bigger upload box */
main.create-page > .create-post-container > form .upload-box {
    width: 100% !important;
    box-sizing: border-box !important;
}

/* Full-width publish button */
main.create-page > .create-post-container > form .publish-button {
    width: 100% !important;
}
</style>
<!-- =========================
     CREATE POST
     ========================= -->

<main class="create-page">


    <div class="create-post-container">


        <div class="create-post-heading">

            <div class="create-icon">
                +
            </div>

            <h1>
                Create Civic Post
            </h1>

            <p>
                Share a civic issue with your community
            </p>

        </div>


        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- LOCATION -->

            <div class="form-section">

                <h3>
                    📍 Location
                </h3>


                <label>
                    Constituency Name
                </label>

                <input
                    type="text"
                    name="constituency_name"
                    placeholder="Enter constituency name"
                    required
                >


                <div class="form-row">

                    <div class="form-group">

                        <label>
                            Ward Number
                        </label>

                        <input
                            type="text"
                            name="ward_number"
                            placeholder="Enter ward number"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Area Name
                        </label>

                        <input
                            type="text"
                            name="area_name"
                            placeholder="Enter area name"
                            required
                        >

                    </div>

                </div>

            </div>


            <!-- DEPARTMENT -->

            <div class="form-section">

                <h3>
                    🏢 Department
                </h3>

                <label>
                    Select the department responsible for this issue
                </label>


                <select
                    name="department"
                    required
                >

                    <option value="">
                        Select Department
                    </option>


                    <?php

                    $department_sql =
                        "SELECT department_name
                         FROM departments";

                    $department_result =
                        mysqli_query(
                            $conn,
                            $department_sql
                        );


                    while (
                        $department =
                        mysqli_fetch_assoc(
                            $department_result
                        )
                    ) {

                        echo "<option value='"
                           . $department["department_name"]
                           . "'>";

                        echo $department["department_name"];

                        echo "</option>";

                    }

                    ?>

                </select>

            </div>


            <!-- ISSUE DESCRIPTION -->

            <div class="form-section">

                <h3>
                    📝 Civic Issue
                </h3>

                <label>
                    Describe the issue
                </label>

                <textarea
                    name="issue_description"
                    placeholder="Explain the civic problem clearly..."
                    required
                ></textarea>

                <small>
                    Please provide enough details to help the concerned department understand the issue.
                </small>

            </div>


            <!-- POST TYPE -->

            <div class="form-section">

                <h3>
                    📱 Post Type
                </h3>

                <label>
                    What would you like to post?
                </label>


                <select
                    name="post_type"
                    id="post_type"
                    required
                >

                    <option value="">
                        Select Post Type
                    </option>

                    <option value="photo">
                        📷 Photo Post
                    </option>

                    <option value="video">
                        🎥 Video / Reel
                    </option>

                </select>

            </div>


            <!-- MEDIA -->

            <div class="form-section">

                <h3>
                    📤 Upload Media
                </h3>

                <label>
                    Select your photo or video
                </label>


                <div class="upload-box">

                    <input
                        type="file"
                        name="post_media[]"
                        id="post_media"
                        accept="image/*,video/*"
                        multiple
                        required
                    >

                    <p id="upload_message">
                        📷 You can upload up to 10 photos.
                    </p>

                </div>

            </div>


            <!-- SUBMIT -->

            <button
                type="submit"
                class="publish-button"
            >
                📤 Publish Civic Post
            </button>


        </form>


        <a
            href="home.php"
            class="back-home"
        >
            ← Back to Home Feed
        </a>


    </div>


</main>


<script>


var postType =
    document.getElementById("post_type");

var media =
    document.getElementById("post_media");

var message =
    document.getElementById("upload_message");


/* Change upload type */

postType.addEventListener(
    "change",
    function () {


        if (this.value == "photo") {

            media.accept = "image/*";

            media.multiple = true;

            message.innerHTML =
                "📷 You can upload up to 10 photos.";

        }


        else if (this.value == "video") {

            media.accept = "video/*";

            media.multiple = false;

            message.innerHTML =
                "🎥 Upload one video. Maximum 60 seconds.";

        }

    }
);


/* Check selected media */

media.addEventListener(
    "change",
    function () {


        if (postType.value == "photo") {


            if (this.files.length > 10) {

                alert(
                    "You can upload a maximum of 10 photos."
                );

                this.value = "";

            }

        }


        else if (postType.value == "video") {


            if (this.files.length > 1) {

                alert(
                    "Please select only one video."
                );

                this.value = "";

                return;

            }


            var file = this.files[0];


            if (file) {


                var video =
                    document.createElement("video");

                video.preload = "metadata";


                video.onloadedmetadata =
                    function () {


                        window.URL.revokeObjectURL(
                            video.src
                        );


                        if (video.duration > 60) {

                            alert(
                                "Video must be 60 seconds or less."
                            );

                            media.value = "";

                        }

                    };


                video.src =
                    URL.createObjectURL(file);

            }

        }

    }

);

</script>


</body>

</html>

