<?php
set_time_limit(0);
// insert products from shopify to core_gtin
require_once "db.php";

global $conn;

$csv = array_map('str_getcsv', file('gint.csv'));

foreach ($csv as $row){    
    $sql = "INSERT INTO core_gtin (product_id, handle, title, vendor, type, url, variant_id, option1_value, variant_sku,gtin,variant_img,variant_price) VALUES (?, ?,?,?,?,?,?,?,?,?,?,?)";
        
    if($stmt = mysqli_prepare($conn, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "sssssssssssd", $product_id,$handle,$title,$vendor,$type,$url,$variant_id,$option_1value,$variant_sku,$gint,$variant_img,$variant_price);
        
        // Set parameters
        $product_id = $row[0];
        $handle = $row[1];
        $title = $row[2];
        $vendor = $row[3];
        $type = $row[4];
        $url = $row[5];
        $variant_id = $row[6];
        $option_1value = $row[7];
        $variant_sku = $row[8];
        $gint = str_pad($row[9], 12, "0", STR_PAD_LEFT);
        $variant_img = $row[10];
        $variant_price = $row[11];
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            /* store result */
            mysqli_stmt_store_result($stmt);
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }

        // Close statement
        mysqli_stmt_close($stmt);
    }
    $results = $conn->query($sql);
}
?>