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


<body>

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


<br>


<a href="login.php">



</a>


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