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

        ?>

        <!DOCTYPE html>
        <html>

        <head>

            <meta charset="UTF-8">

            <meta name="viewport"
                  content="width=device-width, initial-scale=1.0">

            <title>Registration Successful - CivicVoice</title>

            <link rel="stylesheet" href="style.css?v=3">

            <style>

                /* ============================= */
                /* REGISTRATION SUCCESS PAGE */
                /* ============================= */

                .success-page {

                    min-height: 100vh;

                    display: flex;

                    justify-content: center;

                    align-items: center;

                    padding: 30px;

                    box-sizing: border-box;

                    background:
                        linear-gradient(
                            135deg,
                            #eef6ff,
                            #f8fbff
                        );

                }

                .success-card {

                    width: 100%;

                    max-width: 500px;

                    background: #ffffff;

                    border-radius: 18px;

                    padding: 45px 35px;

                    text-align: center;

                    box-sizing: border-box;

                    box-shadow:
                        0 15px 40px rgba(0, 0, 0, 0.10);

                    border: 1px solid #e5e7eb;

                }

                .success-icon {

                    width: 75px;

                    height: 75px;

                    margin: 0 auto 20px;

                    border-radius: 50%;

                    display: flex;

                    align-items: center;

                    justify-content: center;

                    background: #dcfce7;

                    color: #15803d;

                    font-size: 40px;

                    font-weight: bold;

                }

                .success-card h1 {

                    margin: 0 0 12px;

                    font-size: 30px;

                    color: #166534;

                }

                .success-card p {

                    margin: 0 0 28px;

                    font-size: 16px;

                    line-height: 1.6;

                    color: #4b5563;

                }

                .success-button {

                    display: inline-block;

                    padding: 13px 30px;

                    background: #2563eb;

                    color: #ffffff;

                    text-decoration: none;

                    border-radius: 8px;

                    font-size: 16px;

                    font-weight: 600;

                    transition: 0.2s ease;

                }

                .success-button:hover {

                    background: #1d4ed8;

                    transform: translateY(-1px);

                }

                .success-brand {

                    margin-top: 25px;

                    font-size: 14px;

                    color: #6b7280;

                }

                .success-brand strong {

                    color: #2563eb;

                }

                /* ============================= */
                /* MOBILE */
                /* ============================= */

                @media (max-width: 600px) {

                    .success-page {

                        padding: 20px;

                    }

                    .success-card {

                        padding: 35px 22px;

                    }

                    .success-card h1 {

                        font-size: 25px;

                    }

                    .success-card p {

                        font-size: 15px;

                    }

                    .success-icon {

                        width: 65px;

                        height: 65px;

                        font-size: 34px;

                    }

                    .success-button {

                        width: 100%;

                        box-sizing: border-box;

                    }

                }

            </style>

        </head>

        <body>

            <div class="success-page">

                <div class="success-card">

                    <div class="success-icon">
                        ✓
                    </div>

                    <h1>
                        Registration Successful!
                    </h1>

                    <p>
                        Your CivicVoice citizen account has been
                        created successfully.
                    </p>

                    <a href="login.php"
                       class="success-button">

                        Go to Login →

                    </a>

                    <div class="success-brand">

                        Welcome to <strong>CivicVoice</strong>

                    </div>

                </div>

            </div>

        </body>

        </html>

        <?php

    } else {

        echo "Registration failed: " . mysqli_error($conn);

    }

    exit;
}

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="style.css?v=3">

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

        <form method="POST"
              onsubmit="return validateRegistration()">

            <label>Full Name:</label>

            <br>

            <input
                type="text"
                name="citizen_name"
                required
            >

            <br><br>


            <label>Email:</label>

            <br>

            <input
                type="email"
                name="email"
                required
            >

            <br><br>


            <label>Username:</label>

            <br>

            <input
                type="text"
                name="username"
                required
            >

            <br><br>


            <label>Password:</label>

            <br>

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


            <label>Confirm Password:</label>

            <br>

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

            <p>
                Already have an account?
            </p>

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

    var password =
        document.getElementById("registerPassword").value;

    var confirmPassword =
        document.getElementById("confirmPassword").value;

    var message =
        document.getElementById("passwordMessage");


    if (confirmPassword === "") {

        message.innerHTML = "";

    }

    else if (password === confirmPassword) {

        message.innerHTML = "✓ Passwords match";

        message.style.color = "#166534";

    }

    else {

        message.innerHTML = "✕ Passwords do not match";

        message.style.color = "#b91c1c";

    }

}


document
    .getElementById("registerPassword")
    .addEventListener("input", checkPasswords);


document
    .getElementById("confirmPassword")
    .addEventListener("input", checkPasswords);


function validateRegistration() {

    var password =
        document.getElementById("registerPassword").value;

    var confirmPassword =
        document.getElementById("confirmPassword").value;


    if (password !== confirmPassword) {

        alert(
            "Passwords do not match. Please enter the same password."
        );

        return false;

    }

    return true;

}

</script>

</body>

</html>