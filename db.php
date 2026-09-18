<?php

$conn = mysqli_connect("localhost", "root", "", "civicvoice");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

?>