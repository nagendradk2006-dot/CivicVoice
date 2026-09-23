<?php

session_start();
include "db.php";

/* ================================================= */
/* CHECK DEPARTMENT LOGIN */
/* ================================================= */

if (!isset($_SESSION["department_user_id"])) {

    header("Location: department_login.php");
    exit;
}

$department_user_id = $_SESSION["department_user_id"];
$department_name = $_SESSION["department_name"];
$department_id = $_SESSION["department_id"];


/* ================================================= */
/* IMAGE PATH HELPER */
/* ================================================= */

function getComplaintImageUrl($image_path)
{
    if (empty($image_path)) {
        return "";
    }

    $image_path = trim($image_path);

    /* Already a web URL */
    if (
        strpos($image_path, "http://") === 0 ||
        strpos($image_path, "https://") === 0 ||
        strpos($image_path, "data:image") === 0
    ) {
        return $image_path;
    }

    /* Convert Windows backslashes */
    $image_path = str_replace("\\", "/", $image_path);

    /* Remove physical XAMPP project path */
    $project_paths = array(
        "C:/xampp/htdocs/CivicVoice/",
        "C:/xampp/htdocs/CivicVoice",
        "D:/xampp/htdocs/CivicVoice/",
        "D:/xampp/htdocs/CivicVoice"
    );

    foreach ($project_paths as $project_path) {

        if (stripos($image_path, $project_path) === 0) {

            $image_path = substr(
                $image_path,
                strlen($project_path)
            );

            break;
        }
    }

    /* Remove CivicVoice/ if present */
    if (stripos($image_path, "CivicVoice/") === 0) {

        $image_path = substr(
            $image_path,
            strlen("CivicVoice/")
        );
    }

    /* Remove leading slash */
    $image_path = ltrim($image_path, "/");

    return $image_path;
}


/* ================================================= */
/* GET COMPLAINTS FOR THIS DEPARTMENT */
/* ================================================= */

$sql = "SELECT complaints.*, departments.department_name
        FROM complaints
        INNER JOIN departments
        ON complaints.department_id = departments.department_id
        WHERE complaints.department_id='$department_id'
        ORDER BY complaints.created_at DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Department Dashboard - CivicVoice
    </title>

    <link rel="stylesheet" href="style.css">


    <!-- ================================================= -->
    <!-- DEPARTMENT DASHBOARD DESIGN -->
    <!-- ================================================= -->

    <style>

        /* ================================================= */
        /* PAGE */
        /* ================================================= */

        body.department-dashboard-page {

            margin: 0;

            background:
                linear-gradient(
                    135deg,
                    #f5f8f5,
                    #ffffff
                );

            color: #222;

        }


        /* ================================================= */
        /* HEADER */
        /* ================================================= */

        .department-dashboard-header {

            position: relative;

            width: 100%;

            background:
                linear-gradient(
                    135deg,
                    #0b4d2b,
                    #1b5e20
                );

            padding: 35px 20px 30px;

            box-sizing: border-box;

            text-align: center;

            color: white;

            box-shadow:
                0 5px 18px rgba(0,0,0,0.18);

        }


        /* LOGO */

        .department-dashboard-logo {

            width: 120px !important;

            height: 120px !important;

            max-width: 120px !important;

            max-height: 120px !important;

            object-fit: contain !important;

            background: #ffffff;

            padding: 8px;

            border: 4px solid #d4af37;

            border-radius: 50%;

            box-shadow:
                0 6px 20px rgba(0,0,0,0.30);

            box-sizing: border-box;

        }


        /* TITLE */

        .department-dashboard-title {

            margin: 15px 0 3px;

            color: #ffffff;

            font-size: 32px;

            font-weight: 800;

            letter-spacing: 1px;

        }


        .department-dashboard-subtitle {

            margin: 0;

            color: #f5d76e;

            font-size: 16px;

            font-weight: 600;

            letter-spacing: 1px;

        }


        /* ================================================= */
        /* LOGOUT */
        /* ================================================= */

        .department-logout-btn {

            position: absolute;

            top: 25px;

            right: 30px;

            display: inline-block;

            background: #ffffff;

            color: #b71c1c !important;

            text-decoration: none !important;

            padding: 10px 22px;

            border-radius: 25px;

            font-weight: 700;

            border: 2px solid #d4af37;

            box-shadow:
                0 4px 12px rgba(0,0,0,0.20);

            transition: 0.2s;

        }


        .department-logout-btn:hover {

            background: #fff8e1;

            transform: translateY(-2px);

        }


        /* ================================================= */
        /* MAIN */
        /* ================================================= */

        .department-dashboard-container {

            width: 100%;

            max-width: 1100px;

            margin: 0 auto;

            padding: 35px 20px 60px;

            box-sizing: border-box;

        }


        /* ================================================= */
        /* DEPARTMENT NAME CARD */
        /* ================================================= */

        .department-welcome-card {

            width: 100%;

            max-width: 850px;

            margin: 0 auto 35px;

            background: #ffffff;

            border: 1px solid #dddddd;

            border-left: 6px solid #1b5e20;

            border-radius: 15px;

            padding: 22px 28px;

            box-sizing: border-box;

            display: flex;

            align-items: center;

            justify-content: space-between;

            box-shadow:
                0 5px 18px rgba(0,0,0,0.10);

        }


        .department-label {

            font-size: 13px;

            color: #777777;

            text-transform: uppercase;

            letter-spacing: 1px;

            margin-bottom: 6px;

            display: block;

        }


        .department-name {

            margin: 0;

            color: #1b5e20;

            font-size: 25px;

            font-weight: 700;

        }


        .department-icon {

            width: 50px;

            height: 50px;

            border-radius: 50%;

            background: #e8f5e9;

            border: 2px solid #d4af37;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 25px;

            flex-shrink: 0;

        }


        /* ================================================= */
        /* SECTION HEADING */
        /* ================================================= */

        .department-section-heading {

            width: 100%;

            text-align: center;

            margin: 0 auto 30px;

        }


        .department-section-heading h2 {

            margin: 0;

            color: #1b5e20;

            font-size: 27px;

        }


        .department-section-heading p {

            margin: 7px 0 0;

            color: #777777;

            font-size: 15px;

        }


        /* ================================================= */
        /* COMPLAINT CONTAINER */
        /* ================================================= */

        .department-complaints-container {

            width: 100%;

            display: flex;

            flex-direction: column;

            align-items: center;

            gap: 30px;

        }


        /* ================================================= */
        /* DATE HEADING */
        /* ================================================= */

        .department-date-heading {

            width: 100%;

            max-width: 850px;

            margin: 10px auto 0;

            padding: 12px 18px;

            background: #1b5e20;

            color: #ffffff;

            border-left: 5px solid #d4af37;

            border-radius: 10px;

            font-size: 18px;

            font-weight: 700;

            box-sizing: border-box;

            box-shadow:
                0 4px 12px rgba(0,0,0,0.08);

        }


        /* ================================================= */
        /* COMPLAINT CARD */
        /* ================================================= */

        .cv-complaint-card {

            width: 100%;

            max-width: 850px;

            margin: 0 auto;

            background: #ffffff;

            border: 1px solid #dddddd;

            border-top: 5px solid #d4af37;

            border-radius: 18px;

            padding: 30px;

            box-sizing: border-box;

            box-shadow:
                0 8px 25px rgba(0,0,0,0.12);

        }


        /* ================================================= */
        /* COMPLAINT TOP */
        /* ================================================= */

        .cv-card-top {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 20px;

            padding-bottom: 20px;

            border-bottom: 1px solid #eeeeee;

        }


        .cv-complaint-id {

            display: block;

            color: #555555;

            font-size: 15px;

        }


        .cv-card-top h3 {

            margin: 8px 0 0;

            color: #1b5e20;

            font-size: 22px;

            line-height: 1.4;

        }


        /* ================================================= */
        /* STATUS */
        /* ================================================= */

        .cv-status {

            display: inline-block;

            padding: 8px 15px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: 700;

            white-space: nowrap;

        }


        .status-pending {

            background: #fff3cd;

            color: #856404;

            border: 1px solid #ffe08a;

        }


        .status-progress {

            background: #e3f2fd;

            color: #1565c0;

            border: 1px solid #90caf9;

        }


        .status-resolved {

            background: #e8f5e9;

            color: #1b5e20;

            border: 1px solid #a5d6a7;

        }


        /* ================================================= */
        /* DETAILS */
        /* ================================================= */

        .cv-details {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 14px;

            margin-top: 25px;

            width: 100%;

        }


        .cv-detail-box {

            background: #f8faf8;

            border: 1px solid #e1e8e1;

            border-left: 4px solid #d4af37;

            border-radius: 10px;

            padding: 14px 16px;

            box-sizing: border-box;

        }


        .cv-detail-box span {

            display: block;

            color: #777777;

            font-size: 13px;

            font-weight: 600;

            margin-bottom: 5px;

        }


        .cv-detail-box strong {

            display: block;

            color: #1b5e20;

            font-size: 16px;

            font-weight: 700;

            line-height: 1.4;

            word-break: break-word;

        }


        /* ================================================= */
        /* DESCRIPTION */
        /* ================================================= */

        .cv-description {

            display: block !important;

            width: 100%;

            margin-top: 25px;

            padding: 20px;

            background: #f8faf8;

            border: 1px solid #dfe7df;

            border-left: 5px solid #1b5e20;

            border-radius: 12px;

            box-sizing: border-box;

            visibility: visible !important;

            opacity: 1 !important;

        }


        .cv-description h4 {

            display: block;

            margin: 0 0 10px;

            color: #1b5e20;

            font-size: 18px;

        }


        .cv-description p {

            display: block;

            margin: 0;

            color: #333333;

            font-size: 16px;

            line-height: 1.6;

            white-space: normal;

            word-break: break-word;

        }


        /* ================================================= */
        /* IMAGES */
        /* ================================================= */

        .cv-images {

            width: 100%;

            margin-top: 28px;

        }


        .cv-images h4 {

            margin: 0 0 15px;

            color: #1b1b1b;

            font-size: 18px;

        }


        .cv-image-grid {

            width: 100%;

            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 18px;

            align-items: center;

        }


        .cv-image-grid img {

            display: block;

            width: 100%;

            max-width: 100%;

            height: 300px;

            object-fit: contain;

            background: #f5f5f5;

            border: 2px solid #dddddd;

            border-radius: 12px;

            padding: 5px;

            box-sizing: border-box;

            box-shadow:
                0 4px 12px rgba(0,0,0,0.12);

        }


        /* ================================================= */
        /* UPDATE BUTTON */
        /* ================================================= */

        .cv-card-button {

            margin-top: 28px;

            padding-top: 22px;

            border-top: 1px solid #eeeeee;

            text-align: center;

        }


        .cv-card-button a {

            display: inline-block;

            padding: 12px 28px;

            background: #008f68;

            color: #ffffff !important;

            text-decoration: none !important;

            border-radius: 25px;

            font-weight: 700;

            box-shadow:
                0 5px 12px rgba(0,0,0,0.15);

            transition: 0.2s;

        }


        .cv-card-button a:hover {

            background: #006f50;

            transform: translateY(-2px);

        }


        /* ================================================= */
        /* EMPTY STATE */
        /* ================================================= */

        .department-empty-state {

            max-width: 850px;

            margin: 30px auto;

            padding: 50px 20px;

            text-align: center;

            background: #ffffff;

            border-radius: 18px;

            box-shadow:
                0 8px 25px rgba(0,0,0,0.10);

        }


        .empty-icon {

            font-size: 50px;

            margin-bottom: 10px;

        }


        /* ================================================= */
        /* FOOTER */
        /* ================================================= */

        .department-dashboard-footer {

            text-align: center;

            padding: 25px;

            background: #0b4d2b;

            color: #ffffff;

        }


        .department-dashboard-footer p {

            margin: 0;

        }


        /* ================================================= */
        /* MOBILE */
        /* ================================================= */

        @media (max-width: 768px) {

            .department-dashboard-header {

                padding: 30px 15px 25px;

            }


            .department-date-heading {

                font-size: 16px;

                padding: 11px 14px;

            }


            .department-dashboard-logo {

                width: 95px !important;

                height: 95px !important;

                max-width: 95px !important;

                max-height: 95px !important;

            }


            .department-dashboard-title {

                font-size: 27px;

            }


            .department-logout-btn {

                top: 15px;

                right: 15px;

                padding: 8px 14px;

                font-size: 13px;

            }


            .department-dashboard-container {

                padding: 25px 12px 45px;

            }


            .department-welcome-card {

                padding: 18px;

            }


            .department-name {

                font-size: 20px;

            }


            .cv-complaint-card {

                padding: 20px;

            }


            .cv-card-top {

                flex-direction: column;

            }


            .cv-details {

                grid-template-columns: 1fr 1fr;

            }


            .cv-image-grid {

                grid-template-columns: 1fr;

            }


            .cv-image-grid img {

                height: auto;

                min-height: 200px;

            }

        }


        @media (max-width: 480px) {

            .cv-details {

                grid-template-columns: 1fr;

            }


            .department-welcome-card {

                padding: 16px;

            }


            .department-icon {

                width: 42px;

                height: 42px;

                font-size: 21px;

            }


            .cv-card-top h3 {

                font-size: 19px;

            }


            .cv-image-grid img {

                min-height: 180px;

            }

        }
/* ================================================= */
/* HEADER ACTIONS */
/* ================================================= */

.department-header-actions {

    position: absolute;

    top: 25px;

    right: 30px;

    display: flex;

    align-items: center;

    gap: 10px;

}


/* ================================================= */
/* BACK TO HOME */
/* ================================================= */

.department-home-btn {

    display: inline-block;

    background: #ffffff;

    color: #1b5e20 !important;

    text-decoration: none !important;

    padding: 10px 18px;

    border-radius: 25px;

    font-weight: 700;

    border: 2px solid #d4af37;

    box-shadow:
        0 4px 12px rgba(0,0,0,0.20);

    transition: 0.2s;

}


.department-home-btn:hover {

    background: #fff8e1;

    transform: translateY(-2px);

}


/* ================================================= */
/* LOGOUT */
/* ================================================= */

.department-logout-btn {

    position: static;

    display: inline-block;

    background: #ffffff;

    color: #b71c1c !important;

    text-decoration: none !important;

    padding: 10px 18px;

    border-radius: 25px;

    font-weight: 700;

    border: 2px solid #d4af37;

    box-shadow:
        0 4px 12px rgba(0,0,0,0.20);

    transition: 0.2s;

}


.department-logout-btn:hover {

    background: #fff8e1;

    transform: translateY(-2px);

}
    </style>

</head>


<body class="department-dashboard-page">


<!-- ================================================= -->
<!-- HEADER -->
<!-- ================================================= -->

<header class="department-dashboard-header">


    <!-- LOGOUT -->
<div class="department-header-actions">

    <a href="index.php"
       class="department-home-btn">
        🏠 Back to Home
    </a>

    <a href="department_logout.php"
       class="department-logout-btn">
        🚪 Logout
    </a>

</div>


    <!-- CENTER LOGO -->

    <div>

        <img src="logo.jpg"
             alt="CivicVoice Logo"
             class="department-dashboard-logo">


        <h1 class="department-dashboard-title">
            CivicVoice
        </h1>


        <p class="department-dashboard-subtitle">
            Department Dashboard
        </p>

    </div>


</header>


<!-- ================================================= -->
<!-- MAIN -->
<!-- ================================================= -->

<main class="department-dashboard-container">


    <!-- ================================================= -->
    <!-- DEPARTMENT NAME -->
    <!-- ================================================= -->

    <section class="department-welcome-card">


        <div>

            <span class="department-label">
                Department
            </span>


            <h2 class="department-name">

                <?php
                echo htmlspecialchars($department_name);
                ?>

            </h2>

        </div>


        <div class="department-icon">

            🏢

        </div>


    </section>


    <!-- ================================================= -->
    <!-- COMPLAINT HEADING -->
    <!-- ================================================= -->

    <div class="department-section-heading">

        <h2>
            📋 Department Complaints
        </h2>

        <p>
            Complaints assigned to your department
        </p>

    </div>


    <?php

    if (mysqli_num_rows($result) > 0) {

    ?>

    <div class="department-complaints-container">

    <?php

    $current_date = "";

    while ($row = mysqli_fetch_assoc($result)) {


        /* ================================================= */
        /* POSTED DATE GROUP */
        /* ================================================= */

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

            <div class="department-date-heading">

                📅 <?php echo htmlspecialchars($display_date); ?>

            </div>

            <?php
        }


        /* ================================================= */
        /* STATUS */
        /* ================================================= */

        $status = strtolower(
            trim($row["status"])
        );


        if ($status == "resolved") {

            $status_class = "status-resolved";

        }

        elseif ($status == "in progress") {

            $status_class = "status-progress";

        }

        else {

            $status_class = "status-pending";

        }


        ?>


        <!-- ================================================= -->
        <!-- COMPLAINT CARD -->
        <!-- ================================================= -->

        <article class="cv-complaint-card">


            <!-- ================================================= -->
            <!-- TOP -->
            <!-- ================================================= -->

            <div class="cv-card-top">


                <div>

                    <span class="cv-complaint-id">

                        Complaint #

                        <?php

                        echo htmlspecialchars(
                            $row["complaint_id"]
                        );

                        ?>

                    </span>


                    <h3>

                        <?php

                        echo htmlspecialchars(
                            $row["issue_description"]
                        );

                        ?>

                    </h3>

                </div>


                <span class="cv-status <?php echo $status_class; ?>">

                    <?php

                    echo htmlspecialchars(
                        $row["status"]
                    );

                    ?>

                </span>


            </div>


            <!-- ================================================= -->
            <!-- DETAILS -->
            <!-- ================================================= -->

            <div class="cv-details">


                <!-- CITIZEN -->

                <div class="cv-detail-box">

                    <span>
                        Citizen ID
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $row["citizen_id"]
                        );

                        ?>

                    </strong>

                </div>


                <!-- CONSTITUENCY -->

                <div class="cv-detail-box">

                    <span>
                        Constituency
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $row["constituency_name"]
                        );

                        ?>

                    </strong>

                </div>


                <!-- WARD -->

                <div class="cv-detail-box">

                    <span>
                        Ward Number
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $row["ward_number"]
                        );

                        ?>

                    </strong>

                </div>


                <!-- AREA -->

                <div class="cv-detail-box">

                    <span>
                        Area
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $row["area_name"]
                        );

                        ?>

                    </strong>

                </div>


                <!-- PIN -->

                <div class="cv-detail-box">

                    <span>
                        PIN Code
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $row["pincode"]
                        );

                        ?>

                    </strong>

                </div>


                <!-- DATE -->

                <div class="cv-detail-box">

                    <span>
                        Submitted On
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $row["created_at"]
                        );

                        ?>

                    </strong>

                </div>


            </div>


            <!-- ================================================= -->
            <!-- ISSUE DESCRIPTION -->
            <!-- ================================================= -->

            <div class="cv-description">


                <h4>
                    Issue Description
                </h4>


                <p>

                    <?php

                    echo nl2br(
                        htmlspecialchars(
                            $row["issue_description"]
                        )
                    );

                    ?>

                </p>


            </div>


            <!-- ================================================= -->
            <!-- COMPLAINT IMAGES -->
            <!-- ================================================= -->

            <?php

            $image_sql = "SELECT image_path
                          FROM complaint_images
                          WHERE complaint_id='"
                          . mysqli_real_escape_string(
                              $conn,
                              $row["complaint_id"]
                          )
                          . "'
                          ORDER BY image_id ASC";


            $image_result = mysqli_query(
                $conn,
                $image_sql
            );


            if (
                $image_result &&
                mysqli_num_rows($image_result) > 0
            ) {

            ?>

            <div class="cv-images">


                <h4>
                    📷 Complaint Images
                </h4>


                <div class="cv-image-grid">


                    <?php

                    while (
                        $image =
                        mysqli_fetch_assoc(
                            $image_result
                        )
                    ) {


                        $image_url =
                            getComplaintImageUrl(
                                $image["image_path"]
                            );


                        if (!empty($image_url)) {

                    ?>


                    <img
                        src="<?php
                            echo htmlspecialchars(
                                $image_url
                            );
                        ?>"
                        alt="Complaint Image"
                    >


                    <?php

                        }

                    }

                    ?>


                </div>


            </div>


            <?php

            }

            elseif (
                !empty($row["issue_image"])
            ) {


                $image_url =
                    getComplaintImageUrl(
                        $row["issue_image"]
                    );


                if (!empty($image_url)) {

            ?>


            <div class="cv-images">


                <h4>
                    📷 Complaint Image
                </h4>


                <div class="cv-image-grid">


                    <img
                        src="<?php
                            echo htmlspecialchars(
                                $image_url
                            );
                        ?>"
                        alt="Complaint Image"
                    >


                </div>


            </div>


            <?php

                }

            }

            ?>


            <!-- ================================================= -->
            <!-- UPDATE BUTTON -->
            <!-- ================================================= -->

            <div class="cv-card-button">


                <a href="department_update_complaint.php?complaint_id=<?php
                    echo urlencode(
                        $row["complaint_id"]
                    );
                ?>">

                    📝 Update Complaint

                </a>


            </div>


        </article>


    <?php

    }

    ?>


    </div>


    <?php

    }

    else {

    ?>


    <!-- ================================================= -->
    <!-- NO COMPLAINTS -->
    <!-- ================================================= -->

    <div class="department-empty-state">


        <div class="empty-icon">
            📭
        </div>


        <h3>
            No Complaints Found
        </h3>


        <p>
            No complaints have been assigned
            to this department yet.
        </p>


    </div>


    <?php

    }

    ?>


</main>


<!-- ================================================= -->
<!-- FOOTER -->
<!-- ================================================= -->

<footer class="department-dashboard-footer">

    <p>

        © <?php echo date("Y"); ?> CivicVoice

    </p>

</footer>


</body>

</html>