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
    if(empty($species)){
        $sql = 'SELECT attribute_name,attribute_id,image_url FROM advisor_attribute WHERE attribute_id IN (SELECT DISTINCT(species) FROM advisor_related_attributes)';
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
        // output data of each row    
            while ( $row = $result->fetch_assoc())  {
                $species[]=$row;
            }
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
    if(empty($fishingTypes)){
        $sql = 'SELECT attribute_name,attribute_id,image_url FROM advisor_attribute WHERE attribute_id IN (SELECT DISTINCT(fishing_type) FROM advisor_related_attributes WHERE species IN ('.implode(', ', $species_ids_array).'))';
        $result = $conn->query($sql);
    
        $fishingTypes = array();
        if ($result->num_rows > 0) {
        // output data of each row    
            while ( $row = $result->fetch_assoc())  {
                $fishingTypes[]=$row;
            }
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
    if(empty($technique)){
        $sql = 'SELECT attribute_name,attribute_id,image_url FROM advisor_attribute WHERE attribute_id IN (SELECT DISTINCT(technique) FROM advisor_related_attributes WHERE species IN ('.implode(', ', $species_ids_array).') AND fishing_type IN ('.implode(', ', $fishing_types_array).'))';
        $result = $conn->query($sql);
    
        $technique = array();
        if ($result->num_rows > 0) {
        // output data of each row    
            while ( $row = $result->fetch_assoc())  {
                $technique[]=$row;
            }
        }
    }
    echo json_encode($technique);
}

function getBrandsFromSubcategory($subcat){
    global $conn;

    $sql = 'SELECT id, NAME, image_url FROM core_brand WHERE id IN (SELECT DISTINCT(brand_id) FROM core_product WHERE category_id IN (SELECT id FROM core_category WHERE sub="'.$subcat.'")) order by name';
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

    $sql = 'SELECT * FROM core_product WHERE brand_id = '.$brand.' AND category_id IN (SELECT id FROM core_category WHERE sub="'.$subcat.'") ORDER BY NAME';
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

// upload report memo
function uploadReportMemo(){
    if ( 0 < $_FILES['file']['error'] ) {
        echo "failed";
    }
    else {
        global $APP_URL;
        move_uploaded_file($_FILES['file']['tmp_name'], 'uploads/reports/' . $_FILES['file']['name']);
        echo $APP_URL.'/fishingreport/uploads/reports/'.$_FILES['file']['name'];
    }
}

function uploadReportImage(){
    if(isset($_POST['image'])){
        $data = $_POST['image'];
        
        $image_array_1 = explode(";", $data);
        
        $image_array_2 = explode(",", $image_array_1[1]);
        
        $data = base64_decode($image_array_2[1]);
        global $APP_URL;
        
        $image_name = time().'.png';
        // $image_loc = $_SERVER['DOCUMENT_ROOT'].'/shopify-fishinmybestlife.com/fishingreport/uploads/reports/' .$image_name;
        $image_loc = $_SERVER['DOCUMENT_ROOT'].'/fishingreport/uploads/reports/' .$image_name;

        file_put_contents($image_loc, $data);

        $image_url = $APP_URL.'/fishingreport/uploads/reports/'.$image_name;
        echo $image_url;
    }
}

function updateTackleBoxAjax(){
    global $conn;
    $sql = "SELECT * FROM MemberTackleBox WHERE member_email_id ='".$_POST['email_id']."'";

    $tacklebox_result = $conn->query($sql);    
    if($tacklebox_result->num_rows==0){
        $sql = "INSERT INTO MemberTackleBox (member_email_id,variants_array) VALUES (?,?)";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "is", $_POST['email_id'],$_POST['added_gtin']);
            
            if(mysqli_stmt_execute($stmt)){                 
                // header("location: tacklebox.php");
            } else{
                echo $stmt->error;
                echo "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }else{
        $sql = "UPDATE MemberTackleBox SET variants_array=? WHERE member_email_id = ".$_POST['email_id'];
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s",$_POST['added_gtin']);
            
            if(mysqli_stmt_execute($stmt)){                 
                // header("location: tacklebox.php");
            } else{
                echo "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    $sql = "SELECT cb.name AS brandname, cg.option1_value, cg.variant_img, cg.gtin, cc.sub FROM core_gtin AS cg JOIN core_fmblvariant AS cv ON cv.gtin = cg.gtin JOIN core_product AS cp ON cp.id = cv.product_id JOIN core_brand AS cb ON cp.brand_id = cb.id JOIN core_category AS cc ON cp.category_id = cc.id AND FIND_IN_SET(cg.gtin, (SELECT variants_array FROM MemberTackleBox WHERE member_email_id = '".$_POST['email_id']."' LIMIT 1));";
    $tacklebox_result = $conn->query($sql);
    $variants_in_tacklebox = [];
    while($row = $tacklebox_result->fetch_array()){
        $variants_in_tacklebox[] = $row;
    }
    echo json_encode($variants_in_tacklebox);
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
}else if($_POST['action']=="upload-report-memo"){    
    // if($_POST['file']!=""){
    if(isset($_FILES['file']['name'])){
        uploadReportMemo();
    }else{
        header('HTTP/1.1 500 Internal Server Booboo');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => 'file is required', 'code' => 1337)));
    }
}else if($_POST['action']=="upload-report-image"){
    if(isset($_POST['image'])){
        uploadReportImage();
    }else{
        header('HTTP/1.1 500 Internal Server Booboo');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => 'file is required', 'code' => 1337)));
    }
}else if($_POST['action']=="update-tacklebox-ajax"){
    if(isset($_POST['email_id'])){
        updateTackleBoxAjax();
    }else{
        header('HTTP/1.1 500 Internal Server Booboo');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => 'file is required', 'code' => 1337)));
    }
}
?>