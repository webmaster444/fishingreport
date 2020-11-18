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
        <title>Fish in My Best Life</title>
        <link rel="stylesheet" href="assets/css/styles.css">
        <meta name="google-signin-client_id" content="753213052944-4molte8riclfmm373egkuldknat2buh6.apps.googleusercontent.com">
        <script src="https://apis.google.com/js/platform.js" async defer></script>
        
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

        
        
  function statusChangeCallback(response) {  // Called with the results from FB.getLoginStatus().
    console.log('statusChangeCallback');
    console.log(response);                   // The current login status of the person.
    if (response.status === 'connected') {   // Logged into your webpage and Facebook.
      testAPI();  
    } else {                                 // Not logged into your webpage or we are unable to tell.
      document.getElementById('status').innerHTML = 'Please log ' +
        'into this webpage.';
    }
  }


  function checkLoginState() {               // Called when a person is finished with the Login Button.
    FB.getLoginStatus(function(response) {   // See the onlogin handler
      statusChangeCallback(response);
    });
//     FB.login(function(response) {
//   // handle the response
//   console.log(response);
// }, {scope: 'email'});
  }


//   window.fbAsyncInit = function() {
//     FB.init({
//       appId      : '{app-id}',
//       cookie     : true,                     // Enable cookies to allow the server to access the session.
//       xfbml      : true,                     // Parse social plugins on this webpage.
//       version    : '{api-version}'           // Use this Graph API version for this call.
//     });


//     FB.getLoginStatus(function(response) {   // Called after the JS SDK has been initialized.
//       statusChangeCallback(response);        // Returns the login status.
//     });
//   };
 
  function testAPI() {                      // Testing Graph API after login.  See statusChangeCallback() for when this call is made.
    console.log('Welcome!  Fetching your information.... ');
    FB.api('/me', function(response) {
        console.log(response);
      console.log('Successful login for: ' + response.name);
      document.getElementById('status').innerHTML =
        'Thanks for logging in, ' + response.name + '!';
    });
  }
        </script>
    </head>
    <body class="login-page">
        <div class="login-content page-content">
            <div class="login-header text-center"><a href="index.php"><img src="assets/imgs/logo.png" alt="Fish in my best life" /></a></div>
            <h1 class="page-title">Sign in</h1>
            <div class="login-form-container flex-wrapper content">
                <div class="half">
                    <span>Sign In with your social account</span>
                    <div class="g-signin2" data-onsuccess="onSignIn"></div>
                    <fb:login-button 
                    scope="public_profile,email"
                    onlogin="checkLoginState();">
                    </fb:login-button>
                </div>
                <!-- <div class="half"> -->
                <div class="full">
                    <span>Sign in with your email and password</span>
                    <form action="login.php" method="POST">
                        <div class="form-control">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" required value="<?php isset($_POST['email'])?$_POST['email']:""; ?>"/>
                            <p class="err-msg"><?php echo $username_err;?></p>
                        </div>
                        <div class="form-control">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" required/>
                            <p class="err-msg"><?php echo $password_err;?></p>
                        </div>
                        <button type="submit" class="btn-primary">Sign in</button>
                        <p><span>Need an account? </span><a href="signup.php">Sign up</a></p>
                    </form>
                </div>
            </div>
        </div>
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