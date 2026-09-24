<?php

session_start();

/* Clear department session */
unset($_SESSION["department_user_id"]);
unset($_SESSION["department_name"]);
unset($_SESSION["department_id"]);

/* Redirect to department login */
header("Location: department_login.php");
exit;

?>