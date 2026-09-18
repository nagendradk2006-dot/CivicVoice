<?php
session_start();
include "db.php";

/* Check citizen login */
if (!isset($_SESSION["citizen_id"])) {
    header("Location: login.php");
    exit;
}

$citizen_id = $_SESSION["citizen_id"];

/* Get post ID */
$post_id = $_GET["post_id"];

/* Check whether this post belongs to the logged-in citizen */
$sql = "SELECT * FROM civic_posts
        WHERE post_id='$post_id'
        AND citizen_id='$citizen_id'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) != 1) {
    echo "<h2>You are not allowed to delete this post.</h2>";
    exit;
}

$post = mysqli_fetch_assoc($result);

/* Delete post images from database */
$delete_images_sql = "DELETE FROM civic_post_images
                      WHERE post_id='$post_id'";
mysqli_query($conn, $delete_images_sql);

/* Delete support records */
$feed_id = "P" . $post_id;

$delete_support_sql = "DELETE FROM complaint_support
                       WHERE complaint_id='$feed_id'";
mysqli_query($conn, $delete_support_sql);

/* Delete comments */
$delete_comments_sql = "DELETE FROM complaint_comments
                        WHERE complaint_id='$feed_id'";
mysqli_query($conn, $delete_comments_sql);

/* Delete the post */
$delete_post_sql = "DELETE FROM civic_posts
                    WHERE post_id='$post_id'
                    AND citizen_id='$citizen_id'";

if (mysqli_query($conn, $delete_post_sql)) {
    header("Location: profile.php");
    exit;
} else {
    echo "<h2>Post deletion failed.</h2>";
}
?>