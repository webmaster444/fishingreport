<?php
session_start();
// Include config file
require_once "db.php";

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: profile-setup.php");
    exit;
}
 
global $conn;
// Define variables and initialize with empty values
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validate username
    if(empty(trim($_POST["email"]))){
        $username_err = "Please enter your email address.";
    } else{        
        // Prepare a select statement
        $sql = "SELECT id FROM memberemails WHERE email = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = trim($_POST["email"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 0){
                    $username = trim($_POST["email"]);                    
                } else{
                    $username_err = "This email is already taken.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have atleast 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Check input errors before inserting in database
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO memberemails (email, password) VALUES (?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_password);
            
            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to login page                      
                session_start();
                            
                // // Store data in session variables
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = mysqli_insert_id($conn);
                $_SESSION["username"] = $username;   
                header("location: profile-setup.php");
            } else{
                echo "Something went wrong. Please try again later.";
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
    </head>
    <body class="login-page" id="signup-page">
        <div class="login-content page-content">            
            <div class="login-header text-center"><a href="index.php"><img src="assets/imgs/logo.png" alt="Fish in my best life" /></a></div>
            <h1 class="page-title">Register</h1>
            <div class="login-form-container flex-wrapper content">
                <div class="full">
                    <span>Sign up with your email and password</span>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                        <div class="form-control">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" required value="<?php echo isset($_POST['email'])?$_POST['email']:'' ?>"/>
                            <p class="err-msg"><?php echo $username_err; ?></p>
                        </div>
                        <div class="form-control">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" required/>
                            <p class="err-msg"><?php echo $password_err; ?></p>
                        </div>
                        <div class="form-control">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" required/>
                            <p class="err-msg"><?php echo $confirm_password_err; ?></p>
                        </div>
                        <button type="submit" class="btn-primary">Sign up</button>
                        <p><span>Already have an account? </span><a href="login.php">Sign in</a></p>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>