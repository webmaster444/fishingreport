<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}
 
// Include config file
require_once "db.php";

global $conn;
// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if username is empty
    if(empty(trim($_POST["email"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["email"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT id, email, password FROM memberemails WHERE email = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if username exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, so start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;                            
                            
                            // Redirect user to welcome page
                            header("location: index.php");
                        } else{
                            // Display an error message if password is not valid
                            $password_err = "The password you entered was not valid.";
                        }
                    }
                } else{
                    // Display an error message if username doesn't exist
                    $username_err = "No account found with that username.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($conn);
}
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"> 
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"> 
        <title>Log in | Fish in My Best Life</title>
        <link rel="stylesheet" href="assets/css/all.min.css">        
        <link rel="stylesheet" href="assets/css/styles.css">        
        <!-- <meta name="google-signin-client_id" content="1083339568092-tngtmitfqhl5tvoqflcs14aq9ddjrhss.apps.googleusercontent.com">
        <script src="https://apis.google.com/js/platform.js" async defer></script> -->        
        <script>
        window.fbAsyncInit = function() {
            FB.init({
            appId      : '374272643779763',
            cookie     : true,
            xfbml      : true,
            version    : 'v9.0'
            });
            
            FB.AppEvents.logPageView();   
            
        };

        (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>
    </head>
    <body class="login-page" id="login-page">
        <div class="login-content page-content">
            <div class="login-header text-center"><a href="index.php"><img src="assets/imgs/logo.png" alt="Fish in my best life" /></a></div>
            <h1 class="page-title">Sign in</h1>
            <div class="login-form-container content">
                <div class="full">
                    <span>Sign in with your email and password</span>
                    <form action="login.php" method="POST">
                        <div class="ct-form-control">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" required value="<?php isset($_POST['email'])?$_POST['email']:""; ?>"/>
                            <p class="err-msg"><?php echo $username_err;?></p>
                        </div>
                        <div class="ct-form-control">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" required/>
                            <p class="err-msg"><?php echo $password_err;?></p>
                        </div>
                        <button type="submit" class="btn-primary">Sign in</button>
                        <p><span>Need an account? </span><a href="signup.php">Sign up</a></p>
                    </form>
                </div>
                <div class="full">
                    <p>Sign In with your social account</p>
                    <!-- <div class="g-signin2" data-longtitle="true"></div>                     -->
                    <!-- <fb:login-button 
                    data-scope="public_profile,email"
                    data-max-rows="1"
                    data-size="large"
                    data-button-type="continue_with"
                    
                    onlogin="checkLoginState();">
                    </fb:login-button>                     -->
                    <button id="customBtn" class="social-login google"><i class="fab fa-google"></i><div>Continue with Google</div></button>
                    <button class="social-login facebook" onclick="facebookLogin();"><i class="fab fa-facebook-f"></i><div>Continue with facebook</div></button>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script src="https://apis.google.com/js/api:client.js"></script>
        <script>
        var googleUser = {};
        var startApp = function() {
            gapi.load('auth2', function(){
            // Retrieve the singleton for the GoogleAuth library and set up the client.
            auth2 = gapi.auth2.init({
                client_id: '1083339568092-tngtmitfqhl5tvoqflcs14aq9ddjrhss.apps.googleusercontent.com',
                cookiepolicy: 'single_host_origin',
                // Request scopes in addition to 'profile' and 'email'
                // scope: 'https://www.googleapis.com/auth/userinfo.email,https://www.googleapis.com/auth/userinfo.profile'
            });
            attachSignin(document.getElementById('customBtn'));
            });
        };

        function attachSignin(element) {
            auth2.attachClickHandler(element, {},
            function(googleUser) {
                let email = googleUser.Mt.tu;
                $.ajax({
                    url: "core.php",
                    type: "POST",
                    data: {action: "social-login-ajax", email:email},
                    success: function(result) {
                        window.location.href = result;
                    },
                    error: function(err) {
                        console.log(err);
                    }
                });
            }, function(error) {
                console.log(JSON.stringify(error, undefined, 2));
            });
        }



        
        
//   function statusChangeCallback(response) {  // Called with the results from FB.getLoginStatus().    
//     if (response.status === 'connected') {   // Logged into your webpage and Facebook.
//       testAPI();  
//     } 
//   }


//   function checkLoginState() {               // Called when a person is finished with the Login Button.
//     FB.getLoginStatus(function(response) {   // See the onlogin handler
//       statusChangeCallback(response);
//     });
//   }
 
  function testAPI() {
    FB.api('/me', {fields: 'name,email' }, function(response) {
        $.ajax({
            url: "core.php",
            type: "POST",
            data: {action: "social-login-ajax", email:response.email},
            success: function(result) {
                window.location.href = result;
            },
            error: function(err) {
                console.log(err);
            }
        });
    });
  }
  function facebookLogin(){
    FB.login(function(response) {
        if(response.status=="connected"){
            testAPI();
        }
    }, {scope: 'public_profile,email'});
  }

  startApp();
        </script>
        <script type="text/javascript">
        function onSignIn(googleUser) {
            var profile = googleUser.getBasicProfile();
            console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
            console.log('Name: ' + profile.getName());
            console.log('Image URL: ' + profile.getImageUrl());
            console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
        }
        function signOut() {
            var auth2 = gapi.auth2.getAuthInstance();
            auth2.signOut().then(function () {
            console.log('User signed out.');
            });
        }
        </script>
    </body>
</html>