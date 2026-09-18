<?php

session_start();
include "db.php";


/* Check department login */

if (!isset($_SESSION["department_user_id"])) {

    header("Location: department_login.php");
    exit;

}


$department_id = $_SESSION["department_id"];


/* Check complaint ID */

if (!isset($_GET["complaint_id"])) {

    echo "<h2>Complaint ID is missing.</h2>";
    exit;

}


$complaint_id = $_GET["complaint_id"];


/* Get complaint belonging to this department */

$sql = "SELECT complaints.*, departments.department_name
        FROM complaints
        INNER JOIN departments
        ON complaints.department_id = departments.department_id
        WHERE complaints.complaint_id='$complaint_id'
        AND complaints.department_id='$department_id'";


$result = mysqli_query($conn, $sql);


if (mysqli_num_rows($result) != 1) {

    echo "<h2>You are not allowed to update this complaint.</h2>";
    exit;

}


$complaint = mysqli_fetch_assoc($result);


/* Update complaint */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $status = $_POST["status"];
    $resolution_remarks = $_POST["resolution_remarks"];


    if ($status == "Resolved") {

        $update_sql = "UPDATE complaints
                       SET status='$status',
                           resolution_remarks='$resolution_remarks',
                           resolved_at=NOW()
                       WHERE complaint_id='$complaint_id'
                       AND department_id='$department_id'";

    } else {

        $update_sql = "UPDATE complaints
                       SET status='$status',
                           resolution_remarks='$resolution_remarks',
                           resolved_at=NULL
                       WHERE complaint_id='$complaint_id'
                       AND department_id='$department_id'";

    }


    if (mysqli_query($conn, $update_sql)) {

        header("Location: department_dashboard.php");
        exit;

    } else {

        echo "<h2>Update failed.</h2>";

    }

}

?>


<!DOCTYPE html>

<html>

<head>

<title>Update Complaint - CivicVoice</title>

</head>


<body>


<h2>📝 Update Complaint</h2>


<p>

<strong>Complaint ID:</strong>

<?php echo $complaint["complaint_id"]; ?>

</p>


<p>

<strong>Department:</strong>

<?php echo $complaint["department_name"]; ?>

</p>


<p>

<strong>Area:</strong>

<?php echo $complaint["area_name"]; ?>

</p>


<p>

<strong>Issue:</strong>

<?php echo $complaint["issue_description"]; ?>

</p>


<p>

<strong>Current Status:</strong>

<?php echo $complaint["status"]; ?>

</p>


<hr>


<form method="POST">


<label>

<strong>Complaint Status:</strong>

</label>

<br>


<select name="status" required>


<option value="Submitted"
<?php
if ($complaint["status"] == "Submitted") {
    echo "selected";
}
?>
>

Submitted

</option>


<option value="In Progress"
<?php
if ($complaint["status"] == "In Progress") {
    echo "selected";
}
?>
>

In Progress

</option>


<option value="Resolved"
<?php
if ($complaint["status"] == "Resolved") {
    echo "selected";
}
?>
>

Resolved

</option>


</select>


<br><br>


<label>

<strong>Resolution Remarks:</strong>

</label>

<br>


<textarea name="resolution_remarks"
          rows="5"
          cols="50"
          placeholder="Enter resolution remarks..."><?php

echo $complaint["resolution_remarks"];

?></textarea>


<br><br>


<button type="submit">

✅ Update Complaint

</button>


</form>


<br>


<button onclick="window.location.href='department_dashboard.php'">

← Back to Department Dashboard

</button>


</body>

</html>