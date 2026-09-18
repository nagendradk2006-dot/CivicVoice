```php
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

</head>


<body>


<!-- =========================
     HEADER
     ========================= -->

<header class="civic-header">

    <div class="civic-logo">
        CivicVoice
    </div>

    <nav class="civic-nav">

        <a href="home.php">
            Home
        </a>

        <a href="profile.php">
            Profile
        </a>

        <a href="logout.php">
            Logout
        </a>

    </nav>

</header>


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
```
