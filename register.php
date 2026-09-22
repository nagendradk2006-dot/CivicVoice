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
<link rel="stylesheet" href="style.css?v=2">
    <title>CivicVoice Registration</title>

</head>
<body>

<div class="register-container">

    <div class="register-brand">

        <img
            src="logo.jpg"
            alt="CivicVoice Logo"
            class="register-logo"
        >

        <h1>CivicVoice</h1>

        <h3>
            Join Your Digital Civic Community
        </h3>

        <p>
            Create your citizen account to report public issues
            and participate in improving your community.
        </p>

        <div class="register-highlight">

            <span>📢</span>

            <p>
                <strong>Make your voice heard.</strong><br>
                Report civic issues and help build a better community.
            </p>

        </div>

    </div>


    <div class="register-form-section">

        <h2>Create Citizen Account</h2>

        <p class="register-subtitle">
            Enter your details to get started with CivicVoice.
        </p>

        <form method="POST" onsubmit="return validateRegistration()">
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

<div class="password-box">

    <input
        type="password"
        name="password"
        id="registerPassword"
        required
    >

    <button
        type="button"
        class="password-toggle"
        onclick="toggleRegisterPassword('registerPassword')"
    >
        👁
    </button>

</div>

<br><br>


<label>Confirm Password:</label><br>

<div class="password-box">

    <input
        type="password"
        name="confirm_password"
        id="confirmPassword"
        required
    >

    <button
        type="button"
        class="password-toggle"
        onclick="toggleRegisterPassword('confirmPassword')"
    >
        👁
    </button>

</div>

<p id="passwordMessage"></p>

<br>

    <button type="submit">
        Register
    </button>


</form>

<div class="login-link">

    <p>Already have an account?</p>

    <a href="login.php">
        Login to CivicVoice →
    </a>

</div>

    </div>

</div>
<script>

function toggleRegisterPassword(id) {

    var password = document.getElementById(id);

    if (password.type === "password") {
        password.type = "text";
    } else {
        password.type = "password";
    }

}


function checkPasswords() {

    var password = document.getElementById("registerPassword").value;
    var confirmPassword = document.getElementById("confirmPassword").value;
    var message = document.getElementById("passwordMessage");

    if (confirmPassword === "") {

        message.innerHTML = "";

    } else if (password === confirmPassword) {

        message.innerHTML = "✓ Passwords match";
        message.style.color = "#166534";

    } else {

        message.innerHTML = "✕ Passwords do not match";
        message.style.color = "#b91c1c";

    }

}


document.getElementById("registerPassword").addEventListener("input", checkPasswords);

document.getElementById("confirmPassword").addEventListener("input", checkPasswords);
function validateRegistration() {

    var password = document.getElementById("registerPassword").value;
    var confirmPassword = document.getElementById("confirmPassword").value;

    if (password !== confirmPassword) {

        alert("Passwords do not match. Please enter the same password.");

        return false;

    }

    return true;

}
</script>
</body>

</html>