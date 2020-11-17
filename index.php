<?php
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "db.php";

global $conn;

$sql = "SELECT COUNT(*) FROM MemberTackleBox WHERE member_email_id =  ".$_SESSION['id'];
$membertypes = $conn->query($sql);

while($row = $membertypes->fetch_array()){
    $rows[] = $row;
}
$tackleboxcnt = $rows[0][0];

?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"> 
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"> 
        <title>Fish in My Best Life</title>
        <link rel="stylesheet" href="assets/css/styles.css">
        <meta name="google-signin-client_id" content="753213052944-4molte8riclfmm373egkuldknat2buh6.apps.googleusercontent.com">
        <script src="https://apis.google.com/js/platform.js" async defer></script>
    </head>
    <body class="index-page">
        <div class="page-content">
            <div class="login-header text-center"><a href="index.php"><img src="assets/imgs/logo.png" alt="Fish in my best life" /></a></div>
            <h1 class="page-title">Welcome back</h1>
            <div class="content">
                <a class="btn-primary full" href="edit-profile.php">Edit profile</a>
                <a class="btn-primary full" href="edit-fishing-profile-setup.php">Edit fishing profile</a>
                <?php if($tackleboxcnt==0){ ?>
                    <a class="btn-primary full" href="create-tacklebox.php">Create a tacklebox</a>                
                <?php }else {?>
                    <a class="btn-primary full" href="tacklebox.php">Your tacklebox</a>                
                    <a class="btn-primary full" href="create-fishing-report.php">Create a fishing report</a>
                <?php } ?>                
                <a class="btn-secondary full" href="logout.php">Log out</a>
            </div>
        </div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
</body>
</html>