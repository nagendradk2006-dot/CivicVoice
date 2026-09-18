<?php
session_start();

if (!isset($_SESSION["citizen_id"])) {
    header("Location: login.php");
    exit;
}

$citizen_id = $_SESSION["citizen_id"];
?>

<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="style.css">
<title>CivicVoice Dashboard</title>
</head>

<body>

<h2>Welcome to CivicVoice</h2>

<p>Citizen ID: <?php echo $citizen_id; ?></p>

<h3>Citizen Dashboard</h3>

<button onclick="window.location.href='submit_complaint.php'">Submit Complaint</button>
<br><br>

<button onclick="window.location.href='my_complaints.php'">
    My Complaints
</button>
<br><br>

<button onclick="window.location.href='logout.php'">Logout</button>
</body>
</html>