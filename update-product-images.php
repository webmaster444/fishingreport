<?php

// set img_url in core_product
require_once "db.php";

global $conn;

// get one variant id per product_id from core_fmblvariants table 
// $sql = 'SELECT cfv.id, cfv.product_id FROM core_fmblvariant AS cfv WHERE cfv.product_id IN (SELECT id FROM core_product) GROUP BY cfv.product_id;';
$csv = array_map('str_getcsv', file('product-variant.csv'));

foreach ($csv as $row){
    $variant_id = $row[0];
    $product_id = $row[1];    
    $sql = 'SELECT url from core_fmblimages WHERE variant_id = "'.$variant_id.'" LIMIT 1';
    $image_results = $conn->query($sql);

    $image = [];
    while($row = $image_results->fetch_array()){
        $image[] = $row;
    }
    $sql = 'UPDATE core_product SET img_url="'.$image[0][0].'" WHERE id = "'.$product_id.'"';
    $results = $conn->query($sql);
    var_dump($results);
}
?>