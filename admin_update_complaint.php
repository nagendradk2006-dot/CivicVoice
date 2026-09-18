<?php

session_start();

include "db.php";


/* Check Admin Login */

if (!isset($_SESSION["admin_id"])) {

    header("Location: admin_login.php");

    exit;

}


/* Get Complaint ID */

$complaint_id = $_GET["complaint_id"];


/* Get Complaint Details */

$sql = "SELECT *
        FROM complaints
        WHERE complaint_id='$complaint_id'";

$result = mysqli_query($conn, $sql);

$complaint = mysqli_fetch_assoc($result);


/* Update Complaint */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $status = $_POST["status"];

    $resolution_remarks =
        $_POST["resolution_remarks"];


    if ($status == "Resolved") {

    $update_sql = "UPDATE complaints
                   SET status='$status',
                       resolution_remarks='$resolution_remarks',
                       resolved_at=NOW()
                   WHERE complaint_id='$complaint_id'";

} else {

    $update_sql = "UPDATE complaints
                   SET status='$status',
                       resolution_remarks='$resolution_remarks',
                       resolved_at=NULL
                   WHERE complaint_id='$complaint_id'";

}


    if (mysqli_query($conn, $update_sql)) {

        header("Location: admin_dashboard.php");

        exit;

    } else {

        echo "Update failed: "
             . mysqli_error($conn);

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
    <strong>Area:</strong>
    <?php echo $complaint["area_name"]; ?>
</p>


<p>
    <strong>Issue:</strong>
    <?php echo $complaint["issue_description"]; ?>
</p>


<hr>


<form method="POST">


    <label>
        <strong>Complaint Status:</strong>
    </label>

    <br>

    <select name="status" required>

        <option value="Submitted">
            Submitted
        </option>

        <option value="In Progress">
            In Progress
        </option>

        <option value="Resolved">
            Resolved
        </option>

    </select>


    <br><br>


    <label>
        <strong>Resolution Remarks:</strong>
    </label>

    <br>

    <textarea
        name="resolution_remarks"
        rows="5"
        cols="50"
        placeholder="Enter resolution remarks..."
    ></textarea>


    <br><br>


    <button type="submit">
        ✅ Update Complaint
    </button>


</form>


<br>


<button
    onclick="window.location.href='admin_dashboard.php'"
>
    ← Back to Dashboard
</button>


</body>

</html>