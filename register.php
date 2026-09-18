<?php

include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $citizen_name = $_POST["citizen_name"];
    $email = $_POST["email"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    /* Check passwords */

    if ($password != $confirm_password) {

        echo "Passwords do not match";
        exit;

    }

    /* Check whether username already exists */

    $check_sql = "SELECT * FROM citizens WHERE username='$username'";

    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {

        echo "Username already exists";
        exit;

    }

    /* Hash password */

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    /* Insert new citizen */

    $sql = "INSERT INTO citizens
            (citizen_name, email, username, password)
            VALUES
            ('$citizen_name', '$email', '$username', '$hashed_password')";

    if (mysqli_query($conn, $sql)) {

        echo "<h2>Registration Successful!</h2>";

        echo "<p>Your account has been created successfully.</p>";

        echo "<a href='login.php'>Go to Login</a>";

    } else {

        echo "Registration failed: " . mysqli_error($conn);

    }

    exit;
}

?>

<!DOCTYPE html>
<html>

<head>
<link rel="stylesheet" href="style.css">
    <title>CivicVoice Registration</title>

</head>

<body>

<h2>📝 Citizen Registration</h2>

<form method="POST">

    <label>Full Name:</label><br>

    <input
        type="text"
        name="citizen_name"
        required
    >

    <br><br>


    <label>Email:</label><br>

    <input
        type="email"
        name="email"
        required
    >

    <br><br>


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
        required
    >

    <br><br>


    <label>Confirm Password:</label><br>

    <input
        type="password"
        name="confirm_password"
        required
    >

    <br><br>


    <button type="submit">
        Register
    </button>


</form>

<br>

<p>
    Already have an account?
    <a href="login.php">Login</a>
</p>

</body>

</html>