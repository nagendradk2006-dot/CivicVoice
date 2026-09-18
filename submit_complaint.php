<?php

session_start();
include "db.php";

/* Check whether citizen is logged in */
if (!isset($_SESSION["citizen_id"])) {
    header("Location: login.php");
    exit;
}


/* Process complaint after form submission */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $citizen_id = $_SESSION["citizen_id"];

    $constituency_name = $_POST['constituency_name'];
    $Ward_number = $_POST['Ward_number'];
    $area_name = $_POST['area_name'];
    $pincode = $_POST['pincode'];
    $Describe_Issue = $_POST['issue_description'];
    $department = $_POST['department'];


    /* Find department ID */

    $department_query = "SELECT department_id
                         FROM departments
                         WHERE department_name = '$department'";

    $result = mysqli_query($conn, $department_query);

    $department_data = mysqli_fetch_assoc($result);

    $department_id = $department_data['department_id'];


    /* Generate complaint ID */

    $complaint_id = "C" . rand(1000, 9999);


    /* Get uploaded images */

    $images = $_FILES['issue_images'];

    $image_count = count($images['name']);


    /* Check maximum 10 images */

    if ($image_count > 10) {

        echo "<h2>You can upload a maximum of 10 images.</h2>";
        exit;

    }


    /* Check at least one image */

    if ($image_count < 1) {

        echo "<h2>Please upload at least one image.</h2>";
        exit;

    }


    /* First image path */

    $extension = pathinfo(
        $images['name'][0],
        PATHINFO_EXTENSION
    );

    $image_name = $complaint_id . "_1." . $extension;

    $image_path = "Images/" . $image_name;


    /* Insert complaint */

    $sql = "INSERT INTO complaints
    (complaint_id, citizen_id, constituency_name, ward_number, area_name, pincode, issue_description, department_id, issue_image, status)
    VALUES
    ('$complaint_id', '$citizen_id', '$constituency_name', '$Ward_number', '$area_name', '$pincode', '$Describe_Issue', '$department_id', '$image_path', 'Submitted')";


    /* Execute complaint query */

    if (!mysqli_query($conn, $sql)) {

        echo "Complaint submission failed: " . mysqli_error($conn);
        exit;

    }


    /* Upload and save all images */

    for ($i = 0; $i < $image_count; $i++) {

        $extension = pathinfo(
            $images['name'][$i],
            PATHINFO_EXTENSION
        );


        $image_name = $complaint_id . "_" . ($i + 1) . "." . $extension;


        $image_path = "Images/" . $image_name;


        /* Move image to Images folder */

        move_uploaded_file(
            $images['tmp_name'][$i],
            $image_path
        );


        /* Save image path in database */

        $image_sql = "INSERT INTO complaint_images
                      (complaint_id, image_path)
                      VALUES
                      ('$complaint_id', '$image_path')";


        mysqli_query($conn, $image_sql);

    }


    /* Success message */

    echo "<h2>Complaint Submitted Successfully!</h2>";

    echo "<p>Complaint ID: " . $complaint_id . "</p>";

    echo "<p>Images Uploaded: " . $image_count . "</p>";

    echo "<p>Status: Submitted</p>";

    echo "<br>";

    echo "<a href='home.php'>Go to Home</a>";

    exit;

}

?>


<!DOCTYPE html>
<html>

<head>

    <title>Submit Complaint - CivicVoice</title>

</head>

<body>


<h2>Submit a Complaint</h2>


<form method="POST" enctype="multipart/form-data">


    <label>Constituency Name:</label><br>

    <input
        type="text"
        name="constituency_name"
        required
    >

    <br><br>


    <label>Ward Number:</label><br>

    <input
        type="text"
        name="Ward_number"
        required
    >

    <br><br>


    <label>Area Name:</label><br>

    <input
        type="text"
        name="area_name"
        required
    >

    <br><br>


    <label>PIN Code:</label><br>

    <input
        type="text"
        name="pincode"
        required
    >

    <br><br>


    <label>Department:</label><br>


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
                 $department_row['department_name'] .
                 "'>";

            echo $department_row['department_name'];

            echo "</option>";

        }

        ?>

    </select>


    <br><br>


    <label>Describe Issue:</label><br>


    <textarea
        name="issue_description"
        rows="5"
        required
    ></textarea>


    <br><br>


    <label>Upload Images:</label><br>


    <input
        type="file"
        name="issue_images[]"
        accept="image/*"
        multiple
        required
    >


    <p>
        📷 You can upload a maximum of 10 images.
    </p>


    <br>


    <button type="submit">
        Submit Complaint
    </button>


</form>


</body>

</html>