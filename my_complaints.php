<?php

session_start();
include "db.php";

/* Check whether citizen is logged in */
if (!isset($_SESSION["citizen_id"])) {
    header("Location: login.php");
    exit;
}

$citizen_id = $_SESSION["citizen_id"];

/* Get complaints of logged-in citizen */
$sql = "SELECT complaints.*, departments.department_name
        FROM complaints
        INNER JOIN departments
        ON complaints.department_id = departments.department_id
        WHERE complaints.citizen_id = '$citizen_id'
        ORDER BY complaints.created_at DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html>

<head>

    <link rel="stylesheet" href="style.css?v=2">

    <title>My Complaints - CivicVoice</title>

</head>

<body>

<div class="my-complaints-page">


    <!-- TOP HEADER -->

    <div class="complaints-top-bar">

        <div>
            <h2>My Complaints</h2>

            <p>
                View and track the public issues you have reported through CivicVoice.
            </p>
        </div>

        <button
            class="top-dashboard-button"
            onclick="window.location.href='dashboard.php'"
        >
            ← Back to Dashboard
        </button>

    </div>


    <!-- COMPLAINTS -->

    <div class="complaints-list">

<?php

if (mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {

?>

        <div class="complaint-card">


            <!-- COMPLAINT HEADER -->

            <div class="complaint-card-header">

                <div>

                    <span class="complaint-label">
                        Complaint ID
                    </span>

                    <h3>
                        #<?php echo $row["complaint_id"]; ?>
                    </h3>

                </div>


                <div class="complaint-status">

                    <span class="complaint-label">
                        Status
                    </span>

                    <span class="status-badge">
                        <?php echo $row["status"]; ?>
                    </span>

                </div>

            </div>


            <!-- COMPLAINT DETAILS -->

            <div class="complaint-details">


                <div class="complaint-detail">

                    <span>Department</span>

                    <strong>
                        <?php echo $row["department_name"]; ?>
                    </strong>

                </div>


                <div class="complaint-detail">

                    <span>Constituency</span>

                    <strong>
                        <?php echo $row["constituency_name"]; ?>
                    </strong>

                </div>


                <div class="complaint-detail">

                    <span>Ward Number</span>

                    <strong>
                        <?php echo $row["ward_number"]; ?>
                    </strong>

                </div>


                <div class="complaint-detail">

                    <span>Area</span>

                    <strong>
                        <?php echo $row["area_name"]; ?>
                    </strong>

                </div>


                <div class="complaint-detail">

                    <span>PIN Code</span>

                    <strong>
                        <?php echo $row["pincode"]; ?>
                    </strong>

                </div>


                <div class="complaint-detail">

                    <span>Submitted On</span>

                    <strong>
                        <?php echo $row["created_at"]; ?>
                    </strong>

                </div>


            </div>


            <!-- ISSUE -->

            <div class="complaint-issue">

                <span>Issue Description</span>

                <p>
                    <?php echo $row["issue_description"]; ?>
                </p>

            </div>


            <!-- RESOLUTION -->

            <div class="resolution-section">

                <span>Resolution Remarks</span>

                <p>

<?php

if (!empty($row["resolution_remarks"])) {

    echo $row["resolution_remarks"];

} else {

    echo "No resolution remarks yet.";

}

?>

                </p>

            </div>


<?php

if (!empty($row["resolved_at"])) {

?>

            <!-- RESOLVED DATE -->

            <div class="resolved-date">

                <span>Resolved On</span>

                <strong>
                    <?php echo $row["resolved_at"]; ?>
                </strong>

            </div>

<?php

}

?>

        </div>

<?php

    }

} else {

?>

        <!-- NO COMPLAINTS -->

        <div class="no-complaints">

            <h3>No Complaints Yet</h3>

            <p>
                You have not submitted any complaints yet.
            </p>

        </div>

<?php

}

?>

    </div>

</div>


</body>

</html>