<?php
session_start();

if (!isset($_SESSION["citizen_id"])) {
    header("Location: login.php");
    exit;
}

$citizen_id = $_SESSION["citizen_id"];
?>

<!DOCTYPE html>
<html>

<head>

    <link rel="stylesheet" href="style.css">

    <title>CivicVoice Dashboard</title>

    <style>

    /* =========================================================
       1. DASHBOARD BACKGROUND
       ========================================================= */

    body {
        margin: 0;
        background-color: #E8E2D5;
        color: #1F2937;
        font-family: Arial, sans-serif;
    }


    /* =========================================================
       2. WELCOME HEADER
       ========================================================= */

    .dashboard-header {
        max-width: 800px;
        margin: 35px auto 20px;
        padding: 30px 20px;
        background-color: #ffffff;
        border: 2px solid #166534;
        border-top: 7px solid #166534;
        border-radius: 20px;
        box-shadow: 0 6px 20px rgba(22, 101, 52, 0.12);
        text-align: center;
        box-sizing: border-box;
    }

    .dashboard-header h2 {
        margin: 0 0 10px;
        color: #166534;
        font-size: 30px;
        font-weight: 700;
    }

    .dashboard-header .welcome-text {
        margin: 0 0 15px;
        color: #666666;
        font-size: 16px;
    }

    .citizen-id {
        display: inline-block;
        padding: 8px 18px;
        background-color: #faf9f5;
        border: 1px solid #C9A227;
        border-radius: 20px;
        color: #166534;
        font-size: 14px;
        font-weight: 600;
    }


    /* =========================================================
       3. CITIZEN DASHBOARD HEADING
       ========================================================= */

    .dashboard-title {
        max-width: 800px;
        margin: 25px auto 20px;
        text-align: center;
        color: #166534;
        font-size: 23px;
        font-weight: 700;
    }


    /* =========================================================
       4. DASHBOARD BUTTON AREA
       ========================================================= */

    .dashboard-actions {
        max-width: 800px;
        margin: 0 auto;
        padding: 10px 20px;
        box-sizing: border-box;
    }


    /* =========================================================
       5. DASHBOARD BUTTONS
       ========================================================= */

    .dashboard-actions button {
        display: block;
        width: 320px;
        margin: 15px auto;
        padding: 15px 20px;
        border: none;
        border-radius: 10px;
        background-color: #166534;
        color: #ffffff;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(31, 41, 55, 0.12);
        transition: 0.2s ease;
    }

    .dashboard-actions button:hover {
        background-color: #C9A227;
        color: #1F2937;
        transform: translateY(-2px);
    }


    /* =========================================================
       6. LOGOUT BUTTON
       ========================================================= */

    .dashboard-actions .logout-button {
        background-color: #b91c1c;
    }

    .dashboard-actions .logout-button:hover {
        background-color: #991b1b;
        color: #ffffff;
    }


    /* =========================================================
       7. CIVIC INFORMATION PANEL
       ========================================================= */

    .civic-info {
        max-width: 800px;
        margin: 40px auto 25px;
        padding: 25px 20px;
        background-color: #ffffff;
        border: 1px solid #C9A227;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(31, 41, 55, 0.08);
        box-sizing: border-box;
    }

    .civic-info h3 {
        margin: 0 0 10px;
        color: #166534;
        font-size: 21px;
    }

    .civic-info p {
        margin: 8px 0;
        color: #555555;
        font-size: 15px;
        line-height: 1.6;
    }

    .civic-tagline {
        margin-top: 15px !important;
        color: #166534 !important;
        font-size: 17px !important;
        font-weight: 700;
    }


    /* =========================================================
       8. CIVIC DECORATION
       ========================================================= */

    .civic-decoration {
        max-width: 800px;
        margin: 20px auto;
        padding: 18px;
        text-align: center;
        font-size: 28px;
        letter-spacing: 12px;
        opacity: 0.85;
    }


    /* =========================================================
       9. FOOTER
       ========================================================= */

    .dashboard-footer {
        margin-top: 25px;
        padding: 18px;
        background-color: #166534;
        color: #ffffff;
        text-align: center;
        font-size: 13px;
    }

    .dashboard-footer p {
        margin: 5px;
    }


    /* =========================================================
       10. MOBILE RESPONSIVE
       ========================================================= */

    @media (max-width: 700px) {

        .dashboard-header {
            margin: 20px 10px;
            padding: 25px 15px;
        }

        .dashboard-header h2 {
            font-size: 24px;
        }

        .dashboard-title {
            margin-left: 10px;
            margin-right: 10px;
            font-size: 21px;
        }

        .dashboard-actions {
            padding: 5px 10px;
        }

        .dashboard-actions button {
            width: 100%;
            max-width: 320px;
        }

        .civic-info {
            margin: 30px 10px 20px;
            padding: 20px 15px;
        }

        .civic-decoration {
            font-size: 22px;
            letter-spacing: 7px;
        }

    }
.civic-info {
    font-family: Georgia, "Times New Roman", serif;
}
.citizen-dashboard-logo {
    width: 100px;
    height: 100px;
}
/* =========================================================
   CITIZEN DASHBOARD LOGO
   ========================================================= */

.citizen-dashboard-logo {
    width: 120px;
    height: 120px;
    object-fit: contain;
    display: block;
    margin: 0 auto 18px;
}
.back-dashboard {
    display: block;
    width: 220px;
    margin: 0 auto 20px;
    padding: 11px 18px;
    background-color: #333333;
    color: #ffffff;
    text-align: center;
    text-decoration: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 700;
    box-sizing: border-box;
    transition: 0.2s ease;
}

.back-dashboard:hover {
    background-color: #111111;
    transform: translateY(-2px);
}
    </style>

</head>


<body>


<!-- =========================================================
     WELCOME SECTION
     ========================================================= -->

<div class="dashboard-header">

    <img
        src="logo.jpg"
        alt="CivicVoice Logo"
        class="citizen-dashboard-logo"
    >

    <h2>
        👋 Welcome to CivicVoice
    </h2>

    <p class="welcome-text">
        Your voice helps make your community better.
    </p>

    <div class="citizen-id">
        Citizen ID: <?php echo $citizen_id; ?>
    </div>

</div>


<!-- =========================================================
     DASHBOARD TITLE
     ========================================================= -->

<div class="dashboard-title">
    Citizen Dashboard
</div>

<a href="home.php" class="back-dashboard">
    ← Back to Dashboard
</a>


<!-- =========================================================
     DASHBOARD ACTIONS
     ========================================================= -->

<div class="dashboard-actions">

    <button onclick="window.location.href='submit_complaint.php'">
        📢 Submit Complaint
    </button>

    <button onclick="window.location.href='my_complaints.php'">
        📋 My Complaints
    </button>

    <button
        class="logout-button"
        onclick="window.location.href='logout.php'">
        🚪 Logout
    </button>

</div>


<!-- =========================================================
     CIVIC INFORMATION
     ========================================================= -->

<div class="civic-info">

    <h3>
        🏙️ Make Your Voice Heard
    </h3>

    <p>
        Report public issues and keep track of your complaints
        through CivicVoice.
    </p>

    <p>
        Together, citizens and authorities can work towards
        a cleaner, safer and better community.
    </p>

    <p class="civic-tagline">
        “Your Voice. Your City. Your Change.”
    </p>

</div>


<!-- =========================================================
     CIVIC DECORATION
     ========================================================= -->



<!-- =========================================================
     FOOTER
     ========================================================= -->

<div class="dashboard-footer">

    <p>
        CivicVoice – Intelligent Public Grievance and Issue Tracking System
    </p>

    <p>
        Empowering citizens through digital civic participation.
    </p>

</div>


</body>

</html>