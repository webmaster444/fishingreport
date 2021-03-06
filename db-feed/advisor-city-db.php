<?php
set_time_limit(0);
// insert Angler Advisor | City | type products from shopify to shopify_advisor_city_products
require_once "../db.php";

global $conn;

$csv = array_map('str_getcsv', file('advisor-city-data-db.csv'));
$i = 0;
$products_array = array();
foreach ($csv as $row){            
    if($row[4]=="Angler Advisor | City | Techniques" | $row[4]=="Angler Advisor | City | Species" | $row[4]=="Angler Advisor | City | Fishing Types"){        
        if(!in_array($row[0], $products_array)){
            ++$i;
            $sql = "INSERT INTO shopify_advisor_city_products (shopify_product_id, shopify_product_handle, shopify_product_title, shopify_product_type) VALUES (?,?,?,?)";
        
            if($stmt = mysqli_prepare($conn, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "ssss", $product_id,$handle,$title,$type);
                
                // Set parameters
                $product_id = $row[0];
                $handle = $row[1];
                $title = $row[3];
                $type = $row[4];                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    /* store result */
                    mysqli_stmt_store_result($stmt);
                } else{
                    echo $stmt->error;
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                mysqli_stmt_close($stmt);
            }else{
                echo $conn->error;
            }
            $results = $conn->query($sql);                        
            array_push($products_array, $row[0]);
        }       
    }    
}
?>