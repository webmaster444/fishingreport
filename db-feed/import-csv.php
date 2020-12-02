<?php

// set image_url in advisor_attribute for species
require_once "../db.php";

global $conn;

$csv = array_map('str_getcsv', file('results-20201027-144012.csv'));

foreach ($csv as $row){
    $sql = 'UPDATE advisor_attribute SET image_url="'.$row[2].'" WHERE attribute_name = "'.$row[1].'"';
    $results = $conn->query($sql);
    var_dump($results);
}
?>