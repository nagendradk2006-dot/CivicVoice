<?php

session_start();
include "db.php";


/* Check citizen login */

if (!isset($_SESSION["citizen_id"])) {

    header("Location: login.php");
    exit;

}

$citizen_id = $_SESSION["citizen_id"];


/* Check post ID */

if (!isset($_GET["post_id"])) {

    echo "<h2>Post ID is missing.</h2>";
    exit;

}

$post_id = $_GET["post_id"];


/* Check whether the post belongs to the logged-in citizen */

$sql = "SELECT civic_posts.*, departments.department_name
        FROM civic_posts
        INNER JOIN departments
        ON civic_posts.department_id = departments.department_id
        WHERE civic_posts.post_id='$post_id'
        AND civic_posts.citizen_id='$citizen_id'";

$result = mysqli_query($conn, $sql);


if (mysqli_num_rows($result) != 1) {

    echo "<h2>You are not allowed to edit this post.</h2>";
    exit;

}


$post = mysqli_fetch_assoc($result);


/* UPDATE POST */

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $constituency_name = $_POST["constituency_name"];
    $ward_number = $_POST["ward_number"];
    $area_name = $_POST["area_name"];
    $department = $_POST["department"];
    $issue_description = $_POST["issue_description"];


    /* Get department ID */

    $department_sql = "SELECT department_id
                       FROM departments
                       WHERE department_name='$department'";

    $department_result = mysqli_query(
        $conn,
        $department_sql
    );

    $department_data = mysqli_fetch_assoc(
        $department_result
    );

    $department_id = $department_data["department_id"];


    /* Count existing images */

    $image_count_sql = "SELECT COUNT(*) AS total_images
                        FROM civic_post_images
                        WHERE post_id='$post_id'";

    $image_count_result = mysqli_query(
        $conn,
        $image_count_sql
    );

    $image_count_data = mysqli_fetch_assoc(
        $image_count_result
    );

    $existing_images = $image_count_data["total_images"];


    /* Delete selected images */

    if (isset($_POST["delete_images"])) {

        foreach ($_POST["delete_images"] as $image_id) {

            $image_sql = "SELECT image_path
                          FROM civic_post_images
                          WHERE image_id='$image_id'
                          AND post_id='$post_id'";

            $image_result = mysqli_query(
                $conn,
                $image_sql
            );

            if (mysqli_num_rows($image_result) == 1) {

                $image_data = mysqli_fetch_assoc(
                    $image_result
                );

                $image_path = $image_data["image_path"];


                /* Delete image from database */

                $delete_image_sql = "DELETE FROM civic_post_images
                                     WHERE image_id='$image_id'
                                     AND post_id='$post_id'";

                mysqli_query(
                    $conn,
                    $delete_image_sql
                );


                /* Delete physical image */

                $full_path = "C:/xampp/htdocs/CivicVoice/"
                             . $image_path;

                if (file_exists($full_path)) {

                    unlink($full_path);

                }

            }

        }

    }


    /* Count images after deletion */

    $image_count_sql = "SELECT COUNT(*) AS total_images
                        FROM civic_post_images
                        WHERE post_id='$post_id'";

    $image_count_result = mysqli_query(
        $conn,
        $image_count_sql
    );

    $image_count_data = mysqli_fetch_assoc(
        $image_count_result
    );

    $remaining_images = $image_count_data["total_images"];


    /* Add new images */

    if (
        isset($_FILES["new_images"])
        && !empty($_FILES["new_images"]["name"][0])
    ) {


        $new_image_count = count(
            $_FILES["new_images"]["name"]
        );


        /* Check 10 image limit */

        if (
            $remaining_images + $new_image_count > 10
        ) {

            echo "<h2>
                  Maximum 10 photos are allowed.
                  </h2>";

            exit;

        }


        for (
            $i = 0;
            $i < $new_image_count;
            $i++
        ) {


            $extension = pathinfo(
                $_FILES["new_images"]["name"][$i],
                PATHINFO_EXTENSION
            );


            $file_name = "post_"
                       . time()
                       . "_"
                       . uniqid()
                       . "_"
                       . ($i + 1)
                       . "."
                       . $extension;


            $file_path = "Images/" . $file_name;


            if (
                move_uploaded_file(
                    $_FILES["new_images"]["tmp_name"][$i],
                    $file_path
                )
            ) {


                $image_sql = "INSERT INTO civic_post_images
                              (post_id, image_path)
                              VALUES
                              ('$post_id', '$file_path')";


                mysqli_query(
                    $conn,
                    $image_sql
                );

            }

        }

    }


    /* Update post details */

    $update_sql = "UPDATE civic_posts
                   SET constituency_name='$constituency_name',
                       ward_number='$ward_number',
                       area_name='$area_name',
                       department_id='$department_id',
                       issue_description='$issue_description'
                   WHERE post_id='$post_id'
                   AND citizen_id='$citizen_id'";


    if (mysqli_query($conn, $update_sql)) {


        /* Update main post image */

        $main_image_sql = "SELECT image_path
                           FROM civic_post_images
                           WHERE post_id='$post_id'
                           ORDER BY image_id ASC
                           LIMIT 1";

        $main_image_result = mysqli_query(
            $conn,
            $main_image_sql
        );


        if (mysqli_num_rows($main_image_result) > 0) {

            $main_image_data = mysqli_fetch_assoc(
                $main_image_result
            );

            $main_image = $main_image_data["image_path"];


            $update_main_image_sql = "UPDATE civic_posts
                                      SET post_image='$main_image'
                                      WHERE post_id='$post_id'
                                      AND citizen_id='$citizen_id'";

            mysqli_query(
                $conn,
                $update_main_image_sql
            );

        } else {

            $clear_main_image_sql = "UPDATE civic_posts
                                     SET post_image=''
                                     WHERE post_id='$post_id'
                                     AND citizen_id='$citizen_id'";

            mysqli_query(
                $conn,
                $clear_main_image_sql
            );

        }


        header("Location: profile.php");
        exit;


    } else {

        echo "<h2>Post update failed.</h2>";

    }

}

?>

<!DOCTYPE html>
<html>

<head>

<title>Edit Civic Post - CivicVoice</title>

</head>

<body>


<h2>✏️ Edit Civic Post</h2>


<form method="POST"
      enctype="multipart/form-data">


<label>Constituency Name:</label><br>

<input type="text"
       name="constituency_name"
       value="<?php echo $post["constituency_name"]; ?>"
       required>

<br><br>


<label>Ward Number:</label><br>

<input type="text"
       name="ward_number"
       value="<?php echo $post["ward_number"]; ?>"
       required>

<br><br>


<label>Area Name:</label><br>

<input type="text"
       name="area_name"
       value="<?php echo $post["area_name"]; ?>"
       required>

<br><br>


<label>Department:</label><br>

<select name="department" required>

<option value="">
Select Department
</option>


<?php

$department_sql = "SELECT department_name
                   FROM departments";

$department_result = mysqli_query(
    $conn,
    $department_sql
);


while (
    $department = mysqli_fetch_assoc(
        $department_result
    )
) {


    $selected = "";


    if (
        $department["department_name"]
        == $post["department_name"]
    ) {

        $selected = "selected";

    }


    echo "<option value='"
         . $department["department_name"]
         . "' "
         . $selected
         . ">"

         . $department["department_name"]

         . "</option>";

}

?>

</select>

<br><br>


<label>Describe the Civic Issue:</label><br>

<textarea name="issue_description"
          rows="5"
          cols="50"
          required><?php echo $post["issue_description"]; ?></textarea>

<br><br>


<hr>


<h3>📷 Existing Photos</h3>


<?php

$image_sql = "SELECT image_id, image_path
              FROM civic_post_images
              WHERE post_id='$post_id'
              ORDER BY image_id ASC";

$image_result = mysqli_query(
    $conn,
    $image_sql
);


if (mysqli_num_rows($image_result) > 0) {


    while (
        $image = mysqli_fetch_assoc(
            $image_result
        )
    ) {

?>

<div style="display:inline-block;
            text-align:center;
            margin:10px;">

<img src="<?php echo $image["image_path"]; ?>"
     width="200"
     height="150"
     style="object-fit:cover;">

<br>

<label>

<input type="checkbox"
       name="delete_images[]"
       value="<?php echo $image["image_id"]; ?>">

🗑️ Delete this photo

</label>

</div>

<?php

    }

} else {

    echo "<p>No photos available.</p>";

}

?>


<br><br>


<h3>➕ Add New Photos</h3>

<input type="file"
       name="new_images[]"
       accept="image/*"
       multiple>

<p>
You can keep or delete existing photos and add new photos.
Maximum total photos: 10.
</p>


<br>


<button type="submit">

💾 Update Post

</button>


</form>


<br>


<button onclick="window.location.href='profile.php'">

← Back to Profile

</button>


</body>

</html>