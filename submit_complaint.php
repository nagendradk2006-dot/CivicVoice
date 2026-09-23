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
<br>

<button type="button" id="aiSuggestBtn">
    🤖 AI Suggest Department
</button>

<div id="aiLoading" style="display:none;">
    🤖 AI is analyzing your complaint...
</div>

<div id="aiResult" style="display:none;">

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

    <button type="button" id="useAiDepartment">
        ✓ Use This Department
    </button>

</div>

<div id="aiError" style="display:none;color:red;"></div>

<br><br>

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

<script>

document.getElementById("aiSuggestBtn").addEventListener("click", function () {

    const issue =
        document.querySelector('textarea[name="issue_description"]').value.trim();

    if (issue === "") {
        alert("Please describe your complaint first.");
        return;
    }

    const formData = new FormData();

    formData.append("issue_description", issue);

    document.getElementById("aiLoading").style.display = "block";
    document.getElementById("aiResult").style.display = "none";
    document.getElementById("aiError").style.display = "none";

    this.disabled = true;

    fetch("ai_recommend.php", {
        method: "POST",
        body: formData
    })

    .then(response => response.json())

    .then(data => {

        document.getElementById("aiLoading").style.display = "none";

        document.getElementById("aiSuggestBtn").disabled = false;

        if (!data.success) {

            document.getElementById("aiError").innerText =
                data.message || "AI recommendation failed.";

            document.getElementById("aiError").style.display = "block";

            return;
        }

        document.getElementById("aiDepartment").innerText =
            data.department_name;

        document.getElementById("aiCategory").innerText =
            data.category;

        document.getElementById("aiPriority").innerText =
            data.priority;

        document.getElementById("aiReason").innerText =
            data.reason;

        document.getElementById("aiResult").style.display = "block";


        document.getElementById("useAiDepartment").onclick = function () {

            const departmentSelect =
                document.querySelector('select[name="department"]');

            departmentSelect.value = data.department_name;

            alert(
                "AI recommended department selected. You can still change it before submitting."
            );

        };

    })

    .catch(error => {

        document.getElementById("aiLoading").style.display = "none";

        document.getElementById("aiSuggestBtn").disabled = false;

        document.getElementById("aiError").innerText =
            "Unable to connect to AI.";

        document.getElementById("aiError").style.display = "block";

        console.error(error);

    });

});

</script>
</body>

</html>