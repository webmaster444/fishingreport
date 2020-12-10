<?php
set_time_limit(0);
// insert Member, MemberEmails, member_detail_fishing products from shopify to shopify_advisor_city_products
require_once "../db.php";

global $conn;

$csv = array_map('str_getcsv', file('Angler-Profile.csv'));
$i = 0;
$products_array = array();
foreach ($csv as $row){                            
    // var_dump($row);
    $email = $row[5];
    $sql = "SELECT id FROM memberemails WHERE email = ?";

    
    if($stmt = mysqli_prepare($conn, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "s", $param_username);
        
        // Set parameters
        $param_username = trim($email);
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            /* store result */
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) == 0){
                $username = trim($email);                    
                $sql = "INSERT INTO memberemails (email, password) VALUES (?, ?)";
         
                if($stmt = mysqli_prepare($conn, $sql)){
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_password);
                    
                    // Set parameters
                    $param_username = $username;
                    $param_password = password_hash('temp12345', PASSWORD_DEFAULT); // Creates a password hash
                    
                    // Attempt to execute the prepared statement
                    if(mysqli_stmt_execute($stmt)){            
                        echo $email.'::::'.mysqli_insert_id($conn);
                    } else{                       
                        echo 'something happened';
                    }                                
                }   
            } else{                
                $stmt->bind_result($id);
                $cuEmailId = 0;
                while($stmt->fetch()){
                    $cuEmailId = $id;
                }
                echo $cuEmailId;
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }

        // Close statement
        mysqli_stmt_close($stmt);
    }                   
}
?>