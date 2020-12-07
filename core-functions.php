<?php
require_once "db.php";
require_once "config.php";
function getAttributeNamesFromIds($array){
    global $conn;

    $sqlArray = implode(',', $array);
    $sql = 'SELECT attribute_name FROM advisor_attribute WHERE attribute_Id IN ('.$sqlArray.')';
    $result = $conn->query($sql);

    $variants = array();
    if ($result->num_rows > 0) {
    // output data of each row    
        while ( $row = $result->fetch_assoc())  {
            $variants[]=$row;
        }
    }
    return $variants;
}

function getAdvisorProductHandlesFromIds($array){
    global $conn;
    $sqlArray = implode(',', $array);
    $sql = 'SELECT attribute_name FROM advisor_attribute WHERE attribute_Id IN ('.$sqlArray.')';
    
    $result = $conn->query($sql);

    $variants = array();
    if ($result->num_rows > 0) {
    // output data of each row    
        while ( $row = $result->fetch_assoc())  {
            $variants[]=$row;
        }
    }

    $handles = array();
    foreach($variants as $variant){
        $attribute_name = $variant['attribute_name'];
        $cleaned_name = clean($attribute_name);
        $name_words = explode("-", $cleaned_name);
        $sql = 'SELECT shopify_product_handle From shopify_advisor_city_products WHERE';
        foreach($name_words as $index=>$word){
            if($index==0){
                $sql .= ' shopify_product_title LIKE "%'.$word.'%"';
            }else{
                $sql .= ' AND shopify_product_title LIKE "%'.$word.'%"';
            }            
        }
        $sql .= ' LIMIT 1';

        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
        // output data of each row    
            while ( $row = $result->fetch_assoc())  {
                $handles[]=$row['shopify_product_handle'];
            }
        }  
    }
    return $handles;    
}

function getProductsHandleFromGtins($gtins){
    global $conn;
    $sqlArray = implode(',', $gtins);
    $sql = 'SELECT handle FROM core_gtin WHERE gtin IN ('.$sqlArray.')';
    
    $result = $conn->query($sql);

    $variants = array();
    if ($result->num_rows > 0) {
    // output data of each row    
        while ( $row = $result->fetch_assoc())  {
            $variants[]=$row;
        }
    }

    $handles = array();
    foreach($variants as $variant){
        $handle = $variant['handle'];
        $handles[] = $handle;
    }
    return $handles;  
}

function clean($string) {
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}
?>