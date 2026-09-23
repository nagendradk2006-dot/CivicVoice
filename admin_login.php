
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

            $error = "Invalid username or password";

        }

    } else {

        $error = "Invalid username or password";

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>CivicVoice Admin Login</title>

    <link rel="stylesheet" href="style.css">

</head>


<body class="admin-login-page">


<div class="admin-login-card">
<!-- CivicVoice Logo -->

<div class="admin-login-logo">
    <img src="logo.jpg" alt="CivicVoice Logo">
</div>


    <!-- Heading -->

    <h2>CivicVoice Admin</h2>

    <p class="admin-login-subtitle">
        
    Secure administration portal
    </p>


    <!-- Error Message -->

    <?php if (isset($error)) { ?>

        <div class="admin-error">
            <?php echo $error; ?>
        </div>

    <?php } ?>


    <!-- Login Form -->

    <form method="POST">


        <div class="admin-input-group">

            <label for="username">
                Username
            </label>

            <input
                type="text"
                id="username"
                name="username"
                placeholder="Enter admin username"
                required
            >

        </div>


        <div class="admin-input-group">

            <label for="password">
                Password
            </label>


            <div class="password-wrapper">

                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Enter your password"
                    required
                >

                <button
                    type="button"
                    class="password-toggle"
                    onclick="showPassword()"
                    aria-label="Show password"
                >
                    👁
                </button>

            </div>

        </div>


        <button
            type="submit"
            class="admin-login-button"
        >
            Login to Admin Panel
        </button>


    </form>


    <!-- Navigation -->

    <div class="admin-login-links">

    <a href="index.php" class="back-home-btn">
        🏠 Back to Home
    </a>

</div>


</div>


<script>

function showPassword() {

    var password =
        document.getElementById("password");

    var button =
        document.querySelector(".password-toggle");


    if (password.type === "password") {

        password.type = "text";

        button.innerHTML = "🙈";

    } else {

        password.type = "password";

        button.innerHTML = "👁";

    }

}

</script>


</body>

</html>

