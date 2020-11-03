<?php

ini_set('max_execution_time', 0);

// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "fishinbestlife";

$servername = "mysql.cgqj9s1nvhal.us-east-1.rds.amazonaws.com";
$username = "programmer";
$password = "FMBL333";
$dbname = "fmbl_st2";

// Create connection
$conn = new mysqli($servername, $username, $password,$dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}