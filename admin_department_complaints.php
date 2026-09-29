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
/* GET DEPARTMENT ID */
/* ================================================= */

if (!isset($_GET["department_id"]) || !is_numeric($_GET["department_id"])) {
    header("Location: admin_dashboard.php");
    exit;
}

$department_id = (int) $_GET["department_id"];
/* ================================================= */
/* SEARCH COMPLAINTS */
/* ================================================= */

$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

$search_safe = mysqli_real_escape_string($conn, $search);

/* ================================================= */
/* GET DEPARTMENT DETAILS */
/* ================================================= */

$department_sql = "SELECT department_id, department_name
                   FROM departments
                   WHERE department_id = '$department_id'
                   LIMIT 1";

$department_result = mysqli_query($conn, $department_sql);

if (!$department_result || mysqli_num_rows($department_result) == 0) {
    header("Location: admin_dashboard.php");
    exit;
}

$department = mysqli_fetch_assoc($department_result);

$department_name = $department["department_name"];


/* ================================================= */
/* GET COMPLAINTS FOR THIS DEPARTMENT */
/* ================================================= */

$complaint_sql = "SELECT
                    complaints.complaint_id,
                    complaints.citizen_id,
                    citizens.ssn,
                    complaints.constituency_name,
                    complaints.ward_number,
                    complaints.area_name,
                    complaints.pincode,
                    complaints.issue_description,
                    complaints.issue_image,
                    complaints.status,
                    complaints.created_at,
                    complaints.resolution_remarks,
                    complaints.resolved_at,
                    departments.department_name
                  FROM complaints
                  INNER JOIN departments
                  ON complaints.department_id = departments.department_id
                  INNER JOIN citizens
                  ON complaints.citizen_id = citizens.citizen_id
                  WHERE complaints.department_id = '$department_id'";

if ($search != "") {

    $complaint_sql .= " AND (
        complaints.area_name LIKE '%$search_safe%'
        OR complaints.constituency_name LIKE '%$search_safe%'
    )";
}

$complaint_sql .= " ORDER BY complaints.created_at DESC";

$complaint_result = mysqli_query($conn, $complaint_sql);

if (!$complaint_result) {
    die("Database query failed: " . mysqli_error($conn));
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($department_name); ?>
        - CivicVoice Admin
    </title>

    <link rel="stylesheet" href="style.css">


    <style>

        /* =================================================
           PAGE
           ================================================= */

        body.admin-department-page * {
            box-sizing: border-box;
        }

        body.admin-department-page {

            margin: 0;

            padding: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f4f8f5;

            color: #222;
        }


        /* =================================================
           HEADER
           ================================================= */

        .department-header {

            width: 92%;

            max-width: 1150px;

            margin: 30px auto 20px;

            padding: 20px 25px;

            background: #ffffff;

            border: 2px solid #c9a227;

            border-radius: 18px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            box-shadow:
                0 8px 25px rgba(0,0,0,0.08);
        }


        /* =================================================
           HEADER TITLE
           ================================================= */

        .department-header-title {

            flex: 1;

            min-width: 0;
        }

        .department-header-title h2 {

            margin: 0 0 6px;

            color: #176b3a;

            font-size: 28px;

            font-weight: 700;
        }

        .department-header-title p {

            margin: 0;

            color: #777;

            font-size: 14px;
        }


        /* =================================================
           BACK BUTTON
           ================================================= */

        .back-departments {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-width: 190px;

            padding: 11px 18px;

            background: #ffffff;

            color: #176b3a;

            border: 2px solid #c9a227;

            border-radius: 25px;

            font-size: 14px;

            font-weight: bold;

            text-decoration: none;

            white-space: nowrap;

            transition: all 0.25s ease;
        }

        .back-departments:hover {

            background: #fff8e1;

            transform: translateY(-2px);

            box-shadow:
                0 5px 12px rgba(0,0,0,0.10);
        }


        /* =================================================
           DEPARTMENT TITLE
           ================================================= */

        .page-title {

            width: 92%;

            max-width: 1150px;

            margin: 25px auto;

            text-align: center;
        }

        .page-title h3 {

            margin: 0;

            color: #176b3a;

            font-size: 25px;
        }

        .page-title .title-line {

            width: 90px;

            height: 3px;

            margin: 10px auto 0;

            background: #c9a227;

            border-radius: 10px;
        }


        /* =================================================
           COMPLAINT CARD
           ================================================= */

        .complaint-card {

            width: 92%;

            max-width: 900px;

            margin: 22px auto;

            padding: 25px;

            background: #ffffff;

            border: 2px solid #c9a227;

            border-radius: 18px;

            box-shadow:
                0 7px 22px rgba(0,0,0,0.08);

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease;
        }

        .complaint-card:hover {

            transform: translateY(-2px);

            box-shadow:
                0 12px 30px rgba(0,0,0,0.12);
        }


        /* =================================================
           COMPLAINT TITLE
           ================================================= */

        .complaint-card h3 {

            margin: 0 0 18px;

            padding-bottom: 13px;

            color: #176b3a;

            font-size: 22px;

            border-bottom:
                1px solid #e3d39a;
        }


        /* =================================================
           INFORMATION
           ================================================= */

        .complaint-card p {

            margin: 10px 0;

            color: #444;

            font-size: 15px;

            line-height: 1.6;
        }

        .complaint-card p strong {

            color: #176b3a;
        }


        /* =================================================
           STATUS
           ================================================= */

        .status-text {

            display: inline-block;

            padding: 5px 12px;

            margin-left: 5px;

            background: #edf7f0;

            color: #176b3a;

            border: 1px solid #b8d8c2;

            border-radius: 20px;

            font-weight: bold;

            font-size: 13px;
        }


        /* =================================================
           IMAGE LABEL
           ================================================= */

        .media-label {

            margin-top: 22px !important;

            color: #176b3a !important;

            font-weight: 600;
        }


        /* =================================================
   COMPLAINT IMAGE CAROUSEL
   ================================================= */

.complaint-images {
    position: relative;
    width: 100%;
    height: 320px;
    margin-top: 12px;
    overflow: hidden;
    background: white;
    border-radius: 12px;
    border: 2px solid #c9a227;
}


.complaint-image-track {

    display: flex;

    width: 100%;

    height: 100%;

    transition: transform 0.3s ease;
}

.complaint-image {
    min-width: 100%;
    width: 100%;
    height: 100%;
    object-fit: contain;
    background: white;
    display: block;
    flex-shrink: 0;
}


/* =================================================
   CAROUSEL BUTTONS
   ================================================= */

.complaint-image-prev,
.complaint-image-next {

    position: absolute;

    top: 50%;

    transform: translateY(-50%);

    width: 42px;

    height: 42px;

    border: none;

    border-radius: 50%;

    background: rgba(0, 0, 0, 0.60);

    color: white;

    font-size: 26px;

    font-weight: bold;

    cursor: pointer;

    z-index: 5;
}


.complaint-image-prev {

    left: 10px;
}


.complaint-image-next {

    right: 10px;
}


.complaint-image-prev:hover,
.complaint-image-next:hover {

    background: rgba(23, 107, 58, 0.95);
}


/* =================================================
   IMAGE COUNTER
   ================================================= */

.complaint-image-counter {

    position: absolute;

    bottom: 10px;

    left: 50%;

    transform: translateX(-50%);

    padding: 5px 12px;

    background: rgba(0, 0, 0, 0.70);

    color: white;

    border-radius: 15px;

    font-size: 13px;

    z-index: 5;
}

        /* =================================================
           RESOLUTION INFORMATION
           ================================================= */

        .resolution-box {

            margin-top: 20px;

            padding: 15px;

            background: #f4f8f5;

            border-left: 4px solid #176b3a;

            border-radius: 8px;
        }

        .resolution-box p {

            margin: 7px 0;
        }


        /* =================================================
           NO DATA
           ================================================= */

        .no-complaints {

            width: 92%;

            max-width: 900px;

            margin: 35px auto;

            padding: 35px;

            text-align: center;

            background: #ffffff;

            border: 2px solid #c9a227;

            border-radius: 15px;

            color: #666;

            font-size: 16px;
        }


        /* =================================================
           MOBILE
           ================================================= */

        @media (max-width: 700px) {

            .department-header {

                width: 94%;

                padding: 18px;

                flex-direction: column;

                text-align: center;
            }


            .department-header-title h2 {

                font-size: 23px;
            }


            .back-departments {

                width: 100%;

                min-width: 0;
            }


            .page-title h3 {

                font-size: 21px;
            }


            .complaint-card {

                width: 94%;

                padding: 18px;

                border-radius: 14px;
            }


            .complaint-card h3 {

                font-size: 19px;
            }


            .complaint-card p {

                font-size: 14px;
            }


           .complaint-images {

    height: 280px;
}
        }


        /* =================================================
           SMALL MOBILE
           ================================================= */

        @media (max-width: 450px) {

            .complaint-card {

                padding: 15px;
            }


            .complaint-image {

                height: 240px;
            }

        }
/* =================================================
   DATE-WISE COMPLAINT GROUP
================================================= */

.admin-complaint-date {
    width: 92%;
    max-width: 900px;

    margin: 30px auto 10px;
    padding: 12px 18px;

    background: #176b3a;
    color: #ffffff;

    border-left: 5px solid #c9a227;
    border-radius: 10px;

    font-size: 17px;
    font-weight: bold;

    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
  /* =================================================
   ADMIN COMPLAINT SEARCH
================================================= */

.admin-complaint-search {

    width: 92%;

    max-width: 1150px;

    margin: 0 auto 20px;

    display: flex;

    justify-content: flex-end;

    box-sizing: border-box;
}


.admin-complaint-search form {

    display: flex;

    align-items: center;

    gap: 8px;
}


.admin-complaint-search input[type="text"] {

    width: 240px;

    height: 36px;

    padding: 0 14px;

    border: 2px solid #c9a227;

    border-radius: 20px;

    background: #ffffff;

    color: #333333;

    font-size: 13px;

    outline: none;

    box-sizing: border-box;
}


.admin-complaint-search input[type="text"]:focus {

    border-color: #176b3a;

    box-shadow:
        0 0 0 3px rgba(23,107,58,0.10);
}


.admin-complaint-search button {

    height: 36px;

    padding: 0 14px;

    border: none;

    border-radius: 18px;

    background: #176b3a;

    color: #ffffff;

    font-size: 13px;

    font-weight: 700;

    cursor: pointer;

    box-sizing: border-box;
}


.admin-complaint-search button:hover {

    background: #0f542c;
}


.admin-complaint-search a {

    width: 36px;

    height: 36px;

    display: flex;

    align-items: center;

    justify-content: center;

    border: 2px solid #c9a227;

    border-radius: 50%;

    background: #ffffff;

    color: #b71c1c;

    text-decoration: none;

    font-size: 12px;

    font-weight: 700;

    box-sizing: border-box;
}


/* =================================================
   MOBILE
================================================= */

@media (max-width: 700px) {

    .admin-complaint-search {

        justify-content: center;

    }

    .admin-complaint-search form {

        width: 100%;

    }

    .admin-complaint-search input[type="text"] {

        width: 100%;

    }

}
  </style>

</head>


<body class="admin-department-page">


<!-- =================================================
     HEADER
     ================================================= -->

<div class="department-header">


    <div class="department-header-title">

        <h2>

            🏛️

            <?php

            echo htmlspecialchars(
                $department_name
            );

            ?>

        </h2>


        <p>

            Admin complaint monitoring

        </p>

    </div>


    <a
        href="admin_dashboard.php"
        class="back-departments"
    >

        ← Back to Departments

    </a>

</div>



<!-- =================================================
     PAGE TITLE
     ================================================= -->

<div class="page-title">

    <h3>

        📋 Complaints

    </h3>

    <div class="title-line"></div>

</div>

<!-- =================================================
     COMPLAINT SEARCH
     ================================================= -->

<div class="admin-complaint-search">

    <form method="GET">

        <input
            type="hidden"
            name="department_id"
            value="<?php echo (int)$department_id; ?>"
        >

        <input
            type="text"
            name="search"
            value="<?php echo htmlspecialchars($search); ?>"
            placeholder="Area or Constituency"
        >

        <button type="submit">
            🔍 Search
        </button>

        <?php if ($search != "") { ?>

            <a
                href="admin_department_complaints.php?department_id=<?php echo (int)$department_id; ?>"
                title="Clear Search"
            >
                ✖
            </a>

        <?php } ?>

    </form>

</div>

<?php

/* =================================================
   CHECK COMPLAINTS
   ================================================= */
if (mysqli_num_rows($complaint_result) > 0) {

    $current_date = "";

    while ($row = mysqli_fetch_assoc($complaint_result)) {

        $posted_date = date(
            "Y-m-d",
            strtotime($row["created_at"])
        );

        /* =================================================
           SHOW DATE HEADING WHEN DATE CHANGES
        ================================================= */

        if ($posted_date != $current_date) {

            $current_date = $posted_date;

            $display_date = date(
                "d F Y",
                strtotime($row["created_at"])
            );
?>

            <div class="admin-complaint-date">

                📅 <?php echo htmlspecialchars($display_date); ?>

            </div>

<?php
        }
?>


<!-- =================================================
     COMPLAINT CARD
     ================================================= -->

<div class="complaint-card">


    <h3>

        🚨 Civic Complaint

    </h3>


    <!-- COMPLAINT ID -->

    <p>

        🆔

        <strong>
            Complaint ID:
        </strong>

        <?php

        echo htmlspecialchars(
            $row["complaint_id"]
        );

        ?>

    </p>


    <!-- CITIZEN -->

<p>

    👤

    <strong>
        Citizen ID:
    </strong>

    <?php
    echo htmlspecialchars($row["citizen_id"]);
    ?>

</p>

<p>

    🔐

    <strong>
        SSN:
    </strong>

    <?php

    $masked_ssn = "***-**-" . substr($row["ssn"], -4);

    echo htmlspecialchars($masked_ssn);

    ?>

</p>


    <!-- CONSTITUENCY -->

    <p>

        📍

        <strong>
            Constituency:
        </strong>

        <?php

        echo htmlspecialchars(
            $row["constituency_name"]
        );

        ?>

    </p>


    <!-- WARD -->

    <p>

        🏘️

        <strong>
            Ward Number:
        </strong>

        <?php

        echo htmlspecialchars(
            $row["ward_number"]
        );

        ?>

    </p>


    <!-- AREA -->

    <p>

        📌

        <strong>
            Area:
        </strong>

        <?php

        echo htmlspecialchars(
            $row["area_name"]
        );

        ?>

    </p>


    <!-- PINCODE -->

    <p>

        📮

        <strong>
            PIN Code:
        </strong>

        <?php

        echo htmlspecialchars(
            $row["pincode"]
        );

        ?>

    </p>


    <!-- DEPARTMENT -->

    <p>

        🏛️

        <strong>
            Department:
        </strong>

        <?php

        echo htmlspecialchars(
            $row["department_name"]
        );

        ?>

    </p>


    <!-- ISSUE -->

    <p>

        📝

        <strong>
            Issue:
        </strong>

        <?php

        echo nl2br(
            htmlspecialchars(
                $row["issue_description"]
            )
        );

        ?>

    </p>



<?php

/* =================================================
   COMPLAINT IMAGES
   ================================================= */

$image_sql = "SELECT image_path
              FROM complaint_images
              WHERE complaint_id='" .
              mysqli_real_escape_string(
                  $conn,
                  $row["complaint_id"]
              ) .
              "'
              ORDER BY image_id ASC";

$image_result = mysqli_query(
    $conn,
    $image_sql
);

$complaint_images = [];


if (
    $image_result &&
    mysqli_num_rows($image_result) > 0
) {

    while (
        $image_row =
        mysqli_fetch_assoc($image_result)
    ) {

        if (
            !empty(
                $image_row["image_path"]
            )
        ) {

            $complaint_images[] =
                $image_row["image_path"];

        }

    }

}


if (count($complaint_images) > 0) {

?>

    <p class="media-label">

        📷

        <strong>
            Evidence Images:
        </strong>

    </p>


    <div
        class="complaint-images"
        data-current-index="0"
    >

        <div class="complaint-image-track">

<?php

    foreach (
        $complaint_images
        as $image
    ) {

?>

            <img
                src="<?php echo htmlspecialchars($image); ?>"
                class="complaint-image"
                alt="Complaint Evidence"
            >

<?php

    }

?>

        </div>


<?php

    if (count($complaint_images) > 1) {

?>

        <button
            type="button"
            class="complaint-image-prev"
            onclick="moveAdminComplaintImage(this, -1)"
        >
            ‹
        </button>


        <button
            type="button"
            class="complaint-image-next"
            onclick="moveAdminComplaintImage(this, 1)"
        >
            ›
        </button>


        <div class="complaint-image-counter">

            <span>1</span>
            /
            <?php echo count($complaint_images); ?>

        </div>

<?php

    }

?>

    </div>

<?php

} else {

?>

?>

    <p>

        📷

        <strong>
            Evidence Images:
        </strong>

        No evidence image available.

    </p>

<?php

}


/* =================================================
   STATUS
   ================================================= */

?>

    <p>

        📌

        <strong>
            Status:
        </strong>

        <span class="status-text">

            <?php

            echo htmlspecialchars(
                $row["status"]
            );

            ?>

        </span>

    </p>


    <!-- SUBMITTED DATE -->

    <p>

        📅

        <strong>
            Submitted On:
        </strong>

        <?php

        echo htmlspecialchars(
            $row["created_at"]
        );

        ?>

    </p>


<?php

/* =================================================
   RESOLUTION INFORMATION
   ================================================= */

if (
    !empty($row["resolution_remarks"]) ||
    !empty($row["resolved_at"])
) {

?>

    <div class="resolution-box">

        <p>

            🔧

            <strong>
                Resolution Remarks:
            </strong>

            <?php

            echo !empty(
                $row["resolution_remarks"]
            )
                ? nl2br(
                    htmlspecialchars(
                        $row["resolution_remarks"]
                    )
                )
                : "Not available";

            ?>

        </p>


        <p>

            ✅

            <strong>
                Resolved On:
            </strong>

            <?php

            echo !empty(
                $row["resolved_at"]
            )
                ? htmlspecialchars(
                    $row["resolved_at"]
                )
                : "Not resolved yet";

            ?>

        </p>

    </div>

<?php

}

?>


</div>


<?php

    }

} else {

?>


<!-- =================================================
     NO COMPLAINTS
     ================================================= -->

<div class="no-complaints">

    📋

    <br><br>

    No complaints have been submitted
    to this department yet.

</div>


<?php

}

?>


<script>

/* =================================================
   ADMIN COMPLAINT IMAGE CAROUSEL
   ================================================= */

function moveAdminComplaintImage(button, direction) {

    const carousel =
        button.closest(".complaint-images");

    const track =
        carousel.querySelector(
            ".complaint-image-track"
        );

    const slides =
        carousel.querySelectorAll(
            ".complaint-image"
        );

    const counter =
        carousel.querySelector(
            ".complaint-image-counter span"
        );

    let currentIndex =
        parseInt(
            carousel.dataset.currentIndex || "0"
        );


    currentIndex += direction;


    if (currentIndex < 0) {

        currentIndex =
            slides.length - 1;
    }


    if (currentIndex >= slides.length) {

        currentIndex = 0;
    }


    track.style.transform =
        "translateX(-" +
        (currentIndex * 100) +
        "%)";


    carousel.dataset.currentIndex =
        currentIndex;


    if (counter) {

        counter.textContent =
            currentIndex + 1;
    }
}

</script>


</body>
</html>