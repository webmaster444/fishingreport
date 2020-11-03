<?php
require_once "db.php";
require_once "config.php";
function getAllCities(){
    global $conn;
    $sql = "SELECT city, city_id FROM advisor_city";
    $result = $conn->query($sql);

    $cities = array();
    if ($result->num_rows > 0) {
    // output data of each row    
        while ( $row = $result->fetch_assoc())  {
            $cities[]=$row;
        }
    }
    echo json_encode($cities);
}

function getSpeciesFromCity($city_id){    
    global $conn;
    $sql = 'SELECT attribute_name,attribute_id,image_url FROM advisor_attribute WHERE attribute_id IN (SELECT DISTINCT(species) FROM advisor_related_attributes WHERE city="'.$city_id.'")';
    $result = $conn->query($sql);

    $species = array();
    if ($result->num_rows > 0) {
    // output data of each row    
        while ( $row = $result->fetch_assoc())  {
            $species[]=$row;
        }
    }
    echo json_encode($species);
}

function getFishingTypesFromCityAndSpecies($city_id, $species_ids_array){
    global $conn;
    $sql = 'SELECT attribute_name,attribute_id,image_url FROM advisor_attribute WHERE attribute_id IN (SELECT DISTINCT(fishing_type) FROM advisor_related_attributes WHERE city="'.$city_id.'" AND species IN ('.implode(', ', $species_ids_array).'))';
    $result = $conn->query($sql);

    $fishingTypes = array();
    if ($result->num_rows > 0) {
    // output data of each row    
        while ( $row = $result->fetch_assoc())  {
            $fishingTypes[]=$row;
        }
    }
    echo json_encode($fishingTypes);
}

function getTechniqueFromCitySpeciesType($city_id, $species_ids_array, $fishing_types_array){
    global $conn;
    $sql = 'SELECT attribute_name,attribute_id,image_url FROM advisor_attribute WHERE attribute_id IN (SELECT DISTINCT(technique) FROM advisor_related_attributes WHERE city="'.$city_id.'" AND species IN ('.implode(', ', $species_ids_array).') AND fishing_type IN ('.implode(', ', $fishing_types_array).'))';
    $result = $conn->query($sql);

    $technique = array();
    if ($result->num_rows > 0) {
    // output data of each row    
        while ( $row = $result->fetch_assoc())  {
            $technique[]=$row;
        }
    }
    echo json_encode($technique);
}

function getBrandsFromSubcategory($subcat){
    global $conn;

    $sql = 'SELECT id, NAME FROM core_brand WHERE id IN (SELECT DISTINCT(brand_id) FROM core_product WHERE category_id IN (SELECT id FROM core_category WHERE sub="'.$subcat.'"))';
    $result = $conn->query($sql);

    $brands = array();
    if ($result->num_rows > 0) {
    // output data of each row    
        while ( $row = $result->fetch_assoc())  {
            $brands[]=$row;
        }
    }
    echo json_encode($brands);
}

function getProductsFromBrandAndCat($subcat, $brand){
    global $conn;

    $sql = 'SELECT * FROM core_product WHERE brand_id = '.$brand.' AND category_id IN (SELECT id FROM core_category WHERE sub="'.$subcat.'")';
    $result = $conn->query($sql);

    $products = array();
    if ($result->num_rows > 0) {
    // output data of each row    
        while ( $row = $result->fetch_assoc())  {
            $products[]=$row;
        }
    }
    echo json_encode($products);
}

function getVariantsFromProduct($product_id){
    global $conn;

    $sql = 'SELECT option1_value,variant_id, gtin, title, TYPE, variant_img, url FROM core_gtin WHERE gtin IN (SELECT gtin FROM core_fmblvariant WHERE product_id = '.$product_id.')';
    $result = $conn->query($sql);

    $variants = array();
    if ($result->num_rows > 0) {
    // output data of each row    
        while ( $row = $result->fetch_assoc())  {
            $variants[]=$row;
        }
    }
    echo json_encode($variants);
}
// upload report image
function uploadReportImage(){
    if ( 0 < $_FILES['file']['error'] ) {
        echo "failed";
    }
    else {
        global $APP_URL;
        move_uploaded_file($_FILES['file']['tmp_name'], 'uploads/reports/' . $_FILES['file']['name']);
        echo $APP_URL.'/uploads/reports/'.$_FILES['file']['name'];
    }
}
if($_POST['action']=="getAllCities"){
    getAllCities();
}else if($_POST['action']=="getFishingTypes"){
    if($_POST['city_id']!=""&&$_POST['species']!=""){
        getFishingTypesFromCityAndSpecies($_POST['city_id'], $_POST['species']);
    }else{
        header('HTTP/1.1 500 Internal Server Booboo');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => 'city_id and species are required', 'code' => 1337)));
    }
}else if($_POST['action']=="getSpecies"){
    if($_POST['city_id']!=""){
        getSpeciesFromCity($_POST['city_id']);
    }else{
        header('HTTP/1.1 500 Internal Server Booboo');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => 'city_id is required', 'code' => 1337)));
    }
}else if($_POST['action']=="getTechnique"){
    if($_POST['city_id']!=""&&$_POST['species']!=""&&$_POST['fishing_types']!=""){
        getTechniqueFromCitySpeciesType($_POST['city_id'], $_POST['species'],$_POST['fishing_types']);
    }else{
        header('HTTP/1.1 500 Internal Server Booboo');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => 'city_id is required', 'code' => 1337)));
    }
}else if($_POST['action']=="getBrandsFromCategory"){
    // get all brands from core_brand table
    // get all categories from subcategory text
    if($_POST['subcat']!=""){
        getBrandsFromSubcategory($_POST['subcat']);
    }else{
        header('HTTP/1.1 500 Internal Server Booboo');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => 'subcat is required', 'code' => 1337)));
    }
}else if($_POST['action']=="getProductsFromBrandAndCat"){
    // get all products from core_product
    // get all categories from subcategory text
    if($_POST['subcat']!=""&&$_POST['brand']!=""){
        getProductsFromBrandAndCat($_POST['subcat'],$_POST['brand']);
    }else{
        header('HTTP/1.1 500 Internal Server Booboo');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => 'subcat and brand is required', 'code' => 1337)));
    }
}else if($_POST['action']=="getVariantsFromProduct"){
    // get gtin from core_product
    // get title, type, variant_img using gtin from core_gtin
    if($_POST['product_id']!=""){
        getVariantsFromProduct($_POST['product_id']);
    }else{
        header('HTTP/1.1 500 Internal Server Booboo');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => 'product_id is required', 'code' => 1337)));
    }
}else if($_POST['action']=="upload-report-image"){    
    // if($_POST['file']!=""){
    if(isset($_FILES['file']['name'])){
        uploadReportImage();
    }else{
        header('HTTP/1.1 500 Internal Server Booboo');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => 'file is required', 'code' => 1337)));
    }
}
?>