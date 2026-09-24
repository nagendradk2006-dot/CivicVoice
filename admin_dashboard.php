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
/* GET ALL DEPARTMENTS */
/* ================================================= */

$department_sql = "SELECT department_id, department_name
                   FROM departments
                   ORDER BY department_id ASC";

$department_result = mysqli_query($conn, $department_sql);

if (!$department_result) {
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
           ADMIN DASHBOARD
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

            display: grid;

            grid-template-columns: 78px 1fr auto;

            align-items: center;

            column-gap: 20px;

            box-shadow:
                0 8px 25px rgba(0, 0, 0, 0.08);
        }


        /* =================================================
           LOGO
           ================================================= */

        .admin-dashboard-logo {
            width: 78px;
            height: 78px;

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
           HEADER TITLE
           ================================================= */

        .admin-dashboard-title {
            min-width: 0;
            margin: 0;
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
           HEADER BUTTONS
           ================================================= */

        .admin-header-actions {
            display: flex;

            align-items: center;
            justify-content: flex-end;

            gap: 10px;

            margin: 0;
            padding: 0;
        }

        .admin-header-actions a {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            height: 44px;

            padding: 0 18px;

            background: #ffffff;

            border: 2px solid #c9a227;
            border-radius: 25px;

            font-size: 14px;
            font-weight: bold;

            text-decoration: none;

            white-space: nowrap;

            box-sizing: border-box;

            transition: all 0.25s ease;
        }

        .admin-home-btn {
            color: #176b3a;
        }

        .admin-logout-btn {
            color: #b22222;
        }

        .admin-header-actions a:hover {
            background: #fff8e1;

            transform: translateY(-2px);

            box-shadow:
                0 5px 12px rgba(0, 0, 0, 0.10);
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

            margin: 0 auto 25px;

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
           DEPARTMENT GRID
           ================================================= */

        .department-grid {

            width: 92%;
            max-width: 1150px;

            margin: 0 auto 40px;

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;
        }


        /* =================================================
           DEPARTMENT BUTTON
           ================================================= */

        .department-button {

            min-height: 120px;

            padding: 22px 18px;

            background: #ffffff;

            border: 2px solid #c9a227;

            border-radius: 16px;

            text-decoration: none;

            color: #176b3a;

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            text-align: center;

            box-shadow:
                0 6px 18px rgba(0, 0, 0, 0.07);

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease,
                background 0.25s ease;
        }


        .department-button:hover {

            background: #fffdf4;

            transform: translateY(-4px);

            box-shadow:
                0 12px 28px rgba(0, 0, 0, 0.13);
        }


        /* =================================================
           DEPARTMENT ICON
           ================================================= */

        .department-icon {

            width: 48px;
            height: 48px;

            margin-bottom: 12px;

            display: flex;

            align-items: center;
            justify-content: center;

            background: #176b3a;

            color: #ffffff;

            border-radius: 50%;

            font-size: 23px;

            border: 2px solid #c9a227;
        }


        /* =================================================
           DEPARTMENT NAME
           ================================================= */

        .department-name {

            font-size: 17px;

            font-weight: 700;

            line-height: 1.4;

            color: #176b3a;
        }


        /* =================================================
           DEPARTMENT ID
           ================================================= */

        .department-id {

            margin-top: 6px;

            font-size: 12px;

            color: #888;
        }


        /* =================================================
           NO DEPARTMENTS
           ================================================= */

        .no-departments {

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
           MOBILE
           ================================================= */

        @media (max-width: 850px) {

            .department-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        /* =================================================
           SMALL MOBILE
           ================================================= */

        @media (max-width: 600px) {

            .admin-dashboard-header {

                width: 94%;

                padding: 18px;

                grid-template-columns: 1fr;

                text-align: center;

                row-gap: 15px;
            }


            .admin-dashboard-logo {

                width: 68px;
                height: 68px;

                margin: 0 auto;
            }


            .admin-dashboard-title h2 {

                font-size: 24px;

            }


            .admin-dashboard-title p {

                font-size: 13px;

            }


            .admin-header-actions {

                width: 100%;

                justify-content: center;

                flex-wrap: wrap;
            }


            .admin-header-actions a {

                height: 40px;

                padding: 0 14px;

                font-size: 12px;
            }


            .welcome-message {

                width: 94%;

                text-align: center;

                font-size: 14px;
            }


            .dashboard-title h3 {

                font-size: 21px;

            }


            .department-grid {

                width: 94%;

                grid-template-columns: 1fr;

                gap: 15px;
            }


            .department-button {

                min-height: 105px;

            }


            .department-name {

                font-size: 16px;

            }

        }
/* =================================================
   ATTRACTIVE MAIN VISION
   ================================================= */

.admin-vision-heading {
    width: 92%;
    max-width: 1150px;
    margin: 18px auto 32px;
    padding: 30px 20px;
    text-align: center;

    background: linear-gradient(
        135deg,
        #064e3b,
        #087f5b,
        #0b6b4f
    );

    border: 2px solid #d4af37;
    border-radius: 20px;

    box-shadow:
        0 10px 30px rgba(0, 0, 0, 0.18),
        inset 0 1px 0 rgba(255, 255, 255, 0.15);

    position: relative;
    overflow: hidden;
}

/* Decorative shine */

.admin-vision-heading::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 50%;
    height: 100%;

    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.12),
        transparent
    );

    transform: skewX(-20deg);
    animation: visionShine 4s infinite;
}

@keyframes visionShine {
    0% {
        left: -100%;
    }

    50% {
        left: 150%;
    }

    100% {
        left: 150%;
    }
}

.admin-vision-heading h1 {
    position: relative;
    z-index: 1;

    margin: 0;

    font-family: Georgia, "Times New Roman", serif;
    font-size: 38px;
    font-weight: 700;
    letter-spacing: 1px;

    color: #ffd86b;

    text-shadow:
        0 2px 4px rgba(0, 0, 0, 0.35),
        0 0 12px rgba(255, 216, 107, 0.25);
}

.admin-vision-heading .vision-line {
    position: relative;
    z-index: 1;

    width: 150px;
    height: 4px;

    margin: 14px auto 0;

    background: linear-gradient(
        90deg,
        transparent,
        #ffd86b,
        #fff2b3,
        #ffd86b,
        transparent
    );

    border-radius: 20px;
}


/* Mobile */

@media (max-width: 600px) {

    .admin-vision-heading {
        width: 94%;
        padding: 24px 15px;
        margin: 15px auto 25px;
    }

    .admin-vision-heading h1 {
        font-size: 27px;
        line-height: 1.25;
    }

    .admin-vision-heading .vision-line {
        width: 110px;
    }
}
    </style>

</head>


<body class="admin-dashboard-page">


<!-- =================================================
     ADMIN HEADER
     ================================================= -->

<div class="admin-dashboard-header">


    <!-- LOGO -->

    <div class="admin-dashboard-logo">

        <img src="logo.jpg"
             alt="CivicVoice Logo">

    </div>


    <!-- TITLE -->

    <div class="admin-dashboard-title">

        <h2>
            CivicVoice Admin Dashboard
        </h2>

        <p>
            Monitor complaints department-wise
        </p>

    </div>


    <!-- BUTTONS -->

    <div class="admin-header-actions">

        <a href="index.php"
           class="admin-home-btn">

            🏠 Back to Home

        </a>


        <a href="admin_logout.php"
           class="admin-logout-btn">

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
        <?php
        echo htmlspecialchars($_SESSION["admin_name"]);
        ?>
    </strong>

</div>



<!-- =================================================
     PAGE TITLE
     ================================================= -->

<!-- =================================================
     MAIN VISION
     ================================================= -->

<div class="admin-vision-heading">

    <h1>
        " What a Vision, What a Thought "
    </h1>

    <div class="vision-line"></div>

</div>


<!-- =================================================
     PAGE TITLE
     ================================================= -->

<div class="dashboard-title">

    <h3>
        🏛️ Department-wise Complaint Monitoring
    </h3>

    <div class="title-line"></div>

</div>



<!-- =================================================
     DEPARTMENT BUTTONS
     ================================================= -->

<?php

if (mysqli_num_rows($department_result) > 0) {

?>

<div class="department-grid">

<?php

$icons = [
    "💧",
    "🛣️",
    "🧹",
    "🚰",
    "💡",
    "🏥",
    "🎓",
    "🗑️",
    "🏗️",
    "💰",
    "🏢",
    "👮",
    "🌳",
    "🏠",
    "📋"
];

$icon_index = 0;


while ($department = mysqli_fetch_assoc($department_result)) {

?>

    <a
        href="admin_department_complaints.php?department_id=<?php echo $department["department_id"]; ?>"
        class="department-button"
    >

        <div class="department-icon">

            <?php

            echo $icons[$icon_index];

            ?>

        </div>


        <div class="department-name">

            <?php

            echo htmlspecialchars(
                $department["department_name"]
            );

            ?>

        </div>


        <div class="department-id">

            Department ID:
            <?php

            echo htmlspecialchars(
                $department["department_id"]
            );

            ?>

        </div>

    </a>


<?php

    $icon_index++;

}

?>

</div>

<?php

} else {

?>

<div class="no-departments">

    No departments found.

</div>

<?php

}

?>


</body>

</html>