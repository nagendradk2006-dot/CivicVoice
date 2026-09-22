
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

<style>

/* =========================================================
   1. PROFILE PAGE BACKGROUND
   ========================================================= */

body {
    margin: 0;
    background-color: #E8E2D5;
    color: #1F2937;
}
/* CLICKABLE PROFILE IMAGE */

.profile-image-container {
    text-align: center;
    margin: 20px auto;
}

.profile-image-label {
    display: inline-block;
    cursor: pointer;
}

.profile-image {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #C9A227;
    box-shadow: 0 5px 15px rgba(31, 41, 55, 0.15);
    transition: 0.2s ease;
}

.profile-image:hover {
    opacity: 0.8;
    transform: scale(1.03);
}

.no-profile-image {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background-color: #ffffff;
    border: 4px solid #C9A227;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 50px;
}
/* =========================================================
   2. PROFILE HEADER
   ========================================================= */

body > h2 {
    max-width: 760px;
    margin: 30px auto 20px;
    padding: 18px;
    background-color: #ffffff;
    border: 2px solid #166534;
    border-top: 6px solid #166534;
    border-radius: 18px;
    box-shadow: 0 6px 18px rgba(22, 101, 52, 0.12);
    color: #166534;
    font-size: 26px;
    font-weight: 700;
    box-sizing: border-box;
}
/* =========================================================
   3. PROFILE INFORMATION
   ========================================================= */

body > div[style*="text-align:center"] {
    max-width: 760px;
    margin: 0 auto;
}

body > div[style*="text-align:center"] img {
    border: 4px solid #C9A227;
    box-shadow: 0 5px 15px rgba(31, 41, 55, 0.12);
}

body > h3 {
    color: #166534;
    font-size: 22px;
    margin: 15px 0 5px;
}

body > p[style*="text-align:center"] {
    color: #666666;
    font-size: 15px;
    margin: 5px 0;
}
/* =========================================================
   4. POSTS COUNT
   ========================================================= */

body > div[style*="margin:20px 0"] {
    max-width: 760px;
    margin: 20px auto !important;
    padding: 16px;
    background-color: #ffffff;
    border: 1px solid #ddd6c8;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(31, 41, 55, 0.06);
    box-sizing: border-box;
    color: #166534;
    font-size: 16px;
}
/* =========================================================
   5. PROFILE IMAGE UPLOAD
   ========================================================= */

body > h3 {
    max-width: 760px;
    margin: 25px auto 12px;
}

body > form {
    max-width: 760px;
    margin: 0 auto 25px;
    padding: 20px;
    background-color: #ffffff;
    border: 1px solid #ddd6c8;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(31, 41, 55, 0.06);
    box-sizing: border-box;
}

body > form input[type="file"] {
    width: 100%;
    box-sizing: border-box;
    padding: 12px;
    border: 2px dashed #C9A227;
    border-radius: 10px;
    background-color: #faf9f5;
}

body > form button {
    padding: 11px 18px;
    border: none;
    border-radius: 8px;
    background-color: #166534;
    color: #ffffff;
    font-weight: 700;
    cursor: pointer;
}
/* =========================================================
   6. CIVIC POSTS HEADING
   ========================================================= */

body > h2[style*="text-align:center"] {
    max-width: 760px;
    margin: 30px auto 20px;
    color: #166534;
    font-size: 23px;
    font-weight: 700;
}
/* =========================================================
   7. CIVIC POST CARDS
   ========================================================= */

body > hr {
    max-width: 760px;
    margin: 25px auto;
    border: none;
    border-top: 1px solid #ddd6c8;
}

body > p {
    max-width: 760px;
    margin: 10px auto;
    line-height: 1.6;
    color: #374151;
}

body > p strong {
    color: #166534;
}
/* =========================================================
   8. POST IMAGES
   ========================================================= */

body > img {
    display: block;
    width: 100%;
    max-width: 700px;
    height: 400px;
    object-fit: cover;
    margin: 12px auto;
    border-radius: 12px;
    border: 1px solid #ddd6c8;
    box-shadow: 0 4px 12px rgba(31, 41, 55, 0.08);
}
/* =========================================================
   10. POST ACTION BUTTONS
   ========================================================= */

body > button {
    padding: 10px 16px;
    margin: 5px;
    border: none;
    border-radius: 8px;
    background-color: #C9A227;
    color: #1F2937;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s ease;
}

body > button:hover {
    background-color: #166534;
    color: #ffffff;
}
/* =========================================================
   11. BACK TO HOME BUTTON
   ========================================================= */

body > div[style*="text-align:center"] button {
    padding: 11px 20px;
    border: none;
    border-radius: 8px;
    background-color: #166534;
    color: #ffffff;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s ease;
}

body > div[style*="text-align:center"] button:hover {
    background-color: #C9A227;
    color: #1F2937;
}
/* =========================================================
   12. MOBILE RESPONSIVE
   ========================================================= */

@media (max-width: 700px) {

    body > h2 {
        margin: 20px 10px;
        padding: 15px;
        font-size: 22px;
    }

    body > div[style*="text-align:center"],
    body > form,
    body > p,
    body > hr {
        max-width: none;
        margin-left: 10px;
        margin-right: 10px;
    }

    body > img {
        width: calc(100% - 20px);
        height: 300px;
    }

    body > video {
        width: calc(100% - 20px);
        height: 300px;
    }

    body > button {
        margin: 5px 3px;
    }
}
/* POST ACTION BUTTONS */

.post-actions {
    max-width: 700px;
    margin: 15px auto 25px;
    display: flex;
    justify-content: center;
    gap: 12px;
}

.post-actions button {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s ease;
}

.edit-post-btn {
    background-color: #166534;
    color: white;
}

.edit-post-btn:hover {
    background-color: #14532d;
}

.delete-post-btn {
    background-color: #b91c1c;
    color: white;
}

.delete-post-btn:hover {
    background-color: #991b1b;
}
/* PROFILE USERNAME */

.profile-username {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    margin: 8px auto 20px;
}

/* PROFILE USERNAME */

.profile-username {
    display: block;
    width: fit-content;

    margin: 8px auto 22px;
    padding: 6px 16px;

    color: #166534;
    background-color: #ffffff;

    border: 1px solid #C9A227;
    border-radius: 20px;

    font-family: Georgia, "Times New Roman", serif;
    font-size: 16px;
    font-weight: 600;
    letter-spacing: 0.5px;

    box-shadow: 0 3px 10px rgba(31, 41, 55, 0.08);
}
.profile-home-button {
    max-width: 760px;
    margin: 20px auto 10px;
    text-align: left;
}

.profile-home-button button {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    background-color: #166534;
    color: #ffffff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s ease;
}

.profile-home-button button:hover {
    background-color: #C9A227;
    color: #1F2937;
}
.profile-image-section {
    max-width: 760px;
    margin: 20px auto;
    display: flex;
    align-items: center;
    gap: 40px;
}

.profile-home-button {
    flex-shrink: 0;
}

.profile-home-button button {
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    background-color: #166534;
    color: #ffffff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s ease;
}

.profile-home-button button:hover {
    background-color: #C9A227;
    color: #1F2937;
}

.profile-image-section .profile-image-container {
    margin: 0;
}
/* PROFILE CIVIC POST CARD */

.profile-post-card {
    max-width: 760px;
    margin: 25px auto;
    padding: 20px;
    background-color: #ffffff;
    border: 2px solid #C9A227;
    border-radius: 16px;
    box-sizing: border-box;
    box-shadow: 0 5px 15px rgba(31, 41, 55, 0.08);
}
/* PROFILE POST IMAGES */

.profile-post-card img {
    display: block;
    width: 100%;
    max-width: 650px;
    height: 350px;
    object-fit: cover;
    margin: 15px auto;
    border-radius: 12px;
    border: 2px solid #C9A227;
    box-sizing: border-box;
}
.profile-home-button {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 9999;
}

</style>

</head>
</head>

<body>

<h2 style="text-align:center;">👤 My Profile</h2>




</div>
<!-- PROFILE IMAGE -->

<div class="profile-image-container">

    <form method="POST" enctype="multipart/form-data" id="profileForm">

        <label for="profileImageInput" class="profile-image-label">

            <?php if (!empty($citizen["profile_image"])) { ?>

                <img
                    src="<?php echo $citizen["profile_image"]; ?>?v=<?php echo time(); ?>"
                    class="profile-image"
                >

            <?php } else { ?>

                <div class="no-profile-image">
                    👤
                </div>

            <?php } ?>

        </label>

        <input
            type="file"
            id="profileImageInput"
            name="profile_image"
            accept="image/*"
            hidden
            onchange="document.getElementById('profileForm').submit();"
        >

    </form>

</div>

<!-- NAME -->

<h3 style="text-align:center;">

<?php echo $citizen["citizen_name"]; ?>

</h3>

<!-- USERNAME -->

<div class="profile-username">
    @<?php echo $citizen["username"]; ?>
</div>



<!-- MY CIVIC POSTS -->

<h2 style="text-align:center;">📷 My Civic Posts</h2>


<?php

if (mysqli_num_rows($post_result) > 0) {
while ($post = mysqli_fetch_assoc($post_result)) {
?>

<div class="profile-post-card">

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

<div class="post-actions">

    <button
        class="edit-post-btn"
        onclick="window.location.href='edit_post.php?post_id=<?php echo $post["post_id"]; ?>'">
        ✏️ Edit Post
    </button>

    <button
        class="delete-post-btn"
        onclick="confirmDelete(<?php echo $post["post_id"]; ?>)">
        🗑️ Delete Post
    </button>

</div>

<br>
</div>
<?php

    }

} else {

    echo "<p style='text-align:center;'>You have not created any civic posts yet.</p>";

}

?>


<hr>

<div class="profile-home-button">
    <button onclick="window.location.href='home.php'">
        ← Back to Home
    </button>
</div>

</button>

</div>

<script>

function confirmDelete(postId) {

    if (confirm("Are you sure you want to delete this post?")) {

        window.location.href = "delete_post.php?post_id=" + postId;

    }

}
function confirmDelete(postId) {

    if (confirm("Are you sure you want to delete this post?")) {

        window.location.href = "delete_post.php?post_id=" + postId;

    }

}



</script>
</body>

</html>


