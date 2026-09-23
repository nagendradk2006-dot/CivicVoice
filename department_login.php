<?php

session_start();
include "db.php";


/* Department Login */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];


    /* Find department user */

    $sql = "SELECT *
            FROM department_users
            WHERE username='$username'";

    $result = mysqli_query($conn, $sql);


    if (mysqli_num_rows($result) == 1) {

        $department_user = mysqli_fetch_assoc($result);


        /* Verify password */

        if (
            password_verify(
                $password,
                $department_user["password"]
            )
        ) {

            /* Store department information in session */

            $_SESSION["department_user_id"] =
                $department_user["department_user_id"];

            $_SESSION["department_name"] =
                $department_user["department_name"];

            $_SESSION["department_id"] =
                $department_user["department_id"];


            /* Go to department dashboard */

            header("Location: department_dashboard.php");
            exit;

        } else {

            echo "<p style='color:red;'>
                  Invalid username or password.
                  </p>";

        }

    } else {

        echo "<p style='color:red;'>
              Invalid username or password.
              </p>";

    }

}

?>

<!DOCTYPE html>

<html>

<head>
<link rel="stylesheet" href="style.css">
<title>CivicVoice Department Login</title>

</head>

<body class="department-login-page">

<img src="logo.jpg"
     alt="CivicVoice Logo"
     class="department-login-logo"
     style="width:200px !important; height:200px !important; object-fit:contain;">
<h2>🏢 CivicVoice Department Login</h2>


<form method="POST">


<label>Username:</label>

<br>

<input type="text"
       name="username"
       required>

<br><br>


<label>Password:</label>

<br>

<input type="password"
       name="password"
       id="password"
       required>

<button type="button"
        onclick="showPassword()">

👁

</button>

<br><br>


<button type="submit">

Department Login

</button>


</form>


<div style="
    text-align: center;
    margin-top: 20px;
    position: relative;
    z-index: 9999;
">
    <a href="index.php"
       style="
           display: inline-block !important;
           padding: 11px 24px !important;
           border-radius: 30px !important;
           border: 2px solid #d4af37 !important;
           background: linear-gradient(135deg, #146b3e, #0f5732) !important;
           color: #ffffff !important;
           text-decoration: none !important;
           font-family: Arial, sans-serif !important;
           font-size: 14px !important;
           font-weight: 700 !important;
           letter-spacing: 0.3px !important;
           box-shadow: 0 6px 18px rgba(0,0,0,0.18) !important;
       ">
        ← &nbsp; Back to Home
    </a>
</div>

<script>

function showPassword() {

    var password =
        document.getElementById("password");


    if (password.type === "password") {

        password.type = "text";

    } else {

        password.type = "password";

    }

}

</script>


</body>

</html>