<?php
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "db.php";

// get all subcategories
global $conn;

$sql = "SELECT DISTINCT(sub) FROM core_category";
$subcat_result = $conn->query($sql);

while($row = $subcat_result->fetch_array()){
    $subcats[] = $row;
}

// $sql = "SELECT cg.option1_value, cg.variant_img, cg.gtin, cc.sub FROM core_gtin AS cg JOIN core_fmblvariant AS cv ON cv.gtin = cg.gtin JOIN core_product AS cp ON cp.id = cv.product_id JOIN core_category AS cc ON cp.category_id = cc.id AND FIND_IN_SET(cg.gtin, (SELECT variants_array FROM membertacklebox WHERE member_email_id = '".$_SESSION['id']."' LIMIT 1));";
$sql = "SELECT cb.name AS brandname, cg.option1_value, cg.variant_img, cg.gtin, cc.sub FROM core_gtin AS cg JOIN core_fmblvariant AS cv ON cv.gtin = cg.gtin JOIN core_product AS cp ON cp.id = cv.product_id JOIN core_brand AS cb ON cp.brand_id = cb.id JOIN core_category AS cc ON cp.category_id = cc.id AND FIND_IN_SET(cg.gtin, (SELECT variants_array FROM MemberTackleBox WHERE member_email_id = '".$_SESSION['id']."' LIMIT 1));";
$tacklebox_result = $conn->query($sql);

while($row = $tacklebox_result->fetch_array()){
    $variants_in_tacklebox[] = $row;
}
// $sql = 'SELECT DISTINCT(sub) FROM core_category WHERE id IN (SELECT DISTINCT(category_id) FROM core_product WHERE id IN (SELECT DISTINCT(product_id) FROM core_fmblvariant WHERE gtin IN (SELECT gtin FROM core_gtin WHERE FIND_IN_SET(gtin, (SELECT variants_array FROM membertacklebox WHERE member_email_id = 1 LIMIT 1)))));';
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"> 
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"> 
        <title>Fish in My Best Life</title>
        <link rel="stylesheet" href="assets/css/styles.css">        
        <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    </head>
    <body class="tacklebox-page">
        <div class="page-content">
            <div class="login-header text-center"><a href="index.php"><img src="assets/imgs/logo.png" alt="Fish in my best life" /></a></div>        
            <h1 class="text-center">Tackle box</h1>
            <div class="content">
                <div class="scroll-content">
                <div class="categories-wrapper">
                    <ul class="vertical">                
                    <?php 
                        foreach($subcats as $subcat){                        
                            $filtered_vars = array_filter($variants_in_tacklebox, function($var) use ($subcat){
                                return $var['sub']==$subcat[0];
                            });
                            echo '<li class="category">'.$subcat[0];
                            echo '<ul class="sub-vertical">';
                            foreach($filtered_vars as $variant){
                                echo '<li class="vertical-item"><div><img src="'.$variant['variant_img'].'">'.$variant['brandname'].'-'.$variant['option1_value'].'</div></li>';
                            }
                            echo '</ul>';
                            echo '</li>';
                        }
                    ?>
                    </ul>
                </div>
                <div class="slick-slider-wrapper">
                    <div class="brands-wrapper">
                        <h2 class="section-title"></h2>
                        <ul class="vertical">

                        </ul>
                    </div>
                </div> 
                </div>   
            </div>
            <div class="page-footer">
            <a href="index.php" class="pull-left btn-primary">Back to home</a>
            <a href="create-fishing-report.php" class="pull-right btn-primary">Create Fishing Report</a>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
        <script type="text/javascript"></script>
    </body>
</html>