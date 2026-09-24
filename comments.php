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

    <style>

        /* =================================================
           CIVICVOICE COMMENTS PAGE
        ================================================= */

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 15px;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #222;
        }

        .comments-container {
            width: 100%;
            max-width: 850px;
            margin: 0 auto;
        }

        .comments-header {
            background: #176b3a;
            color: white;
            padding: 22px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .comments-header h2 {
            margin: 0 0 10px;
            font-size: 26px;
        }

        .complaint-id {
            margin: 0;
            font-size: 15px;
        }

        /* =================================================
           WRITE COMMENT
        ================================================= */

        .comment-form-card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .comment-form-card h3 {
            margin-top: 0;
            margin-bottom: 15px;
        }

        .comment-textarea {
            width: 100%;
            min-height: 110px;
            padding: 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            resize: vertical;
            font-family: Arial, sans-serif;
            font-size: 15px;
            outline: none;
        }

        .comment-textarea:focus {
            border-color: #176b3a;
            box-shadow: 0 0 0 2px rgba(23, 107, 58, 0.12);
        }

        .comment-submit {
            margin-top: 12px;
            padding: 11px 20px;
            border: none;
            border-radius: 7px;
            background: #176b3a;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .comment-submit:hover {
            background: #12572f;
        }

        /* =================================================
           COMMENTS LIST
        ================================================= */

        .comments-list-title {
            margin-bottom: 15px;
        }

        .comment-card {
            background: white;
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 14px;
            box-shadow: 0 2px 7px rgba(0, 0, 0, 0.07);
            border-left: 4px solid #176b3a;
        }

        .comment-user {
            margin: 0 0 10px;
            font-size: 16px;
            color: #176b3a;
        }

        .comment-text {
            margin: 0 0 12px;
            font-size: 15px;
            line-height: 1.6;
            color: #333;
            word-wrap: break-word;
        }

        .comment-date {
            font-size: 12px;
            color: #777;
        }

        .no-comments {
            background: white;
            padding: 20px;
            border-radius: 10px;
            color: #666;
            text-align: center;
        }

        /* =================================================
           BACK TO HOME
        ================================================= */

        .back-home {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 18px;
            background: #333;
            color: white;
            text-decoration: none;
            border-radius: 7px;
            cursor: pointer;
        }

        .back-home:hover {
            background: #111;
        }

        /* =================================================
           MOBILE
        ================================================= */

        @media (max-width: 600px) {

            body {
                padding: 15px 10px;
            }

            .comments-header {
                padding: 18px;
            }

            .comments-header h2 {
                font-size: 22px;
            }

            .comment-form-card {
                padding: 16px;
            }

            .comment-card {
                padding: 15px;
            }

            .comment-submit {
                width: 100%;
            }

        }

    </style>
<body>

<div class="comments-container">

    <div class="comments-header">

        <h2>💬 Comments</h2>

        <p class="complaint-id">
            <strong>Complaint ID:</strong>
            <?php echo $complaint_id; ?>
        </p>

    </div>
</head>



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