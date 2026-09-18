<?php

session_start();
include "db.php";

/* Check whether citizen is logged in */

if (!isset($_SESSION["citizen_id"])) {
    header("Location: login.php");
    exit;
}

$citizen_id = $_SESSION["citizen_id"];

/* Get complaint ID from URL */

$complaint_id = $_GET["complaint_id"];


/* Add new comment */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $comment_text = $_POST["comment_text"];

    $sql = "INSERT INTO complaint_comments
            (complaint_id, citizen_id, comment_text)
            VALUES
            ('$complaint_id', '$citizen_id', '$comment_text')";

    mysqli_query($conn, $sql);

    header("Location: comments.php?complaint_id=$complaint_id");
    exit;
}


/* Get comments */

$sql = "SELECT complaint_comments.*, citizens.citizen_name
        FROM complaint_comments
        INNER JOIN citizens
        ON complaint_comments.citizen_id = citizens.citizen_id
        WHERE complaint_comments.complaint_id = '$complaint_id'
        ORDER BY complaint_comments.created_at DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Comments - CivicVoice</title>

</head>

<body>

<h2>💬 Comments</h2>

<p>
    <strong>Complaint ID:</strong>
    <?php echo $complaint_id; ?>
</p>

<hr>


<h3>Write a Comment</h3>

<form method="POST">

    <textarea
        name="comment_text"
        rows="4"
        cols="50"
        placeholder="Write your comment..."
        required></textarea>

    <br><br>

    <button type="submit">
        Post Comment
    </button>

</form>

<hr>


<h3>Citizen Comments</h3>

<?php

if (mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {

?>

<p>
    👤 <strong>
        <?php echo $row["citizen_name"]; ?>
    </strong>
</p>

<p>
    <?php echo $row["comment_text"]; ?>
</p>

<small>
    <?php echo $row["created_at"]; ?>
</small>

<hr>

<?php

    }

} else {

    echo "<p>No comments yet. Be the first to comment!</p>";

}

?>


<br>

<button onclick="window.location.href='home.php'">
    ← Back to Home
</button>


</body>

</html>