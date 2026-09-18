<?php

session_start();
include "db.php";

/* Check whether citizen is logged in */
if (!isset($_SESSION["citizen_id"])) {
    header("Location: login.php");
    exit;
}

$citizen_id = $_SESSION["citizen_id"];

/* Get complaints of logged-in citizen */
$sql = "SELECT complaints.*, departments.department_name
        FROM complaints
        INNER JOIN departments
        ON complaints.department_id = departments.department_id
        WHERE complaints.citizen_id = '$citizen_id'
        ORDER BY complaints.created_at DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
<title>My Complaints - CivicVoice</title>
</head>

<body>

<h2>My Complaints</h2>

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
    <strong>Department:</strong>
    <?php echo $row["department_name"]; ?>
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
    <strong>Issue:</strong>
    <?php echo $row["issue_description"]; ?>
</p>

<p>
    <strong>Status:</strong>
    <?php echo $row["status"]; ?>
</p>
<p>
    <strong>Resolution Remarks:</strong>
    <?php
    if (!empty($row["resolution_remarks"])) {
        echo $row["resolution_remarks"];
    } else {
        echo "No resolution remarks yet.";
    }
    ?>
</p>

<p>
    <strong>Submitted On:</strong>
    <?php echo $row["created_at"]; ?>
</p>
<?php
if (!empty($row["resolved_at"])) {
?>

<p>
    <strong>Resolved On:</strong>
    <?php echo $row["resolved_at"]; ?>
</p>

<?php
}
?>
<?php

    }

} else {

    echo "<p>You have not submitted any complaints yet.</p>";

}

?>

<br>

<button onclick="window.location.href='dashboard.php'">
    Back to Dashboard
</button>

</body>

</html>