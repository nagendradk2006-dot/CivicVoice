<?php
session_start();
include "db.php";


/* Generate CAPTCHA when login page is opened */

if (!isset($_SESSION["captcha"])) {

    $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    $captcha = substr(str_shuffle($characters), 0, 6);

    $_SESSION["captcha"] = $captcha;
}


/* Login processing */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];
    $entered_captcha = $_POST["captcha"];


    /* Check CAPTCHA */

    if (strcasecmp(trim($entered_captcha), trim($_SESSION["captcha"])) != 0) {

        echo "<p style='color:red;'>Invalid CAPTCHA. A new CAPTCHA has been generated.</p>";

        /* Generate new CAPTCHA */

        $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        $captcha = substr(str_shuffle($characters), 0, 6);

        $_SESSION["captcha"] = $captcha;

    } else {


        /* Find citizen by username */

        $sql = "SELECT * FROM citizens WHERE username='$username'";

        $result = mysqli_query($conn, $sql);


        if (mysqli_num_rows($result) == 1) {

            $user = mysqli_fetch_assoc($result);


            /* Verify password */

            if (password_verify($password, $user["password"])) {

                $_SESSION["citizen_id"] = $user["citizen_id"];

                /* Generate new CAPTCHA for next login */

                $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

                $_SESSION["captcha"] = substr(str_shuffle($characters), 0, 6);

                header("Location: home.php");

                exit;

            } else {

                echo "<p style='color:red;'>Invalid username or password.</p>";

            }

        } else {

            echo "<p style='color:red;'>Invalid username or password.</p>";

        }

    }
}
?>


<!DOCTYPE html>

html>

<head>
<link rel="stylesheet" href="style.css">
<title>CivicVoice Login</title>

</head>


<body>


<h2>Citizen Login</h2>


<form method="POST">


<label>Username:</label>

<br>

<input type="text" name="username" required>

<br><br>


<label>Password:</label>

<br>

<input type="password"
       name="password"
       id="password"
       required>

<button type="button" onclick="showPassword()">👁</button>

<br><br>


<label>

CAPTCHA:

<?php echo $_SESSION["captcha"]; ?>

</label>


<button type="button" onclick="refreshCaptcha()">🔄</button>

<br>

<input type="text" name="captcha" required>

<br><br>


<button type="submit">Login</button>


</form>


<script>


function showPassword() {

    var password = document.getElementById("password");

    if (password.type === "password") {

        password.type = "text";

    } else {

        password.type = "password";

    }

}


function refreshCaptcha() {

    location.reload();

}


</script>


</body>

</html>