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
    $_SESSION["citizen_name"] = $user["citizen_name"];
 
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
 
<html> 
 
<head> 
 
    <link rel="stylesheet" href="style.css?v=2"> 
 
    <title>CivicVoice Login</title> 
 
</head> 
 
 
<body> 
 
 
<div class="civic-container"> 
 
 
    <!-- LEFT SIDE : CIVICVOICE INFORMATION --> 
 
    <div class="civic-intro"> 
 
 
        <!-- CIVICVOICE LOGO --> 
 
        <div class="civic-logo"> 
 
            <img 
                src="logo.jpg" 
                alt="CivicVoice Logo" 
            > 
 
        </div> 
 
 
        <!-- CIVICVOICE NAME --> 
 
        <h1> 
            CivicVoice 
        </h1> 
 
 
        <!-- PROJECT TITLE --> 
 
        <h3> 
            Intelligent Public Grievance & Issue Tracking System 
        </h3> 
 
 
        <!-- DESCRIPTION --> 
 
        <p class="intro-text"> 
 
            CivicVoice is a digital platform that allows citizens 
            to report public issues, submit grievances and participate 
            in improving their community. 
 
        </p> 
 
 
        <!-- FEATURES --> 
 
        <div class="civic-features"> 
 
 
            <!-- REPORT ISSUES --> 
 
            <div class="feature"> 
 
                <div class="feature-icon"> 
                    📢 
                </div> 
 
                <div> 
 
                    <strong> 
                        Report Issues 
                    </strong> 
 
                    <p> 
                        Report public problems and bring important 
                        civic issues to the attention of authorities. 
                    </p> 
 
                </div> 
 
            </div> 
 
 
            <!-- CITIZEN PARTICIPATION --> 
 
            <div class="feature"> 
 
                <div class="feature-icon"> 
                    🤝 
                </div> 
 
                <div> 
 
                    <strong> 
                        Citizen Participation 
                    </strong> 
 
                    <p> 
                        Participate in creating a cleaner, safer 
                        and better community. 
                    </p> 
 
                </div> 
 
            </div> 
 
 
        </div> 
 
 
        <!-- TAGLINE --> 
 
        <p class="civic-tagline"> 
 
            "Your Voice. Your City. Your Change." 
 
        </p> 
 
 
    </div> 
 
 
 
    <!-- RIGHT SIDE : LOGIN --> 
 
    <div class="login-section"> 
 
 
        <h2> 
            Citizen Login 
        </h2> 
 
 
        <p class="login-subtitle"> 
 
            Login to access your CivicVoice account 
 
        </p> 
 
 
        <form method="POST"> 
 
 
            <!-- USERNAME --> 
 
            <label> 
                Username: 
            </label> 
 
            <br> 
 
            <input 
                type="text" 
                name="username" 
                required 
            > 
 
            <br><br> 
 
 
            <!-- PASSWORD --> 
 
            <label> 
                Password: 
            </label> 
 
            <br> 
 
 
            <div class="password-box"> 
 
                <input 
                    type="password" 
                    name="password" 
                    id="password" 
                    required 
                > 
 
                <button 
                    type="button" 
                    class="password-toggle" 
                    onclick="showPassword()" 
                > 
                    👁 
                </button> 
 
            </div> 
 
 
            <br><br> 
 
 
            <!-- CAPTCHA --> 
 
            <label> 
 
                CAPTCHA: 
 
                <?php echo $_SESSION["captcha"]; ?> 
 
            </label> 
 
 
            <button 
                type="button" 
                onclick="refreshCaptcha()" 
            > 
                🔄 
            </button> 
 
 
            <br> 
 
 
            <input 
                type="text" 
                name="captcha" 
                required 
            > 
 
 
            <br><br> 
 
 
            <!-- LOGIN BUTTON --> 
 
            <button type="submit"> 
 
                Login 
 
            </button> 
 
<!-- REGISTER LINK --> 
 
<div class="register-section"> 
 
    <p> 
        New to CivicVoice? 
    </p> 
 
    <a href="register.php"> 
        Create a Citizen Account → 
    </a> 
 
</div> 
 
 

             </div> 
 
         </form>

       
    </div>

</div>
 </div> <!-- civic-container -->

<a href="index.php" class="back-home-btn">
    🏠 Back to Home
</a>


<script> 
 
 
/* Show / Hide Password */ 
 
function showPassword() { 
 
    var password = document.getElementById("password"); 
 
 
    if (password.type === "password") { 
 
        password.type = "text"; 
 
    } else { 
 
        password.type = "password"; 
 
    } 
 
} 
 
 
/* Refresh CAPTCHA */ 
 
function refreshCaptcha() { 
 
    location.reload(); 
 
} 
 
 
</script> 
 
 
</body> 
 
</html> 