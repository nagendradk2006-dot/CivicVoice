<?php

session_start();
include "db.php";


/* Check department login */

if (!isset($_SESSION["department_user_id"])) {

    header("Location: department_login.php");
    exit;

}


$department_user_id = $_SESSION["department_user_id"];
$department_name = $_SESSION["department_name"];
$department_id = $_SESSION["department_id"];


/* Get complaints for this department */

$sql = "SELECT complaints.*, departments.department_name
        FROM complaints
        INNER JOIN departments
        ON complaints.department_id = departments.department_id
        WHERE complaints.department_id='$department_id'
        ORDER BY complaints.created_at DESC";


$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html>

<head>
<link rel="stylesheet" href="style.css">
<title>Department Dashboard - CivicVoice</title>

</head>


<body>


<h2>🏢 CivicVoice Department Dashboard</h2>


<h3>
Department:
<?php echo $department_name; ?>
</h3>


<hr>


<h2>📋 Department Complaints</h2>


<?php

if (mysqli_num_rows($result) > 0) {


    while ($row = mysqli_fetch_assoc($result)) {

?>


<hr>


<p>

<strong>Complaint ID:</strong>

<?php echo $row["complaint_id"]; ?>

</p>


<p>

<strong>Citizen ID:</strong>

<?php echo $row["citizen_id"]; ?>

</p>


<p>

<strong>Constituency:</strong>

<?php echo $row["constituency_name"]; ?>

</p>


<p>

<strong>Ward Number:</strong>

<?php echo $row["ward_number"]; ?>

</p>


<p>

<strong>Area:</strong>

<?php echo $row["area_name"]; ?>

</p>


<p>

<strong>PIN Code:</strong>

<?php echo $row["pincode"]; ?>

</p>


<p>

<strong>Department:</strong>

<?php echo $row["department_name"]; ?>

</p>


<p>

<strong>Issue:</strong>

<?php echo $row["issue_description"]; ?>

</p>


<p>

<strong>Status:</strong>

<?php echo $row["status"]; ?>

</p>


<p>

<strong>Submitted On:</strong>

<?php echo $row["created_at"]; ?>

</p>


<?php

/* Get complaint images */

$image_sql = "SELECT image_path
              FROM complaint_images
              WHERE complaint_id='"
              . $row["complaint_id"]
              . "'
              ORDER BY image_id ASC";


$image_result = mysqli_query(
    $conn,
    $image_sql
);


if (
    $image_result
    && mysqli_num_rows($image_result) > 0
) {

?>


<h3>📷 Complaint Images</h3>


<?php

    while (
        $image = mysqli_fetch_assoc(
            $image_result
        )
    ) {

?>


<img src="<?php echo $image["image_path"]; ?>"
     width="250"
     style="margin:5px;">


<?php

    }

} else {


    /* Fallback to main complaint image */

    if (!empty($row["issue_image"])) {

?>


<h3>📷 Complaint Image</h3>


<img src="<?php echo $row["issue_image"]; ?>"
     width="250">


<?php

    }

}

?>


<br><br>


<a href="department_update_complaint.php?complaint_id=<?php echo $row["complaint_id"]; ?>">

    📝 Update Complaint

</a>


<br><br>


<?php

    }

} else {

    echo "<p>No complaints have been assigned to this department.</p>";

}

?>


<hr>


<br>


<button onclick="window.location.href='department_logout.php'">

Logout

</button>


</body>

</html>