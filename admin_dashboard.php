<?php 
 
session_start(); 
include "db.php"; 
 
/* ================================================= */
/* CHECK ADMIN LOGIN */
/* ================================================= */ 
 
if (!isset($_SESSION["admin_id"])) { 
    header("Location: admin_login.php"); 
    exit; 
} 
 
/* ================================================= */
/* GET ALL COMPLAINTS */
/* ================================================= */ 
 
$complaint_sql = "SELECT 
                    complaints.complaint_id AS feed_id, 
                    'Complaint' AS feed_type, 
                    complaints.citizen_id, 
                    complaints.constituency_name, 
                    complaints.ward_number, 
                    complaints.area_name, 
                    complaints.pincode, 
                    complaints.issue_description, 
                    complaints.issue_image, 
                    '' AS post_video, 
                    complaints.status, 
                    complaints.created_at, 
                    departments.department_name 
                  FROM complaints 
                  INNER JOIN departments 
                  ON complaints.department_id = departments.department_id"; 
 
/* ================================================= */
/* GET ALL CIVIC POSTS */
/* ================================================= */ 
 
$post_sql = "SELECT 
                CONCAT('P', civic_posts.post_id) AS feed_id, 
                'Post' AS feed_type, 
                civic_posts.citizen_id, 
                civic_posts.constituency_name, 
                civic_posts.ward_number, 
                civic_posts.area_name, 
                '' AS pincode, 
                civic_posts.issue_description, 
                civic_posts.post_image AS issue_image, 
                civic_posts.post_video, 
                'Posted' AS status, 
                civic_posts.created_at, 
                departments.department_name 
             FROM civic_posts 
             INNER JOIN departments 
             ON civic_posts.department_id = departments.department_id"; 
 
/* ================================================= */
/* COMBINE COMPLAINTS AND POSTS */
/* ================================================= */ 
 
$sql = "($complaint_sql) 
        UNION ALL 
        ($post_sql) 
        ORDER BY created_at DESC"; 
 
$result = mysqli_query($conn, $sql); 
 
if (!$result) { 
    die("Database query failed: " . mysqli_error($conn)); 
} 
 
?> 
 
<!DOCTYPE html> 
<html lang="en"> 
 
<head> 
 
    <meta charset="UTF-8"> 
 
    <meta name="viewport" 
          content="width=device-width, initial-scale=1.0"> 
 
    <title>CivicVoice Admin Dashboard</title> 
 
    <link rel="stylesheet" href="style.css"> 
 
    <style> 
 
        /* ================================================= 
           CIVICVOICE ADMIN DASHBOARD
           ================================================= */ 
 
         body.admin-dashboard-page * {
    box-sizing: border-box;
}

body.admin-dashboard-page {
    margin: 0;
    padding: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f8f5;
    color: #222;
}
 
        /* ================================================= 
           HEADER
           ================================================= */ 
 
        .admin-dashboard-header { 
            width: 92%; 
            max-width: 1150px; 
            margin: 30px auto 20px; 
            padding: 20px 25px; 
            background: #ffffff; 
            border: 2px solid #c9a227; 
            border-radius: 18px; 
            display: flex; 
            align-items: center; 
            gap: 20px; 
            box-shadow: 
                0 8px 25px rgba(0, 0, 0, 0.08); 
        } 
 
        /* ================================================= 
           LOGO
           ================================================= */ 
 
        .admin-dashboard-logo { 
            width: 78px; 
            height: 78px; 
            flex-shrink: 0; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background: #ffffff; 
            border: 3px solid #c9a227; 
            border-radius: 50%; 
            padding: 6px; 
            overflow: hidden; 
        } 
 
        .admin-dashboard-logo img { 
            width: 100%; 
            height: 100%; 
            object-fit: contain; 
            display: block; 
        } 
 
        /* ================================================= 
           HEADER TEXT
           ================================================= */ 
 
        .admin-dashboard-title { 
            flex: 1; 
        } 
 
        .admin-dashboard-title h2 { 
            margin: 0 0 6px; 
            color: #176b3a; 
            font-size: 30px; 
            font-weight: 700; 
        } 
 
        .admin-dashboard-title p { 
            margin: 0; 
            color: #777; 
            font-size: 14px; 
        } 
 
        /* ================================================= 
           TOP LOGOUT BUTTON
           ================================================= */ 
 
        .top-logout-button { 
            flex-shrink: 0; 
            padding: 11px 20px; 
            background: #b22222; 
            color: #ffffff; 
            border: 2px solid #8b0000; 
            border-radius: 8px; 
            font-size: 14px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: all 0.25s ease; 
        } 
 
        .top-logout-button:hover { 
            background: #8b0000; 
            transform: translateY(-2px); 
            box-shadow: 
                0 5px 12px rgba(139, 0, 0, 0.20); 
        } 
 
        /* ================================================= 
           WELCOME MESSAGE
           ================================================= */ 
 
        .welcome-message { 
            width: 92%; 
            max-width: 1150px; 
            margin: 10px auto 25px; 
            padding: 14px 18px; 
            background: #ffffff; 
            border-left: 5px solid #c9a227; 
            border-radius: 8px; 
            color: #555; 
            box-shadow: 
                0 3px 12px rgba(0, 0, 0, 0.05); 
        } 
 
        .welcome-message strong { 
            color: #176b3a; 
        } 
 
        /* ================================================= 
           PAGE TITLE
           ================================================= */ 
 
        .dashboard-title { 
            width: 92%; 
            max-width: 1150px; 
            margin: 0 auto 20px; 
            text-align: center; 
        } 
 
        .dashboard-title h3 { 
            margin: 0; 
            color: #176b3a; 
            font-size: 25px; 
        } 
 
        .dashboard-title .title-line { 
            width: 90px; 
            height: 3px; 
            margin: 10px auto 0; 
            background: #c9a227; 
            border-radius: 10px; 
        } 
 
        /* ================================================= 
           FEED CARD
           ================================================= */ 
 
        .admin-feed-card { 
            width: 92%; 
            max-width: 900px; 
            margin: 25px auto; 
            padding: 25px; 
            background: #ffffff; 
            border: 2px solid #c9a227; 
            border-radius: 18px; 
            box-shadow: 
                0 7px 22px rgba(0, 0, 0, 0.08); 
            transition: 
                transform 0.25s ease, 
                box-shadow 0.25s ease; 
        } 
 
        .admin-feed-card:hover { 
            transform: translateY(-3px); 
            box-shadow: 
                0 12px 30px rgba(0, 0, 0, 0.13); 
        } 
 
        .admin-feed-card > hr { 
            display: none; 
        } 
 
        /* ================================================= 
           CARD HEADING
           ================================================= */ 
 
        .admin-feed-card h3 { 
            margin: 0 0 18px; 
            padding-bottom: 13px; 
            color: #176b3a; 
            font-size: 22px; 
            border-bottom: 1px solid #e3d39a; 
        } 
 
        /* ================================================= 
           CARD INFORMATION
           ================================================= */ 
 
        .admin-feed-card p { 
            margin: 10px 0; 
            color: #444; 
            font-size: 15px; 
            line-height: 1.6; 
        } 
 
        .admin-feed-card p strong { 
            color: #176b3a; 
        } 
 
        /* ================================================= 
           IMAGE LABEL
           ================================================= */ 
 
        .media-label { 
            margin-top: 20px !important; 
            color: #176b3a !important; 
            font-weight: 600; 
        } 
 
        /* ================================================= 
           IMAGE CAROUSEL
           ================================================= */ 
 
        body.admin-dashboard-page .carousel {
            width: 100%; 
            max-width: 700px; 
            margin: 18px auto 8px; 
            position: relative; 
            overflow: hidden; 
            background: #111; 
            border: 2px solid #c9a227; 
            border-radius: 14px; 
            cursor: grab; 
            touch-action: pan-y; 
            user-select: none; 
        } 
 
        body.admin-dashboard-page .carousel:active {
            cursor: grabbing; 
        } 
 
        body.admin-dashboard-page .carousel-track {
            display: flex; 
            width: 100%; 
            transition: transform 0.4s ease; 
            will-change: transform; 
        } 
 
        body.admin-dashboard-page .carousel-slide {
            min-width: 100%; 
            width: 100%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background: #111; 
        } 
 
    body.admin-dashboard-page .carousel-slide img {
            display: block; 
            width: 100%; 
            height: 420px; 
            object-fit: contain; 
            background: #111; 
            pointer-events: none; 
        } 
 
        /* ================================================= 
           IMAGE COUNTER
           ================================================= */ 
 
        body.admin-dashboard-page .image-count {
            width: 100%; 
            max-width: 700px; 
            margin: 8px auto 18px; 
            text-align: center; 
            color: #777; 
            font-size: 13px; 
            font-weight: bold; 
        } 
 
        /* ================================================= 
           VIDEO
           ================================================= */ 
 body.admin-dashboard-page .post-video {
        
            display: block; 
            width: 100%; 
            max-width: 700px; 
            margin: 18px auto; 
            background: #000; 
            border: 2px solid #c9a227; 
            border-radius: 14px; 
        } 
 
        /* ================================================= 
           UPDATE BUTTON
           ================================================= */ 
 
        body.admin-dashboard-page .update-button {
            display: inline-block; 
            margin-top: 18px; 
            padding: 11px 22px; 
            background: #176b3a; 
            color: #ffffff; 
            border: 2px solid #c9a227; 
            border-radius: 8px; 
            text-decoration: none; 
            font-size: 15px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: all 0.25s ease; 
        } 
 
        body.admin-dashboard-page .update-button:hover {
            background: #c9a227; 
            color: #176b3a; 
            transform: translateY(-2px); 
        } 
 
        /* ================================================= 
           STATUS
           ================================================= */ 
 
        body.admin-dashboard-page .status-text {
            display: inline-block; 
            padding: 4px 10px; 
            margin-left: 5px; 
            background: #edf7f0; 
            color: #176b3a !important; 
            border: 1px solid #b8d8c2; 
            border-radius: 20px; 
            font-weight: bold; 
        } 
 
        /* ================================================= 
           NO DATA
           ================================================= */ 
 
        .no-data { 
            width: 92%; 
            max-width: 900px; 
            margin: 30px auto; 
            padding: 30px; 
            text-align: center; 
            background: #ffffff; 
            border: 2px solid #c9a227; 
            border-radius: 15px; 
            color: #666; 
        } 
 
        /* ================================================= 
           MOBILE RESPONSIVE
           ================================================= */ 
 
        @media (max-width: 700px) { 
 
            .admin-dashboard-header { 
                width: 94%; 
                padding: 18px; 
                flex-direction: column; 
                text-align: center; 
                gap: 12px; 
            } 
 
            .admin-dashboard-logo { 
                width: 68px; 
                height: 68px; 
            } 
 
            .admin-dashboard-title h2 { 
                font-size: 24px; 
            } 
 
            .admin-dashboard-title p { 
                font-size: 13px; 
            } 
 
            .top-logout-button { 
                width: 100%; 
                max-width: 180px; 
            } 
 
            .welcome-message { 
                width: 94%; 
                text-align: center; 
                font-size: 14px; 
            } 
 
            .dashboard-title h3 { 
                font-size: 21px; 
            } 
 
            .admin-feed-card { 
                width: 94%; 
                padding: 18px; 
                margin: 18px auto; 
                border-radius: 14px; 
            } 
 
            .admin-feed-card h3 { 
                font-size: 19px; 
            } 
 
            .admin-feed-card p { 
                font-size: 14px; 
            } 
 
            .carousel-slide img { 
                height: 300px; 
            } 
 
             body.admin-dashboard-page .update-button {
    width: 100%;
    text-align: center;
}
 
        } 
 
        /* ================================================= 
           SMALL MOBILE
           ================================================= */ 
 
        @media (max-width: 450px) { 
 
            .admin-dashboard-title h2 { 
                font-size: 21px; 
            } 
 
            .carousel-slide img { 
                height: 250px; 
            } 
 
            .admin-feed-card { 
                padding: 15px; 
            } 
 
        } 
 /* =================================================
   POSTED DATE GROUP
   ================================================= */

.admin-date-heading {
    width: 92%;
    max-width: 900px;
    margin: 35px auto 12px;
    padding: 12px 18px;
    background: #176b3a;
    color: #ffffff;
    border-left: 5px solid #c9a227;
    border-radius: 10px;
    font-size: 18px;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.admin-date-heading:first-of-type {
    margin-top: 10px;
}
/* ================================================= */
/* FIX ADMIN HEADER BUTTONS */
/* ================================================= */




/* BACK TO HOME */

body.admin-dashboard-page .admin-header-actions .admin-home-btn {

    position: static !important;

    display: inline-flex !important;

    align-items: center !important;

    justify-content: center !important;

    width: auto !important;

    min-width: 140px !important;

    white-space: nowrap !important;

    background: #ffffff !important;

    color: #1b5e20 !important;

    text-decoration: none !important;

    padding: 10px 18px !important;

    border-radius: 25px !important;

    font-weight: 700 !important;

    border: 2px solid #d4af37 !important;

    box-shadow: 0 4px 12px rgba(0,0,0,0.20) !important;

    box-sizing: border-box !important;

}


/* LOGOUT */

body.admin-dashboard-page .admin-header-actions .admin-logout-btn {

    position: static !important;

    display: inline-flex !important;

    align-items: center !important;

    justify-content: center !important;

    width: auto !important;

    min-width: 100px !important;

    white-space: nowrap !important;

    background: #ffffff !important;

    color: #b71c1c !important;

    text-decoration: none !important;

    padding: 10px 18px !important;

    border-radius: 25px !important;

    font-weight: 700 !important;

    border: 2px solid #d4af37 !important;

    box-shadow: 0 4px 12px rgba(0,0,0,0.20) !important;

    box-sizing: border-box !important;

}


/* HOVER */

body.admin-dashboard-page .admin-header-actions a:hover {

    background: #fff8e1 !important;

    transform: translateY(-2px);

}


/* ================================================= */
/* MOBILE */
/* ================================================= */

@media (max-width: 768px) {

    body.admin-dashboard-page .admin-header-actions {

        top: 15px !important;

        right: 15px !important;

        gap: 6px !important;

    }


    body.admin-dashboard-page .admin-header-actions .admin-home-btn,
    body.admin-dashboard-page .admin-header-actions .admin-logout-btn {

        min-width: auto !important;

        padding: 8px 12px !important;

        font-size: 12px !important;

    }

}
    </style> 
 
</head> 
 
<body class="admin-dashboard-page">
 
<!-- ================================================= 
     DASHBOARD HEADER 
     ================================================= --> 
 
<!-- =================================================
     ADMIN DASHBOARD HEADER
     ================================================= -->

<div class="admin-dashboard-header"
     style="
        width:92%;
        max-width:1150px;
        margin:30px auto 20px;
        padding:20px 25px;
        background:#ffffff;
        border:2px solid #c9a227;
        border-radius:18px;
        display:grid !important;
        grid-template-columns:78px 1fr auto;
        align-items:center;
        column-gap:20px;
        box-sizing:border-box;
        box-shadow:0 8px 25px rgba(0,0,0,.08);
     ">

    <!-- LOGO -->

    <div class="admin-dashboard-logo"
         style="
            width:78px;
            height:78px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:#ffffff;
            border:3px solid #c9a227;
            border-radius:50%;
            padding:6px;
            overflow:hidden;
            box-sizing:border-box;
         ">

        <img src="logo.jpg"
             alt="CivicVoice Logo"
             style="
                width:100%;
                height:100%;
                object-fit:contain;
                display:block;
             ">

    </div>


    <!-- TITLE -->

    <div class="admin-dashboard-title"
         style="
            min-width:0;
            margin:0;
         ">

        <h2 style="
            margin:0 0 6px;
            color:#176b3a;
            font-size:30px;
            font-weight:700;
        ">
            CivicVoice Admin Dashboard
        </h2>

        <p style="
            margin:0;
            color:#777;
            font-size:14px;
        ">
            Manage civic complaints and citizen posts
        </p>

    </div>


    <!-- BUTTONS -->

    <div class="admin-header-actions"
         style="
            display:flex !important;
            align-items:center;
            justify-content:flex-end;
            gap:10px;
            margin:0;
            padding:0;
            position:static !important;
            transform:none !important;
         ">

        <a href="index.php"
           class="admin-home-btn"
           style="
                display:inline-flex !important;
                align-items:center;
                justify-content:center;
                height:44px;
                padding:0 18px;
                background:#ffffff;
                color:#176b3a !important;
                border:2px solid #c9a227;
                border-radius:25px;
                font-size:14px;
                font-weight:bold;
                text-decoration:none !important;
                white-space:nowrap;
                box-sizing:border-box;
           ">
            🏠 Back to Home
        </a>


        <a href="admin_logout.php"
           class="admin-logout-btn"
           style="
                display:inline-flex !important;
                align-items:center;
                justify-content:center;
                height:44px;
                padding:0 18px;
                background:#ffffff;
                color:#b22222 !important;
                border:2px solid #c9a227;
                border-radius:25px;
                font-size:14px;
                font-weight:bold;
                text-decoration:none !important;
                white-space:nowrap;
                box-sizing:border-box;
           ">
            🚪 Logout
        </a>

    </div>

</div>


<!-- =================================================
     WELCOME
     ================================================= -->

<div class="welcome-message">

    Welcome,

    <strong>
        <?php echo $_SESSION["admin_name"]; ?>
    </strong>

</div>
<!-- =================================================
     WELCOME
     ================================================= -->

<div class="welcome-message">
    Welcome, 
 
    <strong> 
        <?php echo $_SESSION["admin_name"]; ?> 
    </strong> 
 
</div> 
 
 
<!-- ================================================= 
     PAGE TITLE 
     ================================================= --> 
 
<div class="dashboard-title"> 
 
    <h3> 
        📋 CivicVoice Posts & Complaints 
    </h3> 
 
    <div class="title-line"></div> 
 
</div> 
 
 
<?php 
 
/* ================================================= 
   CHECK DATA 
   ================================================= */ 
 
if (mysqli_num_rows($result) > 0) {

    $current_date = "";

    while ($row = mysqli_fetch_assoc($result)) {

        /* =================================================
           POSTED DATE GROUP
           ================================================= */

        $posted_date = date(
            "Y-m-d",
            strtotime($row["created_at"])
        );

        if ($posted_date != $current_date) {

            $current_date = $posted_date;

            $display_date = date(
                "d F Y",
                strtotime($row["created_at"])
            );

            ?>

            <div class="admin-date-heading">

                📅 <?php echo $display_date; ?>

            </div>

            <?php
        }

?>
 
<div class="admin-feed-card">
<?php 
 
/* ================================================= 
   COMPLAINT OR POST 
   ================================================= */ 
 
if ($row["feed_type"] == "Complaint") { 
 
?> 
 
<h3> 
    🚨 Civic Complaint 
</h3> 
 
<p> 
    🆔 
    <strong>Complaint ID:</strong> 
    <?php echo $row["feed_id"]; ?> 
</p> 
 
<?php 
 
} else { 
 
?> 
 
<h3> 
    📢 Civic Post 
</h3> 
 
<p> 
    🆔 
    <strong>Post ID:</strong> 
    <?php echo $row["feed_id"]; ?> 
</p> 
 
<?php 
 
} 
 
?> 
 
 
<p> 
    👤 
    <strong>Citizen ID:</strong> 
    <?php echo $row["citizen_id"]; ?> 
</p> 
 
 
<p> 
    📍 
    <strong>Constituency:</strong> 
    <?php echo $row["constituency_name"]; ?> 
</p> 
 
 
<p> 
    🏘️ 
    <strong>Ward Number:</strong> 
    <?php echo $row["ward_number"]; ?> 
</p> 
 
 
<p> 
    📌 
    <strong>Area:</strong> 
    <?php echo $row["area_name"]; ?> 
</p> 
 
 
<?php 
 
/* ================================================= 
   PIN CODE 
   ================================================= */ 
 
if ($row["feed_type"] == "Complaint") { 
 
?> 
 
<p> 
    📮 
    <strong>PIN Code:</strong> 
    <?php echo $row["pincode"]; ?> 
</p> 
 
<?php 
 
} 
 
?> 
 
 
<p> 
    🏛️ 
    <strong>Department:</strong> 
    <?php echo $row["department_name"]; ?> 
</p> 
 
 
<p> 
    📝 
    <strong>Issue:</strong> 
    <?php echo $row["issue_description"]; ?> 
</p> 
 
 
<?php 
 
/* ================================================= 
   COMPLAINT IMAGES 
   ================================================= */ 
 
if ($row["feed_type"] == "Complaint") { 
 
?> 
 
<p class="media-label"> 
    📷 <strong>Evidence Images:</strong> 
</p> 
 
<?php 
 
$image_sql = "SELECT image_path 
              FROM complaint_images 
              WHERE complaint_id='" . $row["feed_id"] . "' 
              ORDER BY image_id ASC"; 
 
$image_result = mysqli_query($conn, $image_sql); 
 
$complaint_images = []; 
 
if ($image_result && mysqli_num_rows($image_result) > 0) { 
 
    while ($image_row = mysqli_fetch_assoc($image_result)) { 
 
        $complaint_images[] = $image_row["image_path"]; 
 
    } 
 
} 
 
if ( 
    count($complaint_images) == 0 && 
    !empty($row["issue_image"]) 
) { 
 
    $complaint_images[] = $row["issue_image"]; 
 
} 
 
 
if (count($complaint_images) > 0) { 
 
    $carousel_id = "complaint_" . $row["feed_id"]; 
 
?> 
 
<div 
    class="carousel" 
    id="<?php echo $carousel_id; ?>" 
> 
 
    <div 
        class="carousel-track" 
        id="<?php echo $carousel_id; ?>_track" 
    > 
 
<?php 
 
foreach ($complaint_images as $image) { 
 
?> 
 
        <div class="carousel-slide"> 
 
            <img 
                src="<?php echo $image; ?>" 
                draggable="false" 
                alt="Complaint Evidence" 
            > 
 
        </div> 
 
<?php 
 
} 
 
?> 
 
    </div> 
 
</div> 
 
<?php 
 
if (count($complaint_images) > 1) { 
 
?> 
 
<div 
    class="image-count" 
    id="<?php echo $carousel_id; ?>_count" 
> 
 
    1 / <?php echo count($complaint_images); ?> 
 
</div> 
 
<script> 
 
createSlider( 
    "<?php echo $carousel_id; ?>", 
    <?php echo count($complaint_images); ?> 
); 
 
</script> 
 
<?php 
 
} 
 
} else { 
 
?> 
 
<p> 
    No evidence image available. 
</p> 
 
<?php 
 
} 
 
} 
 
 
/* ================================================= 
   CIVIC POST IMAGES 
   ================================================= */ 
 
if ($row["feed_type"] == "Post") { 
 
    $actual_post_id = substr($row["feed_id"], 1); 
 
    $image_sql = "SELECT image_path 
                  FROM civic_post_images 
                  WHERE post_id='$actual_post_id' 
                  ORDER BY image_id ASC"; 
 
    $image_result = mysqli_query($conn, $image_sql); 
 
    $post_images = []; 
 
    if ($image_result && mysqli_num_rows($image_result) > 0) { 
 
        while ($image_row = mysqli_fetch_assoc($image_result)) { 
 
            $post_images[] = $image_row["image_path"]; 
 
        } 
 
    } 
 
    if ( 
        count($post_images) == 0 && 
        !empty($row["issue_image"]) 
    ) { 
 
        $post_images[] = $row["issue_image"]; 
 
    } 
 
 
    if (count($post_images) > 0) { 
 
        $carousel_id = "post_" . $actual_post_id; 
 
?> 
 
<p class="media-label"> 
    📷 <strong>Post Images:</strong> 
</p> 
 
<div 
    class="carousel" 
    id="<?php echo $carousel_id; ?>" 
> 
 
    <div 
        class="carousel-track" 
        id="<?php echo $carousel_id; ?>_track" 
    > 
 
<?php 
 
foreach ($post_images as $image) { 
 
?> 
 
        <div class="carousel-slide"> 
 
            <img 
                src="<?php echo $image; ?>" 
                draggable="false" 
                alt="Civic Post Image" 
            > 
 
        </div> 
 
<?php 
 
} 
 
?> 
 
    </div> 
 
</div> 
 
<?php 
 
if (count($post_images) > 1) { 
 
?> 
 
<div 
    class="image-count" 
    id="<?php echo $carousel_id; ?>_count" 
> 
 
    1 / <?php echo count($post_images); ?> 
 
</div> 
 
<script> 
 
createSlider( 
    "<?php echo $carousel_id; ?>", 
    <?php echo count($post_images); ?> 
); 
 
</script> 
 
<?php 
 
} 
 
} 
 
 
/* ================================================= 
   VIDEO 
   ================================================= */ 
 
if (!empty($row["post_video"])) { 
 
?> 
 
<p class="media-label"> 
    🎥 <strong>Video / Reel:</strong> 
</p> 
 
<video 
    class="post-video" 
    controls 
> 
 
    <source 
        src="<?php echo $row["post_video"]; ?>" 
        type="video/mp4" 
    > 
 
    Your browser does not support video playback. 
 
</video> 
 
<?php 
 
} 
 
} 
 
 
/* ================================================= 
   STATUS 
   ================================================= */ 
 
?> 
 
<p> 
 
    📌 
 
    <strong>Status:</strong> 
 
    <span class="status-text"> 
        <?php echo $row["status"]; ?> 
    </span> 
 
</p> 
 
 
<p> 
 
    📅 
 
    <strong>Posted On:</strong> 
 
    <?php echo $row["created_at"]; ?> 
 
</p> 
 
 

 
</div> 
 
<?php 
 
    } 
 
} else { 
 
?> 
 
<div class="no-data"> 
 
    No civic posts or complaints found. 
 
</div> 
 
<?php 
 
} 
 
?> 
 
 
<script> 
 
/* ================================================= 
   SLIDER SYSTEM 
   ================================================= */ 
 
var sliders = {}; 
 
 
/* ================================================= 
   CREATE SLIDER 
   ================================================= */ 
 
function createSlider(id, total) { 
 
    var carousel = 
        document.getElementById(id); 
 
    var track = 
        document.getElementById( 
            id + "_track" 
        ); 
 
    if (!carousel || !track) { 
        return; 
    } 
 
    sliders[id] = { 
 
        current: 0, 
 
        total: total, 
 
        startX: 0, 
 
        endX: 0, 
 
        dragging: false 
 
    }; 
 
 
    /* ================================================= 
       START DRAG 
       ================================================= */ 
 
    carousel.addEventListener( 
        "pointerdown", 
        function(event) { 
 
            sliders[id].startX = 
                event.clientX; 
 
            sliders[id].dragging = 
                true; 
 
            track.style.transition = 
                "none"; 
 
            carousel.setPointerCapture( 
                event.pointerId 
            ); 
 
        } 
    ); 
 
 
    /* ================================================= 
       DRAG IMAGE 
       ================================================= */ 
 
    carousel.addEventListener( 
        "pointermove", 
        function(event) { 
 
            if (!sliders[id].dragging) { 
                return; 
            } 
 
            var currentX = 
                event.clientX; 
 
            var difference = 
                currentX - 
                sliders[id].startX; 
 
            var percentage = 
                ( 
                    difference / 
                    carousel.offsetWidth 
                ) * 100; 
 
            var position = 
                ( 
                    sliders[id].current * 
                    -100 
                ) + percentage; 
 
            track.style.transform = 
                "translateX(" + 
                position + 
                "%)"; 
 
        } 
    ); 
 
 
    /* ================================================= 
       END DRAG 
       ================================================= */ 
 
    carousel.addEventListener( 
        "pointerup", 
        function(event) { 
 
            if (!sliders[id].dragging) { 
                return; 
            } 
 
            sliders[id].dragging = 
                false; 
 
            sliders[id].endX = 
                event.clientX; 
 
            var difference = 
                sliders[id].endX - 
                sliders[id].startX; 
 
            track.style.transition = 
                "transform 0.4s ease"; 
 
 
            if (difference < -50) { 
 
                nextSlide(id); 
 
            } 
 
            else if (difference > 50) { 
 
                previousSlide(id); 
 
            } 
 
            else { 
 
                showSlide(id); 
 
            } 
 
        } 
    ); 
 
 
    /* ================================================= 
       CANCEL DRAG 
       ================================================= */ 
 
    carousel.addEventListener( 
        "pointercancel", 
        function() { 
 
            sliders[id].dragging = 
                false; 
 
            track.style.transition = 
                "transform 0.4s ease"; 
 
            showSlide(id); 
 
        } 
    ); 
 
} 
 
 
/* ================================================= 
   SHOW SLIDE 
   ================================================= */ 
 
function showSlide(id) { 
 
    var slider = 
        sliders[id]; 
 
    var track = 
        document.getElementById( 
            id + "_track" 
        ); 
 
    var count = 
        document.getElementById( 
            id + "_count" 
        ); 
 
    if (!slider || !track) { 
        return; 
    } 
 
    track.style.transform = 
        "translateX(-" + 
        ( 
            slider.current * 100 
        ) + 
        "%)"; 
 
 
    if (count) { 
 
        count.innerHTML = 
            ( 
                slider.current + 1 
            ) + 
            " / " + 
            slider.total; 
 
    } 
 
} 
 
 
/* ================================================= 
   NEXT SLIDE 
   ================================================= */ 
 
function nextSlide(id) { 
 
    var slider = 
        sliders[id]; 
 
    if (!slider) { 
        return; 
    } 
 
    slider.current++; 
 
    if ( 
        slider.current >= 
        slider.total 
    ) { 
 
        slider.current = 0; 
 
    } 
 
    showSlide(id); 
 
} 
 
 
/* ================================================= 
   PREVIOUS SLIDE 
   ================================================= */ 
 
function previousSlide(id) { 
 
    var slider = 
        sliders[id]; 
 
    if (!slider) { 
        return; 
    } 
 
    slider.current--; 
 
    if ( 
        slider.current < 0 
    ) { 
 
        slider.current = 
            slider.total - 1; 
 
    } 
 
    showSlide(id); 
 
} 
 
</script> 
 
</body> 
</html>