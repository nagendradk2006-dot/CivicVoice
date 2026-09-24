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


/* ================================================= */
/* PROCESS COMPLAINT */
/* ================================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $citizen_id = $_SESSION["citizen_id"];

    $constituency_name = $_POST["constituency_name"];
    $Ward_number = $_POST["Ward_number"];
    $area_name = $_POST["area_name"];
    $pincode = $_POST["pincode"];
    $Describe_Issue = $_POST["issue_description"];
    $department = $_POST["department"];


    /* ================================================= */
    /* FIND DEPARTMENT ID */
    /* ================================================= */

    $department_query = "
        SELECT department_id
        FROM departments
        WHERE department_name = '$department'
    ";

    $result = mysqli_query($conn, $department_query);

    if (!$result) {

        die("Department query failed: " . mysqli_error($conn));

    }

    $department_data = mysqli_fetch_assoc($result);

    if (!$department_data) {

        die("Selected department not found.");

    }

    $department_id = $department_data["department_id"];


    /* ================================================= */
    /* GENERATE COMPLAINT ID */
    /* ================================================= */

    do {

        $complaint_id = "C" . rand(1000, 9999);

        $check_id = mysqli_query(
            $conn,
            "SELECT complaint_id
             FROM complaints
             WHERE complaint_id = '$complaint_id'"
        );

    } while (mysqli_num_rows($check_id) > 0);


    /* ================================================= */
    /* CHECK IMAGES */
    /* ================================================= */

    if (
        !isset($_FILES["issue_images"]) ||
        empty($_FILES["issue_images"]["name"][0])
    ) {

        echo "<h2>Please upload at least one image.</h2>";
        exit;

    }


    $images = $_FILES["issue_images"];

    $image_count = count($images["name"]);


    if ($image_count > 10) {

        echo "<h2>You can upload a maximum of 10 images.</h2>";
        exit;

    }


    if ($image_count < 1) {

        echo "<h2>Please upload at least one image.</h2>";
        exit;

    }


    /* ================================================= */
    /* IMAGE UPLOAD FOLDER */
    /* ================================================= */

    $upload_dir =
        __DIR__ .
        DIRECTORY_SEPARATOR .
        "Images" .
        DIRECTORY_SEPARATOR;


    if (!is_dir($upload_dir)) {

        mkdir($upload_dir, 0777, true);

    }


    /* ================================================= */
    /* INSERT COMPLAINT FIRST */
    /* ================================================= */

    $sql = "
        INSERT INTO complaints
        (
            complaint_id,
            citizen_id,
            constituency_name,
            ward_number,
            area_name,
            pincode,
            issue_description,
            department_id,
            issue_image,
            status
        )
        VALUES
        (
            '$complaint_id',
            '$citizen_id',
            '$constituency_name',
            '$Ward_number',
            '$area_name',
            '$pincode',
            '$Describe_Issue',
            '$department_id',
            '',
            'Submitted'
        )
    ";


    if (!mysqli_query($conn, $sql)) {

        die(
            "DATABASE ERROR: " .
            mysqli_error($conn)
        );

    }


    /* ================================================= */
    /* UPLOAD ALL IMAGES */
    /* ================================================= */

    $uploaded_paths = [];


    for ($i = 0; $i < $image_count; $i++) {


        /* Check upload error */

        if ($images["error"][$i] != UPLOAD_ERR_OK) {

            echo "Failed to upload image number " .
                 ($i + 1);

            exit;

        }


        /* Original filename */

        $original_name =
            basename($images["name"][$i]);


        /* Safe filename */

        $safe_name =
            preg_replace(
                "/[^A-Za-z0-9._-]/",
                "_",
                $original_name
            );


        /* Generate ONE unique stored filename */

        $filename =
            $complaint_id .
            "_" .
            ($i + 1) .
            "_" .
            time() .
            "_" .
            $safe_name;


        /* Physical file location */

        $destination =
            $upload_dir .
            $filename;


        /* Database path */

        $image_path =
            "Images/" .
            $filename;


        /* Move uploaded file */

        if (
            !move_uploaded_file(
                $images["tmp_name"][$i],
                $destination
            )
        ) {

            echo "Failed to upload image.";

            exit;

        }


        /* ================================================= */
        /* SAVE IMAGE PATH EXACTLY ONCE */
        /* ================================================= */

        $image_sql = "
            INSERT INTO complaint_images
            (
                complaint_id,
                image_path
            )
            VALUES
            (
                '$complaint_id',
                '$image_path'
            )
        ";


        if (!mysqli_query($conn, $image_sql)) {

            echo
                "Failed to save image information: " .
                mysqli_error($conn);

            exit;

        }


        /* Store path for later */

        $uploaded_paths[] = $image_path;


    }


    /* ================================================= */
    /* SAVE FIRST IMAGE PATH IN COMPLAINT */
    /* ================================================= */

    if (!empty($uploaded_paths[0])) {

        $first_image =
            $uploaded_paths[0];


        $update_sql = "
            UPDATE complaints
            SET issue_image = '$first_image'
            WHERE complaint_id = '$complaint_id'
        ";


        if (!mysqli_query($conn, $update_sql)) {

            echo
                "Failed to save complaint image path: " .
                mysqli_error($conn);

            exit;

        }

    }


    /* ================================================= */
    /* SUCCESS */
    /* ================================================= */

    echo "

    <div style='
        max-width:600px;
        margin:60px auto;
        padding:35px;
        background:white;
        border-radius:15px;
        text-align:center;
        box-shadow:0 8px 25px rgba(0,0,0,0.12);
        font-family:Arial;
    '>

        <h2 style='color:#166534;'>
            ✅ Complaint Submitted Successfully!
        </h2>

        <p>
            <strong>Complaint ID:</strong>
            $complaint_id
        </p>

        <p>
            <strong>Images Uploaded:</strong>
            $image_count
        </p>

        <p>
            <strong>Status:</strong>
            Submitted
        </p>

        <br>

        <a
            href='home.php'
            style='
                display:inline-block;
                padding:12px 25px;
                background:#166534;
                color:white;
                text-decoration:none;
                border-radius:8px;
            '
        >
            Go to Home
        </a>

    </div>

    ";

    exit;

}

?>


<!DOCTYPE html>
<html>

<head>

    <title>Submit Complaint - CivicVoice</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 15px;
            font-family: Arial, sans-serif;
            background: #f4f1ea;
            color: #222;
        }

        .complaint-container {
            width: 100%;
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .complaint-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .complaint-header h2 {
            margin: 0;
            color: #166534;
            font-size: 30px;
        }

        .complaint-header p {
            margin-top: 8px;
            color: #666;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            background: #fff;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #166534;
            box-shadow: 0 0 0 2px rgba(22,101,52,0.10);
        }

        textarea {
            resize: vertical;
            min-height: 130px;
        }

        

        .upload-box {
            padding: 20px;
            border: 2px dashed #bbb;
            border-radius: 10px;
            background: #fafafa;
            text-align: center;
        }

        .upload-box input {
            border: none;
            padding: 5px;
        }

        .upload-info {
            margin: 8px 0 0;
            color: #777;
            font-size: 13px;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            margin-top: 10px;
            border: none;
            border-radius: 9px;
            background: #166534;
            color: white;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
        }

        .submit-btn:hover {
            background: #0f4d26;
        }

        @media (max-width: 600px) {

            body {
                padding: 15px 10px;
            }

            .complaint-container {
                padding: 22px 18px;
                border-radius: 12px;
            }

            .complaint-header h2 {
                font-size: 24px;
            }

            input,
            select,
            textarea {
                font-size: 14px;
            }

        }
.back-dashboard {
    display: inline-block;
    margin-bottom: 20px;
    padding: 10px 18px;
    background: #333;
    color: #ffffff;
    text-decoration: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: bold;
    transition: 0.2s ease;
}

.back-dashboard:hover {
    background: #111;
}
    </style>

</head>
<body>

<div class="complaint-container">

    <a href="home.php" class="back-dashboard">
        ← Back to Dashboard
    </a>

    <div class="complaint-header">

        <h2>Submit a Complaint</h2>
        <p>
            Report a civic issue and help improve your community.
        </p>

    </div>


    <form method="POST" enctype="multipart/form-data">


        <div class="form-group">

            <label>Constituency Name</label>

            <input
                type="text"
                name="constituency_name"
                required
            >

        </div>


        <div class="form-group">

            <label>Ward Number</label>

            <input
                type="text"
                name="Ward_number"
                required
            >

        </div>


        <div class="form-group">

            <label>Area Name</label>

            <input
                type="text"
                name="area_name"
                required
            >

        </div>


        <div class="form-group">

            <label>PIN Code</label>

            <input
                type="text"
                name="pincode"
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

                $department_list =
                    "SELECT department_name FROM departments";

                $department_result =
                    mysqli_query($conn, $department_list);

                while (
                    $department_row =
                    mysqli_fetch_assoc($department_result)
                ) {

                    echo "<option value='" .
                         htmlspecialchars(
                             $department_row['department_name']
                         ) .
                         "'>";

                    echo htmlspecialchars(
                        $department_row['department_name']
                    );

                    echo "</option>";

                }

                ?>

            </select>

        </div>


        <div class="form-group">

            <label>Describe Issue</label>

            <textarea
                name="issue_description"
                rows="5"
                required
            ></textarea>


            

                <div
                    id="aiResult"
                    style="display:none;"
                >

                    <h3>🤖 AI Recommendation</h3>

                    <p>
                        <strong>Department:</strong>
                        <span id="aiDepartment"></span>
                    </p>

                    <p>
                        <strong>Category:</strong>
                        <span id="aiCategory"></span>
                    </p>

                    <p>
                        <strong>Priority:</strong>
                        <span id="aiPriority"></span>
                    </p>

                    <p>
                        <strong>Reason:</strong>
                        <span id="aiReason"></span>
                    </p>


                    <button
                        type="button"
                        id="useAiDepartment"
                    >
                        ✓ Use This Department
                    </button>

                </div>


                <div
                    id="aiError"
                    style="display:none;"
                ></div>

            </div>

        </div>


        <div class="form-group">

            <label>Upload Images</label>

            <div class="upload-box">

                <input
                    type="file"
                    name="issue_images[]"
                    accept="image/*"
                    multiple
                    required
                >

                <p class="upload-info">
                    📷 You can upload a maximum of 10 images.
                </p>

            </div>

        </div>


        <button
            type="submit"
            class="submit-btn"
        >
            Submit Complaint
        </button>


    </form>

</div>


<script>

document
.getElementById("

</script>

</body>
</html>
