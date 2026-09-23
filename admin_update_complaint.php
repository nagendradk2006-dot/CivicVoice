<?php

session_start();

include "db.php";


/* Check Admin Login */

if (!isset($_SESSION["admin_id"])) {

    header("Location: admin_login.php");

    exit;

}


/* Get Complaint ID */

$complaint_id = $_GET["complaint_id"];


/* Get Complaint Details */

$sql = "SELECT *
        FROM complaints
        WHERE complaint_id='$complaint_id'";

$result = mysqli_query($conn, $sql);

$complaint = mysqli_fetch_assoc($result);


/* Update Complaint */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $status = $_POST["status"];

    $resolution_remarks =
        $_POST["resolution_remarks"];


    if ($status == "Resolved") {

        $update_sql = "UPDATE complaints
                       SET status='$status',
                           resolution_remarks='$resolution_remarks',
                           resolved_at=NOW()
                       WHERE complaint_id='$complaint_id'";

    } else {

        $update_sql = "UPDATE complaints
                       SET status='$status',
                           resolution_remarks='$resolution_remarks',
                           resolved_at=NULL
                       WHERE complaint_id='$complaint_id'";

    }


    if (mysqli_query($conn, $update_sql)) {

        header("Location: admin_dashboard.php");

        exit;

    } else {

        echo "Update failed: "
             . mysqli_error($conn);

    }

}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Update Complaint - CivicVoice</title>


    <style>

        /* =================================================
           CIVICVOICE UPDATE COMPLAINT PAGE
           ================================================= */

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            padding: 30px 15px;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f4f8f5;

            color: #333;

        }


        /* =================================================
           MAIN CONTAINER
           ================================================= */

        .update-container {

            width: 100%;

            max-width: 750px;

            margin: 20px auto;

        }


        /* =================================================
           PAGE HEADER
           ================================================= */

        .update-header {

            text-align: center;

            margin-bottom: 25px;

        }


        .update-header h1 {

            margin: 0;

            color: #176b3a;

            font-size: 30px;

            font-weight: 700;

        }


        .update-header p {

            margin-top: 8px;

            color: #777;

            font-size: 14px;

        }


        .header-line {

            width: 80px;

            height: 3px;

            margin: 12px auto 0;

            background: #c9a227;

            border-radius: 10px;

        }


        /* =================================================
           COMPLAINT DETAILS CARD
           ================================================= */

        .complaint-details {

            background: #ffffff;

            border: 2px solid #c9a227;

            border-radius: 16px;

            padding: 25px;

            margin-bottom: 22px;

            box-shadow:
                0 7px 22px
                rgba(0, 0, 0, 0.08);

        }


        .complaint-details h2 {

            margin: 0 0 18px;

            padding-bottom: 12px;

            color: #176b3a;

            font-size: 20px;

            border-bottom:
                1px solid #e3d39a;

        }


        .detail-row {

            margin: 13px 0;

            line-height: 1.6;

        }


        .detail-label {

            color: #176b3a;

            font-weight: bold;

        }


        .complaint-id {

            display: inline-block;

            padding: 4px 10px;

            margin-left: 5px;

            background: #edf7f0;

            color: #176b3a;

            border: 1px solid #b8d8c2;

            border-radius: 20px;

            font-weight: bold;

        }


        /* =================================================
           UPDATE FORM CARD
           ================================================= */

        .update-form-card {

            background: #ffffff;

            border: 2px solid #c9a227;

            border-radius: 16px;

            padding: 25px;

            box-shadow:
                0 7px 22px
                rgba(0, 0, 0, 0.08);

        }


        .update-form-card h2 {

            margin: 0 0 20px;

            color: #176b3a;

            font-size: 20px;

        }


        /* =================================================
           FORM GROUP
           ================================================= */

        .form-group {

            margin-bottom: 22px;

        }


        .form-group label {

            display: block;

            margin-bottom: 8px;

            color: #176b3a;

            font-weight: bold;

            font-size: 15px;

        }


        /* =================================================
           SELECT
           ================================================= */

        .status-select {

            width: 100%;

            padding: 12px 14px;

            background: #ffffff;

            color: #333;

            border: 1px solid #c9a227;

            border-radius: 8px;

            font-size: 15px;

            outline: none;

            cursor: pointer;

        }


        .status-select:focus {

            border-color: #176b3a;

            box-shadow:
                0 0 0 3px
                rgba(23, 107, 58, 0.10);

        }


        /* =================================================
           TEXTAREA
           ================================================= */

        .remarks-box {

            width: 100%;

            min-height: 140px;

            padding: 13px;

            resize: vertical;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            font-size: 15px;

            line-height: 1.5;

            border: 1px solid #c9a227;

            border-radius: 8px;

            outline: none;

        }


        .remarks-box:focus {

            border-color: #176b3a;

            box-shadow:
                0 0 0 3px
                rgba(23, 107, 58, 0.10);

        }


        .remarks-box::placeholder {

            color: #999;

        }


        /* =================================================
           BUTTON AREA
           ================================================= */

        .button-area {

            display: flex;

            gap: 12px;

            flex-wrap: wrap;

            margin-top: 10px;

        }


        /* =================================================
           UPDATE BUTTON
           ================================================= */

        .update-button {

            flex: 1;

            min-width: 200px;

            padding: 13px 20px;

            background: #176b3a;

            color: #ffffff;

            border: 2px solid #c9a227;

            border-radius: 8px;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            transition: all 0.25s ease;

        }


        .update-button:hover {

            background: #c9a227;

            color: #176b3a;

            transform: translateY(-2px);

        }


        /* =================================================
           BACK BUTTON
           ================================================= */

        .back-button {

            flex: 1;

            min-width: 200px;

            padding: 13px 20px;

            background: #ffffff;

            color: #176b3a;

            border: 2px solid #176b3a;

            border-radius: 8px;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            transition: all 0.25s ease;

        }


        .back-button:hover {

            background: #176b3a;

            color: #ffffff;

            transform: translateY(-2px);

        }


        /* =================================================
           MOBILE RESPONSIVE
           ================================================= */

        @media (max-width: 600px) {

            body {

                padding:
                    20px 12px;

            }


            .update-container {

                width: 100%;

                margin: 10px auto;

            }


            .update-header h1 {

                font-size: 24px;

            }


            .complaint-details,
            .update-form-card {

                padding: 18px;

                border-radius: 13px;

            }


            .complaint-details h2,
            .update-form-card h2 {

                font-size: 18px;

            }


            .detail-row {

                font-size: 14px;

            }


            .button-area {

                flex-direction: column;

            }


            .update-button,
            .back-button {

                width: 100%;

                min-width: 0;

            }

        }

    </style>

</head>


<body>


<div class="update-container">


    <!-- =============================================
         PAGE HEADER
         ============================================= -->

    <div class="update-header">

        <h1>
            📝 Update Complaint
        </h1>

        <p>
            Review the complaint and update its current status
        </p>

        <div class="header-line"></div>

    </div>


    <!-- =============================================
         COMPLAINT DETAILS
         ============================================= -->

    <div class="complaint-details">

        <h2>
            📋 Complaint Details
        </h2>


        <div class="detail-row">

            <span class="detail-label">
                Complaint ID:
            </span>

            <span class="complaint-id">
                <?php echo $complaint["complaint_id"]; ?>
            </span>

        </div>


        <div class="detail-row">

            <span class="detail-label">
                📍 Area:
            </span>

            <?php echo $complaint["area_name"]; ?>

        </div>


        <div class="detail-row">

            <span class="detail-label">
                📝 Issue:
            </span>

            <?php echo $complaint["issue_description"]; ?>

        </div>

    </div>


    <!-- =============================================
         UPDATE FORM
         ============================================= -->

    <div class="update-form-card">

        <h2>
            ⚙️ Complaint Status
        </h2>


        <form method="POST">


            <!-- STATUS -->

            <div class="form-group">

                <label>
                    Complaint Status
                </label>

                <select
                    name="status"
                    class="status-select"
                    required
                >

                    <option value="Submitted">
                        Submitted
                    </option>

                    <option value="In Progress">
                        In Progress
                    </option>

                    <option value="Resolved">
                        Resolved
                    </option>

                </select>

            </div>


            <!-- REMARKS -->

            <div class="form-group">

                <label>
                    Resolution Remarks
                </label>

                <textarea
                    name="resolution_remarks"
                    class="remarks-box"
                    placeholder="Enter resolution remarks..."
                ></textarea>

            </div>


            <!-- BUTTONS -->

            <div class="button-area">

                <button
                    type="submit"
                    class="update-button"
                >
                    ✅ Update Complaint
                </button>


                <button
                    type="button"
                    class="back-button"
                    onclick="window.location.href='admin_dashboard.php'"
                >
                    ← Back to Dashboard
                </button>

            </div>


        </form>

    </div>


</div>


</body>

</html>