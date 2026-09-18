<?php

session_start();
include "db.php";


/* Admin Login Processing */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];


    /* Find admin by username */

    $sql = "SELECT * FROM admins
            WHERE username='$username'";

    $result = mysqli_query($conn, $sql);


    if (mysqli_num_rows($result) == 1) {

        $admin = mysqli_fetch_assoc($result);


        /* Verify password */

        if (password_verify($password, $admin["password"])) {

            /* Store admin ID in session */

            $_SESSION["admin_id"] = $admin["admin_id"];

            $_SESSION["admin_name"] = $admin["admin_name"];


            /* Go to Admin Dashboard */

            header("Location: admin_dashboard.php");
            exit;

        } else {

            echo "<p>Invalid username or password</p>";

        }

    } else {

        echo "<p>Invalid username or password</p>";

    }

}

?>


<!DOCTYPE html>
<html>

<head>
<link rel="stylesheet" href="style.css">
    <title>CivicVoice Admin Login</title>

</head>

<body>

<h2>🏛️ CivicVoice Admin Login</h2>


<form method="POST">


    <label>Username:</label><br>

    <input
        type="text"
        name="username"
        required
    >

    <br><br>


    <label>Password:</label><br>

    <input
        type="password"
        name="password"
        id="password"
        required
    >

    <button
        type="button"
        onclick="showPassword()"
    >
        👁
    </button>

    <br><br>


    <button type="submit">
        Admin Login
    </button>


</form>


<br>


<a href="login.php">
    ← Citizen Login
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