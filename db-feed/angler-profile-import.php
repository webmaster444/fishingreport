<?php
set_time_limit(0);
// insert Member, MemberEmails, member_detail_fishing products from shopify to shopify_advisor_city_products
require_once "../db.php";

global $conn;

$csv = array_map('str_getcsv', file('Angler-Profile.csv'));
$i = 0;
$products_array = array();
foreach ($csv as $key=>$row){                            
    if($key>0){
        $email = $row[5];
        $fname  = $row[2];
        $lname  = $row[3];
        $phone = $row[4];
        $zipcode = $row[6];
        $species = str_replace(',','',$row[7]);
        $species_array = explode(";",$species);
        $species_str = getAttributeString($species_array,1);

        $fishing_types = str_replace(',','',$row[8]);
        $fishing_types_array = explode(";",$fishing_types);
        $fishing_types_str = getAttributeString($fishing_types_array,2);

        $fishing_techniques = str_replace(',','',$row[9]);
        $fishing_techniques_array = explode(";",$fishing_techniques);
        $fishing_techniques_str = getAttributeString($fishing_techniques_array,3);

        $own_boat = $row[11]=='YES'?1:0;
        $member_type_id = 3; // set member type as Angler for default
        $sql = "SELECT id FROM memberemails WHERE email = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = trim($email);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $cuEmailId = 0;
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
                            $cuEmailId = mysqli_insert_id($conn);
                        } else{                       
                            echo 'something happened';
                        }                                
                    }   
                } else{                
                    $stmt->bind_result($id);
                    while($stmt->fetch()){
                        $cuEmailId = $id;
                    }
                }
                echo $cuEmailId."========>".$email.'<br/>';                

                $sql = "INSERT INTO Member (first_name, last_name,email,phone,postal_code,member_type_id,own_boat,member_email_id) VALUES (?, ?,?,?,?,?,?,?)";
            
                if($stmt = mysqli_prepare($conn, $sql)){
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, "sssssiii", $fname,$lname,$email,$phone,$zipcode,$member_type_id,$own_boat,$cuEmailId);
                                        
                    // Attempt to execute the prepared statement
                    if(mysqli_stmt_execute($stmt)){            
                        $cu_member_id = mysqli_insert_id($conn);
                    } else{                       
                        echo 'something happened';
                    }                                
                }   

                $sql = "INSERT INTO member_detail_fishing (email_id, species,fishing_types,fishing_technique) VALUES (?,?,?,?)";
            
                if($stmt = mysqli_prepare($conn, $sql)){
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, "isss", $cuEmailId,$species_str,$fishing_types_str,$fishing_techniques_str);
                                        
                    // Attempt to execute the prepared statement
                    if(mysqli_stmt_execute($stmt)){            
                        $cu_member_id = mysqli_insert_id($conn);
                    } else{                       
                        echo 'something happened';
                    }                                
                }  

            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }       
    }                
}

function getAttributeString($attr_array,$attr_type){
    global $conn;
    $attr_id_array = array();
    foreach($attr_array as $attr){
        $sql = "SELECT attribute_id FROM advisor_attribute WHERE attribute_name LIKE '%".$attr."%' AND attribute_type_id = ".$attr_type.";";        
        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_row()) {
                array_push($attr_id_array, $row[0]);
            }
            /* free result set */
            $result->close();
        }
    }
    return join(",",$attr_id_array);
}
?>