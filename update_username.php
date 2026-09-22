<?php

session_start();
include "db.php";


// Check if citizen is logged in
if (!isset($_SESSION["citizen_id"])) {

    header("Location: login.php");
    exit;

}


// Get logged-in citizen ID
$citizen_id = $_SESSION["citizen_id"];


// Get new username
$new_username = trim($_GET["username"] ?? "");


// Check username is not empty
if ($new_username == "") {

    header("Location: profile.php");
    exit;

}


// Update username
$sql = "UPDATE citizens
        SET username='$new_username'
        WHERE citizen_id='$citizen_id'";


if (mysqli_query($conn, $sql)) {

    header("Location: profile.php");
    exit;

} else {

    echo "Username update failed.";

}

?>