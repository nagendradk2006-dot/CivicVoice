
<?php
session_start();
include "db.php";

if (!isset($_SESSION["citizen_id"])) {
    header("Location: login.php");
    exit;
}

$citizen_id = $_SESSION["citizen_id"];


/* PROFILE IMAGE UPLOAD */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $profile_image = $_FILES["profile_image"];

    if ($profile_image["error"] != 0) {
        echo "<h3>Upload Error Code: " . $profile_image["error"] . "</h3>";
        exit;
    }

    $extension = pathinfo($profile_image["name"], PATHINFO_EXTENSION);

    $file_name = "profile_" . $citizen_id . "." . $extension;

    $file_path = "C:/xampp/htdocs/CivicVoice/profile_images/" . $file_name;

    if (move_uploaded_file($profile_image["tmp_name"], $file_path)) {

        $database_path = "profile_images/" . $file_name;

        $sql = "UPDATE citizens
                SET profile_image='$database_path'
                WHERE citizen_id='$citizen_id'";

        if (mysqli_query($conn, $sql)) {
            header("Location: profile.php");
            exit;
        }

    } else {

        echo "<h3>Profile image upload failed.</h3>";
        exit;

    }
}


/* GET CITIZEN DETAILS */

$sql = "SELECT citizen_name, username, profile_image
        FROM citizens
        WHERE citizen_id='$citizen_id'";

$result = mysqli_query($conn, $sql);

$citizen = mysqli_fetch_assoc($result);


/* COUNT CITIZEN POSTS */

$post_count_sql = "SELECT COUNT(*) AS total_posts
                   FROM civic_posts
                   WHERE citizen_id='$citizen_id'";

$post_count_result = mysqli_query($conn, $post_count_sql);

$post_count_data = mysqli_fetch_assoc($post_count_result);

$total_posts = $post_count_data["total_posts"];


/* GET CITIZEN POSTS */

$post_sql = "SELECT civic_posts.*, departments.department_name
             FROM civic_posts
             INNER JOIN departments
             ON civic_posts.department_id = departments.department_id
             WHERE civic_posts.citizen_id='$citizen_id'
             ORDER BY civic_posts.created_at DESC";

$post_result = mysqli_query($conn, $post_sql);

?>

<!DOCTYPE html>
<html>

<head>
<link rel="stylesheet" href="style.css">
<title>CivicVoice Profile</title>

</head>

<body>

<h2 style="text-align:center;">👤 My Profile</h2>


<!-- PROFILE IMAGE -->

<div style="text-align:center;">

<?php

if (!empty($citizen["profile_image"])) {

?>

<img src="<?php echo $citizen["profile_image"]; ?>?v=<?php echo time(); ?>"
     width="150"
     height="150"
     style="border-radius:50%; object-fit:cover;">

<?php

} else {

    echo "<p>No profile image uploaded.</p>";

}

?>

</div>


<!-- NAME -->

<h3 style="text-align:center;">

<?php echo $citizen["citizen_name"]; ?>

</h3>


<!-- USERNAME -->

<p style="text-align:center;">

@<?php echo $citizen["username"]; ?>

</p>


<!-- POST COUNT -->

<div style="text-align:center; margin:20px 0;">

<strong><?php echo $total_posts; ?> Posts</strong>

</div>


<hr>


<!-- UPLOAD PROFILE PICTURE -->

<h3>Upload Profile Picture</h3>

<form method="POST" enctype="multipart/form-data">

<input type="file"
       name="profile_image"
       accept="image/*"
       required>

<br><br>

<button type="submit">

Upload Profile Picture

</button>

</form>


<hr>


<!-- MY CIVIC POSTS -->

<h2 style="text-align:center;">📷 My Civic Posts</h2>


<?php

if (mysqli_num_rows($post_result) > 0) {

    while ($post = mysqli_fetch_assoc($post_result)) {

?>

<hr>


<p>

<strong>Post ID:</strong>

P<?php echo $post["post_id"]; ?>

</p>


<p>

<strong>Constituency:</strong>

<?php echo $post["constituency_name"]; ?>

</p>


<p>

<strong>Ward Number:</strong>

<?php echo $post["ward_number"]; ?>

</p>


<p>

<strong>Area:</strong>

<?php echo $post["area_name"]; ?>

</p>


<p>

<strong>Department:</strong>

<?php echo $post["department_name"]; ?>

</p>


<p>

<strong>Issue:</strong>

<?php echo $post["issue_description"]; ?>

</p>


<!-- POST PHOTOS -->

<?php

$image_sql = "SELECT image_path
              FROM civic_post_images
              WHERE post_id='" . $post["post_id"] . "'
              ORDER BY image_id ASC";

$image_result = mysqli_query($conn, $image_sql);

if (mysqli_num_rows($image_result) > 0) {

?>

<h3>📷 Photos</h3>

<?php

while ($image = mysqli_fetch_assoc($image_result)) {

?>

<img src="<?php echo $image["image_path"]; ?>"
     width="300"
     style="margin:5px;">

<?php

}

} else {

    /* FALLBACK TO MAIN POST IMAGE */

    if (!empty($post["post_image"])) {

?>

<h3>📷 Photo</h3>

<img src="<?php echo $post["post_image"]; ?>"
     width="300">

<?php

    }

}

?>


<br><br>


<!-- POST VIDEO -->

<?php

if (!empty($post["post_video"])) {

?>

<h3>🎥 Video</h3>

<video width="300" controls>

<source src="<?php echo $post["post_video"]; ?>"
        type="video/mp4">

Your browser does not support video playback.

</video>

<br><br>

<?php

}

?>


<p>

<strong>Posted On:</strong>

<?php echo $post["created_at"]; ?>

</p>
<br>

<button onclick="confirmDelete(<?php echo $post["post_id"]; ?>)">
    🗑️ Delete Post
<button onclick="window.location.href='edit_post.php?post_id=<?php echo $post["post_id"]; ?>'">
    ✏️ Edit Post
<button onclick="window.location.href='edit_post.php?post_id=<?php echo $post["post_id"]; ?>'">
    
</button>

<br><br>
</button>

<?php

    }

} else {

    echo "<p style='text-align:center;'>You have not created any civic posts yet.</p>";

}

?>


<hr>


<div style="text-align:center;">

<button onclick="window.location.href='home.php'">

← Back to Home

</button>

</div>

<script>

function confirmDelete(postId) {

    if (confirm("Are you sure you want to delete this post?")) {

        window.location.href = "delete_post.php?post_id=" + postId;

    }

}

</script>
</body>

</html>


