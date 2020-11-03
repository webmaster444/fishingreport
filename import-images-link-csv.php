<?php

// set image_url in advisor_attribute
require_once "db.php";

global $conn;

$csv = array_map('str_getcsv', file('results-20201022-202252.csv'));

foreach ($csv as $row){
    $attribute_type = $row[1];    
    if(strpos($attribute_type, '.jpg') !== false){
        $strArray = explode('-', $attribute_type);
        array_pop($strArray);
        
        $brandName = implode(" ", $strArray);
        if($brandName!=""){
            $sql = 'SELECT * FROM core_brand WHERE name LIKE "%'.$brandName.'%"';    
            $results=$conn->query($sql);
            if($results->num_rows==0){
                array_pop($strArray);
                $newBrandName = implode(" ", $strArray);
                if($newBrandName!=""){
                    $sql = 'SELECT * FROM core_brand WHERE name LIKE "%'.$newBrandName.'%"';
                    $results = $conn->query($sql);
                    if($results->num_rows!=0){
                        $sql = 'UPDATE core_brand SET image_url="'.$row[2].'" WHERE name LIKE "%'.$newBrandName.'%"';
                        $results = $conn->query($sql);
                        var_dump($results);
                    }
                }                
            }else{
                $sql = 'UPDATE core_brand SET image_url="'.$row[2].'" WHERE name LIKE "%'.$brandName.'%"';
                $results = $conn->query($sql);
                var_dump($results);
            }
        }
    }else{
        // $sql = 'SELECT * FROM advisor_attribute WHERE attribute_name LIKE "%'.$row[1].'%"';
        $sql = 'UPDATE advisor_attribute SET image_url="'.$row[2].'" WHERE attribute_name = "'.$row[1].'"';
        $results = $conn->query($sql);

        // var_dump($results);
        // echo $row[1].':::::::::::::::'.$results->num_rows.'<br/>';
    }    
    // $sql = 'UPDATE advisor_attribute SET image_url="'.$row[1].'" WHERE attribute_name = "'.$row[0].'"';
    // $results = $conn->query($sql);
    // var_dump($results);
}
?>