<?php

session_start();
include "db.php";


/* Check citizen login */

if (!isset($_SESSION["citizen_id"])) {

    header("Location: login.php");
    exit;

}

$citizen_id = $_SESSION["citizen_id"];


/* Check post ID */

if (!isset($_GET["post_id"])) {

    echo "<h2>Post ID is missing.</h2>";
    exit;

}

$post_id = $_GET["post_id"];


/* Check whether the post belongs to the logged-in citizen */

$sql = "SELECT civic_posts.*, departments.department_name
        FROM civic_posts
        INNER JOIN departments
        ON civic_posts.department_id = departments.department_id
        WHERE civic_posts.post_id='$post_id'
        AND civic_posts.citizen_id='$citizen_id'";

$result = mysqli_query($conn, $sql);


if (mysqli_num_rows($result) != 1) {

    echo "<h2>You are not allowed to edit this post.</h2>";
    exit;

}


$post = mysqli_fetch_assoc($result);


/* UPDATE POST */

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $constituency_name = $_POST["constituency_name"];
    $ward_number = $_POST["ward_number"];
    $area_name = $_POST["area_name"];
    $department = $_POST["department"];
    $issue_description = $_POST["issue_description"];


    /* Get department ID */

    $department_sql = "SELECT department_id
                       FROM departments
                       WHERE department_name='$department'";

    $department_result = mysqli_query(
        $conn,
        $department_sql
    );

    $department_data = mysqli_fetch_assoc(
        $department_result
    );

    $department_id = $department_data["department_id"];


    /* Count existing images */

    $image_count_sql = "SELECT COUNT(*) AS total_images
                        FROM civic_post_images
                        WHERE post_id='$post_id'";

    $image_count_result = mysqli_query(
        $conn,
        $image_count_sql
    );

    $image_count_data = mysqli_fetch_assoc(
        $image_count_result
    );

    $existing_images = $image_count_data["total_images"];


    /* Delete selected images */

    if (isset($_POST["delete_images"])) {

        foreach ($_POST["delete_images"] as $image_id) {

            $image_sql = "SELECT image_path
                          FROM civic_post_images
                          WHERE image_id='$image_id'
                          AND post_id='$post_id'";

            $image_result = mysqli_query(
                $conn,
                $image_sql
            );

            if (mysqli_num_rows($image_result) == 1) {

                $image_data = mysqli_fetch_assoc(
                    $image_result
                );

                $image_path = $image_data["image_path"];


                /* Delete image from database */

                $delete_image_sql = "DELETE FROM civic_post_images
                                     WHERE image_id='$image_id'
                                     AND post_id='$post_id'";

                mysqli_query(
                    $conn,
                    $delete_image_sql
                );


                /* Delete physical image */

                $full_path = "C:/xampp/htdocs/CivicVoice/"
                             . $image_path;

                if (file_exists($full_path)) {

                    unlink($full_path);

                }

            }

        }

    }


    /* Count images after deletion */

    $image_count_sql = "SELECT COUNT(*) AS total_images
                        FROM civic_post_images
                        WHERE post_id='$post_id'";

    $image_count_result = mysqli_query(
        $conn,
        $image_count_sql
    );

    $image_count_data = mysqli_fetch_assoc(
        $image_count_result
    );

    $remaining_images = $image_count_data["total_images"];


    /* Add new images */

    if (
        isset($_FILES["new_images"])
        && !empty($_FILES["new_images"]["name"][0])
    ) {


        $new_image_count = count(
            $_FILES["new_images"]["name"]
        );


        /* Check 10 image limit */

        if (
            $remaining_images + $new_image_count > 10
        ) {

            echo "<h2>
                  Maximum 10 photos are allowed.
                  </h2>";

            exit;

        }


        for (
            $i = 0;
            $i < $new_image_count;
            $i++
        ) {


            $extension = pathinfo(
                $_FILES["new_images"]["name"][$i],
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


            if (
                move_uploaded_file(
                    $_FILES["new_images"]["tmp_name"][$i],
                    $file_path
                )
            ) {


                $image_sql = "INSERT INTO civic_post_images
                              (post_id, image_path)
                              VALUES
                              ('$post_id', '$file_path')";


                mysqli_query(
                    $conn,
                    $image_sql
                );

            }

        }

    }


    /* Update post details */

    $update_sql = "UPDATE civic_posts
                   SET constituency_name='$constituency_name',
                       ward_number='$ward_number',
                       area_name='$area_name',
                       department_id='$department_id',
                       issue_description='$issue_description'
                   WHERE post_id='$post_id'
                   AND citizen_id='$citizen_id'";


    if (mysqli_query($conn, $update_sql)) {


        /* Update main post image */

        $main_image_sql = "SELECT image_path
                           FROM civic_post_images
                           WHERE post_id='$post_id'
                           ORDER BY image_id ASC
                           LIMIT 1";

        $main_image_result = mysqli_query(
            $conn,
            $main_image_sql
        );


        if (mysqli_num_rows($main_image_result) > 0) {

            $main_image_data = mysqli_fetch_assoc(
                $main_image_result
            );

            $main_image = $main_image_data["image_path"];


            $update_main_image_sql = "UPDATE civic_posts
                                      SET post_image='$main_image'
                                      WHERE post_id='$post_id'
                                      AND citizen_id='$citizen_id'";

            mysqli_query(
                $conn,
                $update_main_image_sql
            );

        } else {

            $clear_main_image_sql = "UPDATE civic_posts
                                     SET post_image=''
                                     WHERE post_id='$post_id'
                                     AND citizen_id='$citizen_id'";

            mysqli_query(
                $conn,
                $clear_main_image_sql
            );

        }


        header("Location: profile.php");
        exit;


    } else {

        echo "<h2>Post update failed.</h2>";

    }

}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Civic Post - CivicVoice</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 40px 20px;
            background: #E8E2D5;
            font-family: Arial, sans-serif;
            color: #333333;
        }

        .edit-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            padding: 35px;
            border-radius: 18px;
            border: 1px solid #ddd6c8;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.10);
        }

        /* PAGE TITLE */

        .page-title {
            margin: 0 0 30px;
            text-align: center;
            color: #166534;
            font-size: 30px;
        }

        /* FORM */

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 15px;
            font-weight: 700;
            color: #333333;
        }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #cfc8bb;
            border-radius: 9px;
            background: #ffffff;
            color: #333333;
            font-size: 15px;
            font-family: Arial, sans-serif;
            outline: none;
            transition: 0.2s ease;
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            border-color: #166534;
            box-shadow: 0 0 0 3px rgba(22, 101, 52, 0.10);
        }

        textarea {
            min-height: 140px;
            resize: vertical;
            line-height: 1.6;
        }

        /* SECTION */

        .section-title {
            margin: 30px 0 18px;
            padding-bottom: 10px;
            border-bottom: 2px solid #C9A227;
            color: #166534;
            font-size: 21px;
        }

        /* EXISTING PHOTOS */

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-top: 15px;
        }

        .photo-card {
            background: #faf9f5;
            border: 1px solid #ddd6c8;
            border-radius: 12px;
            padding: 12px;
            text-align: center;
        }

        .photo-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            display: block;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .delete-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            color: #a33a3a;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .delete-label input {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .no-photo {
            padding: 20px;
            background: #faf9f5;
            border: 1px dashed #cfc8bb;
            border-radius: 10px;
            color: #777777;
            text-align: center;
        }

        /* ADD PHOTOS */

        .upload-box {
            background: #faf9f5;
            border: 1px dashed #C9A227;
            border-radius: 12px;
            padding: 20px;
        }

        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #d5cec0;
            border-radius: 8px;
            background: #ffffff;
            font-size: 14px;
        }

        .help-text {
            margin: 12px 0 0;
            color: #666666;
            font-size: 14px;
            line-height: 1.5;
        }

        /* BUTTONS */

        .button-area {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }

        .update-button,
        .back-button {
            display: inline-block;
            padding: 13px 25px;
            border-radius: 9px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .update-button {
            border: none;
            background: #166534;
            color: #ffffff;
        }

        .update-button:hover {
            background: #14532d;
            transform: translateY(-1px);
        }

        .back-button {
            border: 1px solid #C9A227;
            background: #ffffff;
            color: #333333;
        }

        .back-button:hover {
            background: #f8f3e5;
        }

        /* MOBILE */

        @media (max-width: 700px) {

            body {
                padding: 20px 12px;
            }

            .edit-container {
                padding: 25px 18px;
                border-radius: 14px;
            }

            .page-title {
                font-size: 25px;
            }

            .photo-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .photo-card img {
                height: 150px;
            }

            .button-area {
                flex-direction: column;
            }

            .update-button,
            .back-button {
                width: 100%;
                text-align: center;
            }

        }

        @media (max-width: 450px) {

            .photo-grid {
                grid-template-columns: 1fr;
            }

            .photo-card img {
                height: 200px;
            }

        }

    </style>

</head>


<body>

<div class="edit-container">

    <h2 class="page-title">
        ✏️ Edit Civic Post
    </h2>


    <form method="POST"
          enctype="multipart/form-data">


        <div class="form-group">

            <label>Constituency Name:</label>

            <input type="text"
                   name="constituency_name"
                   value="<?php echo $post["constituency_name"]; ?>"
                   required>

        </div>


        <div class="form-group">

            <label>Ward Number:</label>

            <input type="text"
                   name="ward_number"
                   value="<?php echo $post["ward_number"]; ?>"
                   required>

        </div>


        <div class="form-group">

            <label>Area Name:</label>

            <input type="text"
                   name="area_name"
                   value="<?php echo $post["area_name"]; ?>"
                   required>

        </div>


        <div class="form-group">

            <label>Department:</label>

            <select name="department" required>

                <option value="">
                    Select Department
                </option>


                <?php

                $department_sql = "SELECT department_name
                                   FROM departments";

                $department_result = mysqli_query(
                    $conn,
                    $department_sql
                );


                while (
                    $department = mysqli_fetch_assoc(
                        $department_result
                    )
                ) {

                    $selected = "";

                    if (
                        $department["department_name"]
                        == $post["department_name"]
                    ) {

                        $selected = "selected";

                    }


                    echo "<option value='"
                         . $department["department_name"]
                         . "' "
                         . $selected
                         . ">"
                         . $department["department_name"]
                         . "</option>";

                }

                ?>

            </select>

        </div>


        <div class="form-group">

            <label>
                Describe the Civic Issue:
            </label>

            <textarea name="issue_description"
                      required><?php echo $post["issue_description"]; ?></textarea>

        </div>


        <h3 class="section-title">
            📷 Existing Photos
        </h3>


        <?php

        $image_sql = "SELECT image_id, image_path
                      FROM civic_post_images
                      WHERE post_id='$post_id'
                      ORDER BY image_id ASC";

        $image_result = mysqli_query(
            $conn,
            $image_sql
        );


        if (mysqli_num_rows($image_result) > 0) {

        ?>

        <div class="photo-grid">

        <?php

            while (
                $image = mysqli_fetch_assoc(
                    $image_result
                )
            ) {

        ?>

            <div class="photo-card">

                <img src="<?php echo $image["image_path"]; ?>"
                     alt="Post Photo">

                <label class="delete-label">

                    <input type="checkbox"
                           name="delete_images[]"
                           value="<?php echo $image["image_id"]; ?>">

                    🗑️ Delete this photo

                </label>

            </div>

        <?php

            }

        ?>

        </div>

        <?php

        } else {

            echo "
            <div class='no-photo'>
                No photos available.
            </div>
            ";

        }

        ?>


        <h3 class="section-title">
            ➕ Add New Photos
        </h3>


        <div class="upload-box">

            <input type="file"
                   name="new_images[]"
                   accept="image/*"
                   multiple>

            <p class="help-text">
                You can keep or delete existing photos and add new photos.
                Maximum total photos: 10.
            </p>

        </div>


        <div class="button-area">

            <button type="submit"
                    class="update-button">

                💾 Update Post

            </button>


            <a href="profile.php"
               class="back-button">

                ← Back to Profile

            </a>

        </div>


    </form>

</div>

</body>

</html>