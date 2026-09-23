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


$department_id = $_SESSION["department_id"];


/* ================================================= */
/* CHECK COMPLAINT ID */
/* ================================================= */

if (!isset($_GET["complaint_id"]) || empty($_GET["complaint_id"])) {

    echo "<h2>Complaint ID is missing.</h2>";
    exit;
}


$complaint_id = mysqli_real_escape_string(
    $conn,
    $_GET["complaint_id"]
);


/* ================================================= */
/* GET COMPLAINT BELONGING TO THIS DEPARTMENT */
/* ================================================= */

$sql = "SELECT complaints.*, departments.department_name
        FROM complaints
        INNER JOIN departments
        ON complaints.department_id = departments.department_id
        WHERE complaints.complaint_id='$complaint_id'
        AND complaints.department_id='$department_id'";


$result = mysqli_query($conn, $sql);


if (!$result || mysqli_num_rows($result) != 1) {

    echo "<h2>You are not allowed to update this complaint.</h2>";
    exit;
}


$complaint = mysqli_fetch_assoc($result);


/* ================================================= */
/* UPDATE COMPLAINT */
/* ================================================= */

$update_error = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $status = isset($_POST["status"])
        ? mysqli_real_escape_string($conn, $_POST["status"])
        : "";

    $resolution_remarks = isset($_POST["resolution_remarks"])
        ? mysqli_real_escape_string(
            $conn,
            $_POST["resolution_remarks"]
        )
        : "";


    /* --------------------------------------------- */
    /* RESOLVED */
    /* --------------------------------------------- */

    if ($status == "Resolved") {

        $update_sql = "UPDATE complaints
                       SET status='$status',
                           resolution_remarks='$resolution_remarks',
                           resolved_at=NOW()
                       WHERE complaint_id='$complaint_id'
                       AND department_id='$department_id'";

    }


    /* --------------------------------------------- */
    /* OTHER STATUS */
    /* --------------------------------------------- */

    else {

        $update_sql = "UPDATE complaints
                       SET status='$status',
                           resolution_remarks='$resolution_remarks',
                           resolved_at=NULL
                       WHERE complaint_id='$complaint_id'
                       AND department_id='$department_id'";

    }


    /* --------------------------------------------- */
    /* EXECUTE UPDATE */
    /* --------------------------------------------- */

    if (mysqli_query($conn, $update_sql)) {

        header(
            "Location: department_dashboard.php"
        );

        exit;

    } else {

        $update_error =
            "Unable to update the complaint. Please try again.";

    }

}

?>


<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Update Complaint - CivicVoice
    </title>

    <link rel="stylesheet" href="style.css">


    <!-- ================================================= -->
    <!-- UPDATE PAGE DESIGN -->
    <!-- ================================================= -->

    <style>

        /* ================================================= */
        /* PAGE */
        /* ================================================= */

        body.department-update-page {

            margin: 0;

            background:
                linear-gradient(
                    135deg,
                    #f5f8f5,
                    #ffffff
                );

            color: #222;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

        }


        /* ================================================= */
        /* HEADER */
        /* ================================================= */

        .update-header {

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

            color: #ffffff;

            box-shadow:
                0 5px 18px rgba(0,0,0,0.18);

        }


        /* ================================================= */
        /* LOGO */
        /* ================================================= */

        .update-logo {

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


        .update-title {

            margin: 15px 0 3px;

            color: #ffffff;

            font-size: 32px;

            font-weight: 800;

            letter-spacing: 1px;

        }


        .update-subtitle {

            margin: 0;

            color: #f5d76e;

            font-size: 16px;

            font-weight: 600;

            letter-spacing: 1px;

        }


        /* ================================================= */
        /* LOGOUT */
        /* ================================================= */

        .update-logout {

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

        }


        .update-logout:hover {

            background: #fff8e1;

        }


        /* ================================================= */
        /* MAIN CONTAINER */
        /* ================================================= */

        .update-container {

            width: 100%;

            max-width: 900px;

            margin: 0 auto;

            padding: 40px 20px 60px;

            box-sizing: border-box;

        }


        /* ================================================= */
        /* PAGE HEADING */
        /* ================================================= */

        .update-heading {

            text-align: center;

            margin-bottom: 30px;

        }


        .update-heading h2 {

            margin: 0;

            color: #1b5e20;

            font-size: 28px;

        }


        .update-heading p {

            margin: 8px 0 0;

            color: #777777;

            font-size: 15px;

        }


        /* ================================================= */
        /* COMPLAINT CARD */
        /* ================================================= */

        .update-card {

            width: 100%;

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
        /* COMPLAINT INFORMATION */
        /* ================================================= */

        .complaint-info-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 15px;

            margin-bottom: 25px;

        }


        .info-box {

            background: #f8faf8;

            border: 1px solid #e1e8e1;

            border-left: 4px solid #d4af37;

            border-radius: 10px;

            padding: 15px;

            box-sizing: border-box;

        }


        .info-box span {

            display: block;

            color: #777777;

            font-size: 13px;

            font-weight: 600;

            margin-bottom: 6px;

        }


        .info-box strong {

            display: block;

            color: #1b5e20;

            font-size: 16px;

            line-height: 1.4;

            word-break: break-word;

        }


        /* ================================================= */
        /* ISSUE DESCRIPTION */
        /* ================================================= */

        .issue-box {

            background: #f8faf8;

            border: 1px solid #dfe7df;

            border-left: 5px solid #1b5e20;

            border-radius: 12px;

            padding: 20px;

            margin-bottom: 30px;

        }


        .issue-box h3 {

            margin: 0 0 10px;

            color: #1b5e20;

            font-size: 18px;

        }


        .issue-box p {

            margin: 0;

            color: #333333;

            font-size: 16px;

            line-height: 1.6;

            word-break: break-word;

        }


        /* ================================================= */
        /* FORM SECTION */
        /* ================================================= */

        .update-form {

            border-top: 1px solid #eeeeee;

            padding-top: 25px;

        }


        .form-group {

            margin-bottom: 25px;

        }


        .form-group label {

            display: block;

            margin-bottom: 8px;

            color: #1b5e20;

            font-size: 15px;

            font-weight: 700;

        }


        /* ================================================= */
        /* SELECT */
        /* ================================================= */

        .status-select {

            width: 100%;

            padding: 13px 15px;

            border: 1px solid #cccccc;

            border-radius: 10px;

            background: #ffffff;

            color: #222222;

            font-size: 16px;

            outline: none;

            box-sizing: border-box;

            cursor: pointer;

        }


        .status-select:focus {

            border-color: #1b5e20;

            box-shadow:
                0 0 0 3px rgba(
                    27,
                    94,
                    32,
                    0.10
                );

        }


        /* ================================================= */
        /* TEXTAREA */
        /* ================================================= */

        .remarks-textarea {

            width: 100%;

            min-height: 150px;

            padding: 15px;

            border: 1px solid #cccccc;

            border-radius: 10px;

            background: #ffffff;

            color: #222222;

            font-size: 16px;

            line-height: 1.5;

            resize: vertical;

            outline: none;

            box-sizing: border-box;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

        }


        .remarks-textarea:focus {

            border-color: #1b5e20;

            box-shadow:
                0 0 0 3px rgba(
                    27,
                    94,
                    32,
                    0.10
                );

        }


        /* ================================================= */
        /* BUTTONS */
        /* ================================================= */

        .update-actions {

            display: flex;

            justify-content: center;

            gap: 15px;

            padding-top: 10px;

        }


        .save-btn {

            border: none;

            background: #008f68;

            color: #ffffff;

            padding: 13px 30px;

            border-radius: 25px;

            font-size: 15px;

            font-weight: 700;

            cursor: pointer;

            box-shadow:
                0 5px 12px rgba(0,0,0,0.15);

        }


        .save-btn:hover {

            background: #006f50;

        }


        .back-btn {

            display: inline-block;

            background: #ffffff;

            color: #1b5e20 !important;

            text-decoration: none !important;

            padding: 12px 25px;

            border-radius: 25px;

            border: 2px solid #1b5e20;

            font-size: 15px;

            font-weight: 700;

        }


        .back-btn:hover {

            background: #e8f5e9;

        }


        /* ================================================= */
        /* ERROR */
        /* ================================================= */

        .update-error {

            margin-bottom: 20px;

            padding: 13px 16px;

            background: #ffebee;

            color: #b71c1c;

            border: 1px solid #ef9a9a;

            border-radius: 10px;

            font-weight: 600;

        }


        /* ================================================= */
        /* FOOTER */
        /* ================================================= */

        .update-footer {

            text-align: center;

            padding: 25px;

            background: #0b4d2b;

            color: #ffffff;

        }


        .update-footer p {

            margin: 0;

        }


        /* ================================================= */
        /* MOBILE */
        /* ================================================= */

        @media (max-width: 768px) {

            .update-header {

                padding: 30px 15px 25px;

            }


            .update-logo {

                width: 95px !important;

                height: 95px !important;

                max-width: 95px !important;

                max-height: 95px !important;

            }


            .update-title {

                font-size: 27px;

            }


            .update-logout {

                top: 15px;

                right: 15px;

                padding: 8px 14px;

                font-size: 13px;

            }


            .update-container {

                padding: 25px 12px 45px;

            }


            .update-card {

                padding: 20px;

            }


            .complaint-info-grid {

                grid-template-columns: 1fr;

            }


            .update-actions {

                flex-direction: column;

            }


            .save-btn,
            .back-btn {

                width: 100%;

                text-align: center;

                box-sizing: border-box;

            }

        }

    </style>

</head>


<body class="department-update-page">


<!-- ================================================= -->
<!-- HEADER -->
<!-- ================================================= -->

<header class="update-header">


    <!-- LOGOUT -->

    <a href="department_logout.php"
       class="update-logout">

        🚪 Logout

    </a>


    <!-- CENTER LOGO -->

    <div>

        <img src="logo.jpg"
             alt="CivicVoice Logo"
             class="update-logo">


        <h1 class="update-title">
            CivicVoice
        </h1>


        <p class="update-subtitle">
            Department Complaint Update
        </p>

    </div>


</header>


<!-- ================================================= -->
<!-- MAIN -->
<!-- ================================================= -->

<main class="update-container">


    <!-- PAGE HEADING -->

    <div class="update-heading">

        <h2>
            📝 Update Complaint
        </h2>

        <p>
            Review the complaint and update its current status
        </p>

    </div>


    <!-- ================================================= -->
    <!-- COMPLAINT CARD -->
    <!-- ================================================= -->

    <section class="update-card">


        <!-- ================================================= -->
        <!-- COMPLAINT INFORMATION -->
        <!-- ================================================= -->

        <div class="complaint-info-grid">


            <!-- COMPLAINT ID -->

            <div class="info-box">

                <span>
                    Complaint ID
                </span>

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $complaint["complaint_id"]
                    );
                    ?>

                </strong>

            </div>


            <!-- DEPARTMENT -->

            <div class="info-box">

                <span>
                    Department
                </span>

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $complaint["department_name"]
                    );
                    ?>

                </strong>

            </div>


            <!-- CONSTITUENCY -->

            <div class="info-box">

                <span>
                    Constituency
                </span>

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $complaint["constituency_name"]
                    );
                    ?>

                </strong>

            </div>


            <!-- WARD -->

            <div class="info-box">

                <span>
                    Ward Number
                </span>

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $complaint["ward_number"]
                    );
                    ?>

                </strong>

            </div>


            <!-- AREA -->

            <div class="info-box">

                <span>
                    Area
                </span>

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $complaint["area_name"]
                    );
                    ?>

                </strong>

            </div>


            <!-- PIN -->

            <div class="info-box">

                <span>
                    PIN Code
                </span>

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $complaint["pincode"]
                    );
                    ?>

                </strong>

            </div>


        </div>


        <!-- ================================================= -->
        <!-- ISSUE -->
        <!-- ================================================= -->

        <div class="issue-box">

            <h3>
                Issue Description
            </h3>

            <p>

                <?php

                echo nl2br(
                    htmlspecialchars(
                        $complaint["issue_description"]
                    )
                );

                ?>

            </p>

        </div>


        <!-- ================================================= -->
        <!-- UPDATE FORM -->
        <!-- ================================================= -->

        <form method="POST"
              class="update-form">


            <?php

            if (!empty($update_error)) {

            ?>

                <div class="update-error">

                    <?php
                    echo htmlspecialchars(
                        $update_error
                    );
                    ?>

                </div>

            <?php

            }

            ?>


            <!-- STATUS -->

            <div class="form-group">


                <label for="status">

                    Complaint Status

                </label>


                <select name="status"
                        id="status"
                        class="status-select"
                        required>


                    <option value="Submitted"
                        <?php

                        if (
                            $complaint["status"]
                            == "Submitted"
                        ) {

                            echo "selected";

                        }

                        ?>>

                        Submitted

                    </option>


                    <option value="In Progress"
                        <?php

                        if (
                            $complaint["status"]
                            == "In Progress"
                        ) {

                            echo "selected";

                        }

                        ?>>

                        In Progress

                    </option>


                    <option value="Resolved"
                        <?php

                        if (
                            $complaint["status"]
                            == "Resolved"
                        ) {

                            echo "selected";

                        }

                        ?>>

                        Resolved

                    </option>


                </select>

            </div>


            <!-- RESOLUTION REMARKS -->

            <div class="form-group">


                <label for="resolution_remarks">

                    Resolution Remarks

                </label>


                <textarea
                    name="resolution_remarks"
                    id="resolution_remarks"
                    class="remarks-textarea"
                    rows="6"
                    placeholder="Enter details about the action taken or resolution..."><?php

                    echo htmlspecialchars(
                        $complaint["resolution_remarks"] ?? ""
                    );

                    ?></textarea>


            </div>


            <!-- BUTTONS -->

            <div class="update-actions">


                <button type="submit"
                        class="save-btn">

                    ✅ Update Complaint

                </button>


                <a href="department_dashboard.php"
                   class="back-btn">

                    ← Back to Dashboard

                </a>


            </div>


        </form>


    </section>


</main>


<!-- ================================================= -->
<!-- FOOTER -->
<!-- ================================================= -->

<footer class="update-footer">

    <p>

        © <?php echo date("Y"); ?> CivicVoice

    </p>

</footer>


</body>

</html>