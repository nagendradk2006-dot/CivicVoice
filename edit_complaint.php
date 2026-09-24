<?php

session_start();
include "db.php";

/* CHECK CITIZEN LOGIN */
if (!isset($_SESSION["citizen_id"])) {
    header("Location: login.php");
    exit;
}

$citizen_id = $_SESSION["citizen_id"];


/* CHECK COMPLAINT ID */
if (!isset($_GET["complaint_id"])) {
    echo "<h2>Complaint ID is missing.</h2>";
    exit;
}

$complaint_id = mysqli_real_escape_string(
    $conn,
    $_GET["complaint_id"]
);


/* CHECK COMPLAINT BELONGS TO LOGGED-IN CITIZEN */

$sql = "SELECT complaints.*, departments.department_name
        FROM complaints
        INNER JOIN departments
        ON complaints.department_id = departments.department_id
        WHERE complaints.complaint_id='$complaint_id'
        AND complaints.citizen_id='$citizen_id'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) != 1) {

    echo "<h2>You are not allowed to edit this complaint.</h2>";
    exit;
}

$complaint = mysqli_fetch_assoc($result);


/* UPDATE COMPLAINT */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $constituency_name = mysqli_real_escape_string(
        $conn,
        $_POST["constituency_name"]
    );

    $ward_number = mysqli_real_escape_string(
        $conn,
        $_POST["ward_number"]
    );

    $area_name = mysqli_real_escape_string(
        $conn,
        $_POST["area_name"]
    );

    $pincode = mysqli_real_escape_string(
        $conn,
        $_POST["pincode"]
    );

    $department = mysqli_real_escape_string(
        $conn,
        $_POST["department"]
    );

    $issue_description = mysqli_real_escape_string(
        $conn,
        $_POST["issue_description"]
    );


    /* GET DEPARTMENT ID */

    $department_sql = "
        SELECT department_id
        FROM departments
        WHERE department_name='$department'
    ";

    $department_result = mysqli_query(
        $conn,
        $department_sql
    );

    if (mysqli_num_rows($department_result) != 1) {
        echo "<h2>Invalid department selected.</h2>";
        exit;
    }

    $department_data = mysqli_fetch_assoc(
        $department_result
    );

    $department_id = $department_data["department_id"];


    /* DELETE SELECTED IMAGES */

    if (isset($_POST["delete_images"])) {

        foreach ($_POST["delete_images"] as $image_id) {

            $image_id = mysqli_real_escape_string(
                $conn,
                $image_id
            );

            $image_sql = "
                SELECT image_path
                FROM complaint_images
                WHERE image_id='$image_id'
                AND complaint_id='$complaint_id'
            ";

            $image_result = mysqli_query(
                $conn,
                $image_sql
            );

            if (mysqli_num_rows($image_result) == 1) {

                $image_data = mysqli_fetch_assoc(
                    $image_result
                );

                $image_path = $image_data["image_path"];


                /* DELETE FROM DATABASE */

                $delete_image_sql = "
                    DELETE FROM complaint_images
                    WHERE image_id='$image_id'
                    AND complaint_id='$complaint_id'
                ";

                mysqli_query(
                    $conn,
                    $delete_image_sql
                );


                /* DELETE PHYSICAL FILE */

                $full_path =
                    "C:/xampp/htdocs/CivicVoice/" .
                    $image_path;

                if (file_exists($full_path)) {
                    unlink($full_path);
                }
            }
        }
    }


    /* COUNT REMAINING IMAGES */

    $image_count_sql = "
        SELECT COUNT(*) AS total_images
        FROM complaint_images
        WHERE complaint_id='$complaint_id'
    ";

    $image_count_result = mysqli_query(
        $conn,
        $image_count_sql
    );

    $image_count_data = mysqli_fetch_assoc(
        $image_count_result
    );

    $remaining_images =
        $image_count_data["total_images"];


    /* ADD NEW IMAGES */

    if (
        isset($_FILES["new_images"])
        &&
        !empty($_FILES["new_images"]["name"][0])
    ) {

        $new_image_count =
            count($_FILES["new_images"]["name"]);


        /* MAXIMUM 10 IMAGES */

        if (
            $remaining_images + $new_image_count > 10
        ) {

            echo "<h2>
                    Maximum 10 photos are allowed.
                  </h2>";

            exit;
        }


        /* CREATE IMAGES FOLDER IF NEEDED */

        if (!is_dir("Images")) {
            mkdir("Images", 0777, true);
        }


        for (
            $i = 0;
            $i < $new_image_count;
            $i++
        ) {

            if (
                $_FILES["new_images"]["error"][$i] != 0
            ) {
                continue;
            }


            $extension = strtolower(
                pathinfo(
                    $_FILES["new_images"]["name"][$i],
                    PATHINFO_EXTENSION
                )
            );


            /* ALLOWED IMAGE TYPES */

            $allowed_extensions = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];

            if (
                !in_array(
                    $extension,
                    $allowed_extensions
                )
            ) {
                continue;
            }


            $file_name =
                $complaint_id .
                "_" .
                time() .
                "_" .
                uniqid() .
                "_" .
                ($i + 1) .
                "." .
                $extension;


            $file_path =
                "Images/" .
                $file_name;


            if (
                move_uploaded_file(
                    $_FILES["new_images"]["tmp_name"][$i],
                    $file_path
                )
            ) {

                $image_sql = "
                    INSERT INTO complaint_images
                    (complaint_id, image_path)
                    VALUES
                    ('$complaint_id', '$file_path')
                ";

                mysqli_query(
                    $conn,
                    $image_sql
                );
            }
        }
    }


    /* UPDATE COMPLAINT DETAILS */

    $update_sql = "
        UPDATE complaints
        SET
            constituency_name='$constituency_name',
            ward_number='$ward_number',
            area_name='$area_name',
            pincode='$pincode',
            department_id='$department_id',
            issue_description='$issue_description'
        WHERE complaint_id='$complaint_id'
        AND citizen_id='$citizen_id'
    ";


    if (mysqli_query($conn, $update_sql)) {


        /* UPDATE MAIN COMPLAINT IMAGE */

        $main_image_sql = "
            SELECT image_path
            FROM complaint_images
            WHERE complaint_id='$complaint_id'
            ORDER BY image_id ASC
            LIMIT 1
        ";

        $main_image_result = mysqli_query(
            $conn,
            $main_image_sql
        );


        if (
            mysqli_num_rows($main_image_result) > 0
        ) {

            $main_image_data =
                mysqli_fetch_assoc(
                    $main_image_result
                );

            $main_image =
                $main_image_data["image_path"];


            $update_main_image_sql = "
                UPDATE complaints
                SET issue_image='$main_image'
                WHERE complaint_id='$complaint_id'
                AND citizen_id='$citizen_id'
            ";

            mysqli_query(
                $conn,
                $update_main_image_sql
            );

        } else {

            $clear_main_image_sql = "
                UPDATE complaints
                SET issue_image=''
                WHERE complaint_id='$complaint_id'
                AND citizen_id='$citizen_id'
            ";

            mysqli_query(
                $conn,
                $clear_main_image_sql
            );
        }


        header("Location: profile.php");
        exit;

    } else {

        echo "<h2>Complaint update failed.</h2>";
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Update Complaint - CivicVoice</title>

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
            box-shadow: 0 10px 30px rgba(0,0,0,.10);
        }

        .page-title {
            margin: 0 0 30px;
            text-align: center;
            color: #166534;
            font-size: 30px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 15px;
            font-weight: 700;
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
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            border-color: #166534;
            outline: none;
        }

        textarea {
            min-height: 140px;
            resize: vertical;
            line-height: 1.6;
        }

        .section-title {
            margin: 30px 0 18px;
            padding-bottom: 10px;
            border-bottom: 2px solid #C9A227;
            color: #166534;
            font-size: 21px;
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(3,1fr);
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
        }

        .no-photo {
            padding: 20px;
            background: #faf9f5;
            border: 1px dashed #cfc8bb;
            border-radius: 10px;
            color: #777777;
            text-align: center;
        }

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
        }

        .help-text {
            margin: 12px 0 0;
            color: #666666;
            font-size: 14px;
        }

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
        }

        .update-button {
            border: none;
            background: #166534;
            color: #ffffff;
        }

        .back-button {
            border: 1px solid #C9A227;
            background: #ffffff;
            color: #333333;
        }

        @media (max-width:700px) {

            body {
                padding: 20px 12px;
            }

            .edit-container {
                padding: 25px 18px;
            }

            .photo-grid {
                grid-template-columns: repeat(2,1fr);
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

        @media (max-width:450px) {

            .photo-grid {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="edit-container">

    <h2 class="page-title">
        ✏️ Update Complaint
    </h2>


    <form method="POST"
          enctype="multipart/form-data">


        <div class="form-group">

            <label>Complaint ID</label>

            <input
                type="text"
                value="<?php echo htmlspecialchars($complaint["complaint_id"]); ?>"
                readonly
            >

        </div>


        <div class="form-group">

            <label>Constituency Name</label>

            <input
                type="text"
                name="constituency_name"
                value="<?php echo htmlspecialchars($complaint["constituency_name"]); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>Ward Number</label>

            <input
                type="text"
                name="ward_number"
                value="<?php echo htmlspecialchars($complaint["ward_number"]); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>Area Name</label>

            <input
                type="text"
                name="area_name"
                value="<?php echo htmlspecialchars($complaint["area_name"]); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>PIN Code</label>

            <input
                type="text"
                name="pincode"
                value="<?php echo htmlspecialchars($complaint["pincode"]); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>Department</label>

            <select name="department" required>

                <option value="">
                    Select Department
                </option>

                <?php

                $department_sql =
                    "SELECT department_name
                     FROM departments
                     ORDER BY department_name ASC";

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

                    $selected = "";

                    if (
                        $department["department_name"]
                        ==
                        $complaint["department_name"]
                    ) {

                        $selected = "selected";
                    }

                    echo "<option value=\""
                         . htmlspecialchars(
                             $department["department_name"],
                             ENT_QUOTES
                         )
                         . "\" "
                         . $selected
                         . ">"
                         . htmlspecialchars(
                             $department["department_name"]
                         )
                         . "</option>";
                }

                ?>

            </select>

        </div>


        <div class="form-group">

            <label>Describe the Civic Issue</label>

            <textarea
                name="issue_description"
                required><?php echo htmlspecialchars($complaint["issue_description"]); ?></textarea>

        </div>


        <!-- EXISTING PHOTOS -->

        <h3 class="section-title">
            📷 Existing Complaint Photos
        </h3>


        <?php

        $image_sql = "
            SELECT image_id, image_path
            FROM complaint_images
            WHERE complaint_id='$complaint_id'
            ORDER BY image_id ASC
        ";

        $image_result =
            mysqli_query(
                $conn,
                $image_sql
            );


        if (mysqli_num_rows($image_result) > 0) {

        ?>

            <div class="photo-grid">

            <?php

            while (
                $image =
                mysqli_fetch_assoc(
                    $image_result
                )
            ) {

            ?>

                <div class="photo-card">

                    <img
                        src="<?php echo htmlspecialchars($image["image_path"]); ?>"
                        alt="Complaint Photo"
                    >

                    <label class="delete-label">

                        <input
                            type="checkbox"
                            name="delete_images[]"
                            value="<?php echo $image["image_id"]; ?>"
                        >

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


        <!-- ADD NEW PHOTOS -->

        <h3 class="section-title">
            ➕ Add New Complaint Photos
        </h3>


        <div class="upload-box">

            <input
                type="file"
                name="new_images[]"
                accept="image/*"
                multiple
            >

            <p class="help-text">
                You can keep existing photos, delete selected photos,
                and add new photos. Maximum total photos: 10.
            </p>

        </div>


        <div class="button-area">

            <button
                type="submit"
                class="update-button"
            >
                💾 Update Complaint
            </button>


            <a
                href="profile.php"
                class="back-button"
            >
                ← Back to Profile
            </a>

        </div>

    </form>

</div>

</body>

</html>