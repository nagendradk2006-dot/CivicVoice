<?php

session_start();
include "db.php";

/* CHECK CITIZEN LOGIN */
if (!isset($_SESSION["citizen_id"])) {
    header("Location: login.php");
    exit;
}

/* CHECK COMPLAINT ID */
if (!isset($_GET["complaint_id"]) || empty($_GET["complaint_id"])) {
    header("Location: profile.php");
    exit;
}

$citizen_id = $_SESSION["citizen_id"];
$complaint_id = mysqli_real_escape_string($conn, $_GET["complaint_id"]);


/* ================================================= */
/* CHECK COMPLAINT BELONGS TO LOGGED-IN CITIZEN */
/* ================================================= */

$sql = "
    SELECT complaint_id
    FROM complaints
    WHERE complaint_id='$complaint_id'
    AND citizen_id='$citizen_id'
";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Unauthorized complaint deletion.");
}


/* ================================================= */
/* DELETE COMPLAINT IMAGES */
/* ================================================= */

$image_sql = "
    SELECT image_path
    FROM complaint_images
    WHERE complaint_id='$complaint_id'
";

$image_result = mysqli_query($conn, $image_sql);

if ($image_result) {

    while ($image = mysqli_fetch_assoc($image_result)) {

        $image_path = $image["image_path"];

        if (!empty($image_path) && file_exists($image_path)) {
            unlink($image_path);
        }
    }
}


/* ================================================= */
/* DELETE IMAGE RECORDS */
/* ================================================= */

$delete_images = "
    DELETE FROM complaint_images
    WHERE complaint_id='$complaint_id'
";

mysqli_query($conn, $delete_images);


/* ================================================= */
/* DELETE SUPPORTS */
/* ================================================= */

$delete_support = "
    DELETE FROM complaint_support
    WHERE complaint_id='$complaint_id'
";

mysqli_query($conn, $delete_support);


/* ================================================= */
/* DELETE COMMENTS */
/* ================================================= */

$delete_comments = "
    DELETE FROM complaint_comments
    WHERE complaint_id='$complaint_id'
";

mysqli_query($conn, $delete_comments);


/* ================================================= */
/* DELETE COMPLAINT */
/* ================================================= */

$delete_complaint = "
    DELETE FROM complaints
    WHERE complaint_id='$complaint_id'
    AND citizen_id='$citizen_id'
";

if (mysqli_query($conn, $delete_complaint)) {

    header("Location: profile.php");
    exit;

} else {

    die("Complaint deletion failed: " . mysqli_error($conn));
}

?>