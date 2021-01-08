<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
 
// Include config file
require_once "db.php";

global $conn;

// Define variables and initialize with empty values
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";

$sql = "SELECT id,email FROM memberemails;";
$member_emails_result = $conn->query($sql);
$member_emails = [];
while($row = $member_emails_result->fetch_array()){
    $member_emails[] = $row;
}

?>
 
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"> 
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"> 
        <title>Fish in My Best Life</title>        
        <meta name="google-signin-client_id" content="753213052944-4molte8riclfmm373egkuldknat2buh6.apps.googleusercontent.com">
        <script src="https://apis.google.com/js/platform.js" async defer></script>
        <link rel="stylesheet" href="assets/css/all.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.23/datatables.min.css"/>
        <link rel="stylesheet" href="assets/css/styles.css">
    </head>
    <body class="reset-password-page" id="reset-password-page">
    <div class="page-content">
        <div class="login-header text-center"><a href="index.php"><img src="assets/imgs/logo.png" alt="Fish in my best life" /></a></div>
        <h1 class="page-title">Manage Users</h1>
        <div class="content">
        <div class="scroll-wrapper">
        <table id="member-emails-table">
        <thead>
        <tr>
        <th>No</th>
        <th>Email</th>
        <th></th>
        </tr>
        </thead>
        <tbody>
        <?php
            $i = 0;
            foreach ($member_emails as $email) {                                
                $i++;
                echo '<tr><td>'.$i.'</td><td>'.$email['email'].'</td><td><a href="#" class="change_pwd_popup" user-id="'.$email['id'].'"><i class="fas fa-pen"></i></a></td></tr>';
            }
        ?>
        </tbody>
        </table>
        </div>        
        </div>
    </div>    
    <div class="modal fade" id="change-pwd-modal" tabindex="-1" role="dialog" aria-labelledby="Change Password" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Password</h5>
                    <a href="#" class="modal-close" onClick="$('#change-pwd-modal').popup('hide');"><i class="fas fa-times"></i></a>
                </div>
                <div class="modal-body">
                    <div class="ct-form-control">
                        <label>User Email</label>
                        <input type="email" name="new_password" class="ct-form-control" id="selected_user_email" value="" disabled>
                        <input type="hidden" id="hidden_selected_user_id" value="" />
                        <p class="err-msg"></p>
                    </div>
                    <div class="ct-form-control">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="ct-form-control" id="new_pwd_input" value="">
                        <p class="err-msg"></p>
                    </div>
                    <div class="ct-form-control">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" class="ct-form-control" id="new_confirm_pwd_input">
                        <p class="err-msg"></p>
                    </div>
                    <div class="ct-form-control">
                        <button type="submit" class="btn btn-primary" id="change-pwd-submit">Change Password</button>
                        <a class="btn btn-link" onClick="$('#change-pwd-modal').popup('hide');">Cancel</a>
                    </div>
                </div>                
            </div>
        </div>

    </div>	
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.23/datatables.min.js"></script>
    <script type="text/javascript" src="assets/js/jquery.popupoverlay.js"></script>
    <script>        
        $(function(){
            $("#change-pwd-modal").popup();
            $('#member-emails-table').DataTable({
                initComplete: function() {
                    $(this.api().table().container()).find('input').parent().wrap('<form>').parent().attr('autocomplete', 'off');
                }
            });            
            $(document).on('click', '.change_pwd_popup',function(){
                $("#change-pwd-modal").popup('show');
                $("#hidden_selected_user_id").val($(this).attr('user-id'));
                $("#selected_user_email").val($(this).closest('tr').find('td:nth-child(2)').html());                
            })
            $("#change-pwd-submit").on('click',function(){
                if($("#new_pwd_input").val()!=""){
                    if($("#new_pwd_input").val()==$("#new_confirm_pwd_input").val()){
                        $("#change-pwd-submit").html("Submitting");
                        $.ajax({
                            url:'core.php',
                            method:'POST',
                            data:{id:$("#hidden_selected_user_id").val(),password:$("#new_pwd_input").val(),action:'reset-password-ajax'},
                            success:function(data)
                            {
                                $("#change-pwd-submit").html("Change Password");
                                $("#change-pwd-modal").popup('hide');
                                $("#new_pwd_input").val("");
                                $("#new_confirm_pwd_input").val("");
                            },
                            error: function(err){
                                $("#change-pwd-submit").html("Change Password");
                                alert('Sorry something went wrong');
                            }
                        });
                    }
                }                
            })
        })
    </script>
</body>
</html>