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

$sql = "SELECT species,fishing_types,fishing_technique FROM member_detail_fishing WHERE email_id = ".$_SESSION['id'];
$member_detail_result = $conn->query($sql);  

$sql = "SELECT member_type_id FROM Member WHERE member_email_id = ".$_SESSION['id'];
$member_type = $conn->query($sql);  

$row = $member_type->fetch_array();
$member_role_no = $row[0];

$sql = "SELECT member_type FROM MemberType WHERE member_type_id = ".$member_role_no;
$member_type_query= $conn->query($sql);

$member_role = "";
if($member_type_query!=false){
    if($member_type_query->num_rows!=0){
        $row = $member_type_query->fetch_array();
        $member_role = $row[0];
    }    
}

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
    <body class="index-page no-slider" id="index-page">
        <div class="page-content">
            <div class="login-header text-center"><a href="index.php"><img src="assets/imgs/logo.png" alt="Fish in my best life" /></a></div>
            <h1 class="page-title">Welcome back</h1>
            <div class="content">
                <a class="btn-primary full" href="edit-profile.php">Edit profile</a>
                <a class="btn-primary full" href="reset-password.php">Reset password</a>
                <?php if($member_detail_result->num_rows==0){ ?>
                    <a class="btn-primary full" href="fishing-profile-setup.php">Create fishing profile</a>
                <?php } else { ?>
                    <a class="btn-primary full" href="edit-fishing-profile-setup.php">Edit fishing profile</a>
                <?php } ?>
                <?php if($tackleboxcnt==0){ ?>
                    <a class="btn-primary full" href="create-tacklebox.php">Create a tacklebox</a>                
                <?php }else {?>
                    <a class="btn-primary full" href="tacklebox.php">Your tacklebox</a>        
                    <a class="btn-primary full" href="create-fishing-report.php">Create a fishing report</a>
                <?php } ?>                

                <?php if($member_role == 'Administrator'||$member_role=="Super"){?>
                    <a class="btn-primary full" href="create-weekly-report.php">Create a weekly report</a>
                <?php } ?>

                <!-- <?php if($member_role == 'Super'){?>
                    <a class="btn-primary full" href="manage-users.php">Manage Users</a>
                <?php } ?> -->

                <a class="btn-secondary full" href="logout.php">Log out</a>
            </div>
        </div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
</body>
</html>