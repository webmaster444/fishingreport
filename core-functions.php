<?php
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

?>