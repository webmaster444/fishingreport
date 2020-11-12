<?php
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "db.php";
require_once "core-functions.php";
require_once "config.php";
// get all subcategories
global $conn;

$sql = "SELECT DISTINCT(sub) FROM core_category WHERE sub IN ('Hooks', 'Line', 'Reels', 'Lures', 'Terminal Tackle') ORDER BY sub";
$subcat_result = $conn->query($sql);

while($row = $subcat_result->fetch_array()){
    $subcats[] = $row;
}

$loggedin_user_citys = array();
$sql = "SELECT city_id, city FROM advisor_city WHERE city IN (SELECT city FROM Member WHERE member_email_id = ".$_SESSION['id'].')';
$loggedin_user_city = $conn->query($sql);

while($row = $loggedin_user_city->fetch_array()){
    $loggedin_user_citys[] = $row;
}
$userCity = $loggedin_user_citys[0];
$sql = 'SELECT attribute_id, attribute_name,image_url FROM advisor_attribute WHERE FIND_IN_SET(attribute_id, (SELECT species FROM member_detail_fishing WHERE email_id = "'.$_SESSION['id'].'" LIMIT 1)) ORDER BY attribute_name';
$species_result = $conn->query($sql);
while($row = $species_result->fetch_array()){
    $species[] = $row;
}

$sql = 'SELECT attribute_id, attribute_name,image_url FROM advisor_attribute WHERE FIND_IN_SET(attribute_id, (SELECT fishing_types FROM member_detail_fishing WHERE email_id = "'.$_SESSION['id'].'" LIMIT 1))';
$fishingtype_result = $conn->query($sql);
while($row = $fishingtype_result->fetch_array()){
    $fishing_types[] = $row;
}

$sql = 'SELECT attribute_id, attribute_name,image_url FROM advisor_attribute WHERE FIND_IN_SET(attribute_id, (SELECT fishing_technique FROM member_detail_fishing WHERE email_id = "'.$_SESSION['id'].'" LIMIT 1))';
$technique_result = $conn->query($sql);

while($row = $technique_result->fetch_array()){
    $techniques[] = $row;
}

$sql = "SELECT cb.name AS brandname, cg.option1_value, cg.variant_img, cg.gtin, cc.sub FROM core_gtin AS cg JOIN core_fmblvariant AS cv ON cv.gtin = cg.gtin JOIN core_product AS cp ON cp.id = cv.product_id JOIN core_brand AS cb ON cp.brand_id = cb.id JOIN core_category AS cc ON cp.category_id = cc.id AND FIND_IN_SET(cg.gtin, (SELECT variants_array FROM MemberTackleBox WHERE member_email_id = '".$_SESSION['id']."' LIMIT 1));";
$tacklebox_result = $conn->query($sql);
$variants_in_tacklebox = [];
while($row = $tacklebox_result->fetch_array()){
    $variants_in_tacklebox[] = $row;
}

$sql = "SELECT attribute_id, attribute_name, image_url FROM advisor_attribute WHERE attribute_id IN (SELECT DISTINCT(fishing_type) FROM advisor_related_attributes WHERE city = ".$userCity['city_id'].") ORDER BY attribute_name;";
$alltype_result = $conn->query($sql);

if($alltype_result->num_rows!=0){
    while($row = $alltype_result->fetch_array()){
        $alltypes[] = $row;
    }
}else{
    $sql = "SELECT attribute_id, attribute_name, image_url FROM advisor_attribute WHERE attribute_id IN (SELECT DISTINCT(fishing_type) FROM advisor_related_attributes) ORDER BY attribute_name;";
    $alltype_result = $conn->query($sql);
    while($row = $alltype_result->fetch_array()){
        $alltypes[] = $row;
    }
}
$sql = "SELECT attribute_id, attribute_name, image_url FROM advisor_attribute WHERE attribute_id IN (SELECT DISTINCT(technique) FROM advisor_related_attributes WHERE city = ".$userCity['city_id'].") ORDER BY attribute_name;";
$alltechnique_result = $conn->query($sql);
$alltechnique = [];

if($alltechnique_result->num_rows!=0){
    while($row = $alltechnique_result->fetch_array()){
        $alltechnique[] = $row;
    }
}else{
    $sql = "SELECT attribute_id, attribute_name, image_url FROM advisor_attribute WHERE attribute_id IN (SELECT DISTINCT(technique) FROM advisor_related_attributes) ORDER BY attribute_name;";
    $alltechnique_result = $conn->query($sql);
    while($row = $alltechnique_result->fetch_array()){
        $alltechnique[] = $row;
    }
}

$sql = "SELECT variants_array from MemberTackleBox where member_email_id='".$_SESSION['id']."' LIMIT 1";
$gtinresult = $conn->query($sql);

$selected_gtins = [];
while($row = $gtinresult->fetch_array()){
    $selected_gtins[] = $row;
}

$notifications = array();
if($_SERVER["REQUEST_METHOD"] == "POST"){ 
        
    $metafields = array();

    //trip_date;
    $metafield = array();
    $metafield['key'] = 'trip_date';
    $metafield['value'] = $_POST['trip_date'];
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;

    // weather_conditions
    $metafield = array();
    $metafield['key'] = 'weather_conditions';
    $metafield['value'] = $_POST['weatherconditions'];
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;

    // sea_conditions
    $metafield = array();
    $metafield['key'] = 'sea_conditions';
    $metafield['value'] = $_POST['seawaveheight'];
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;

    //fishing_depth
    $metafield = array();
    $metafield['key'] = 'fishing_depth';
    $metafield['value'] = $_POST['fishingdepth'];
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;

    //rating
    $metafield = array();
    $metafield['key'] = 'rating';
    $metafield['value'] = $_POST['rating'];
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;

    $species_meta_value = implode("||",array_map(function($d){return $d['attribute_name'];}, getAttributeNamesFromIds($_POST['species'])));    
    $fishing_types_meta_value = implode("||",array_map(function($d){return $d['attribute_name'];}, getAttributeNamesFromIds($_POST['fishing_types'])));
    $fishing_technique_meta_value = implode("||",array_map(function($d){return $d['attribute_name'];}, getAttributeNamesFromIds($_POST['fishing_technique'])));

    //species
    $metafield = array();
    $metafield['key'] = 'species';
    $metafield['value'] = $species_meta_value;
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;

    //fishing types
    $metafield = array();
    $metafield['key'] = 'fishing_types';
    $metafield['value'] = $fishing_types_meta_value;
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;

    //techniques
    $metafield = array();
    $metafield['key'] = 'techniques';
    $metafield['value'] = $fishing_technique_meta_value;
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;
        
    $loggedin_user_citys = array();
    $sql = "SELECT city_id, city FROM advisor_city WHERE city IN (SELECT city FROM Member WHERE member_email_id = ".$_SESSION['id'].')';
    $loggedin_user_city = $conn->query($sql);

    while($row = $loggedin_user_city->fetch_array()){
        $loggedin_user_citys[] = $row;
    }

    $location_city = $loggedin_user_citys[0]['city'];

    //city
    $metafield = array();
    $metafield['key'] = 'location_city';
    $metafield['value'] = $location_city;
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;

    //global description
    $metafield = array();
    $metafield['key'] = 'description_tag';
    $metafield['value'] = "Find Fishing Reports, Charters, Tackle and information to create the best fishing memories of a lifetime for you and your family and friends.  Fishin' My Best Life";
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "global";
    $metafields[] = $metafield;

    $formated_trip_date = date_format(date_create($_POST['trip_date']),"n/j/y");

    $title = $location_city.' '.$formated_trip_date.' | Daily Fishing Report | What\'s Biting Now';

    $metafield = array();
    $metafield['key'] = 'title_tag';
    $metafield['value'] = $title;
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "global";
    $metafields[] = $metafield;
    
    $selected_gtins_str = implode(",", $_POST['selected_variants']);    
    // store it to db
    $sql = "INSERT INTO member_fishing_report (member_email_id,trip_date,used_gtin,city) VALUES (?,?,?,?)";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "isss", $_SESSION['id'],$formated_trip_date, $selected_gtins_str, $location_city);
        
        if(mysqli_stmt_execute($stmt)){                 
            // header("location: tacklebox.php");
            $mapping_id = mysqli_insert_id($conn);
            $notification = "Fishing report is successfully created";
            $notifications[] = $notification;
        } else{
            echo $stmt->error;
            echo "Something went wrong. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    }

    $metafield = array();
    $metafield['key'] = 'mapping_id';
    $metafield['value'] = $mapping_id;
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;

    $metafield = array();
    $metafield['key'] = 'ad_block_1';
    $metafield['value'] = 'https://cdn.shopify.com/s/files/1/0084/4785/2604/files/imgpsh_fullsize_anim.png?v=1595934383';
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;

    $metafield = array();
    $metafield['key'] = 'ad_block_url';
    $metafield['value'] = 'https://www.fishinmybestlife.com/products/west-palm-beach-fl-bait-and-tackle-shop-bait';
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;


    $metafield = array();
    $metafield['key'] = 'box_1_url';
    $metafield['value'] = 'https://form.jotform.com/201472484667058';
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;

    $metafield = array();
    $metafield['key'] = 'box_2_url';
    $metafield['value'] = 'https://www.fishinmybestlife.com/collections/fishing-report';
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;

    $metafield = array();
    $metafield['key'] = 'box_3_url';
    $metafield['value'] = 'https://www.fishinmybestlife.com/collections/charter-boats';
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;

    $metafield = array();
    $metafield['key'] = 'book_url';
    $metafield['value'] = 'https://www.fishinmybestlife.com/collections/charter-boats';
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;

    $metafield = array();
    $metafield['key'] = 'memo_url';
    $metafield['value'] = $_POST['memo_uploaded'];
    $metafield['value_type'] = "string";
    $metafield['namespace'] = "report";
    $metafields[] = $metafield;

    $description = $_POST['description'];
    $product_images =  array();
    $tmp_image = array();
    $tmp_image['src'] = $_POST['image_uploaded'];
    $product_images[] = $tmp_image;
    $products_array = array(
        "product" => array( 
            "title"        => $title,
            "body_html"    => $description,
            "handle"       => 'catch-logs-view-'.$mapping_id,
            "template_suffix" => "report",
            "vendor"       => "FishinMyBestLife",
            "product_type" => "Angler Advisor | Fishing Reports | Catch Logs",
            "tags"         => "AnglerAdvisor:JotformFishingReports",
            "published"    => false ,
            "images"       =>$product_images,
            "status"       => "draft",
            "metafields"   => $metafields
        )
    );

    global $apiKey;
    global $password;
    global $domain;
    $SHOPIFY_API = "https://".$apiKey.":".$password."@".$domain."/admin/products.json";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $SHOPIFY_API);
    $headers = array( "Authorization: Basic ".base64_encode($apiKey.":".$password),  
    "Content-Type: application/json", 
    "charset: utf-8");
    curl_setopt($curl, CURLOPT_HTTPHEADER,$headers);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_VERBOSE, 0);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($products_array));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 

    $response = curl_exec ($curl);
    curl_close ($curl);

    if(array_key_exists('errors', json_decode($response))){
        $notification = 'Failed to create report on shopify store';
        $notifications[] = $notification;
    }else{
        $notification = 'Successfully added report to store';
        $notifications[] = $notification;
        mail('jlmobile710@gmail.com','Fishing Report Submission',"One report is created");
    }    
}

?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"> 
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"> 
        <title>Fish in My Best Life</title>
        <link rel="stylesheet" href="assets/css/datepicker.min.css">        
        <link rel="stylesheet" href="assets/css/dropzone.min.css" />
		<link href="assets/css/cropper.min.css" rel="stylesheet"/>
        <link rel="stylesheet" href="assets/css/all.min.css">
        <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
        <link rel="stylesheet" href="assets/css/styles.css">                       
    </head>
    <body class="create-tacklebox-page">
        <div class="page-content">
            <div class="login-header text-center"><a href="index.php"><img src="assets/imgs/logo.png" alt="Fish in my best life" /></a></div>        
            <h1 class="page-title">Create a fishing report</h1>            
            <div class="back-home"><i class="fas fa-chevron-left"></i></div>
            <div class="content">
                <div class="notifications-wrapper">
                <?php foreach ($notifications as $notification){ ?>
                    <div class="notification"><?php echo $notification;?></div>
                <?php } ?>       
                </div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="slick-slider-wrapper">
                        <div class="slide">
                            <p class="err-msg"></p>
                            <div class="form-control">
                                <label for="trip_date">Upload Image<span class="required">*</span></label>
                                <input id="report_image" type="file" name="report_image" accept="image/x-png,image/gif,image/jpeg"/>
                                <img src="assets/imgs/loader.gif" alt="Loading" class="img-loader loader hide"/>
                                <div class="thumbnail hide"><img src="" alt="Preview"/></div>
                            </div>
                        </div>
                        <div class="slide">
                            <p class="err-msg"></p>
                            <div class="form-control">
                                <label for="trip_date">Trip date</label>
                                <input id="trip_date" type="text" name="trip_date" data-toggle="datepicker" />
                            </div>
                        </div>
                        <div class="slide">
                        <p class="err-msg"></p>
                            <label class="form-field-title"> Whether conditions <span class="required">*</span></label>
                            <div class="flex-wrapper">
                            <ul class="vertical full">
                            <li class="vertical-item"><label><div><img src="assets/imgs/cloudy.png" class="sea-condition-images"> Cloudy</div><input type="radio" name="weatherconditions" value="cloudy" required /></label></li>
                            <li class="vertical-item"><label><div><img src="assets/imgs/raining.png" class="sea-condition-images"> Rainy</div><input type="radio" name="weatherconditions" value="raining" required /></label></li>
                            <li class="vertical-item"><label><div><img src="assets/imgs/sunny.png" class="sea-condition-images"> Sunny</div><input type="radio" name="weatherconditions" value="sunny" required /></label></li>
                            </ul>                        
                            </div>                        
                        </div>
                        <div class="slide">
                            <p class="err-msg"></p>
                            <label class="form-field-title"> Sea Wave Height</label>
                            <div class="flex-wrapper values-wrapper">
                            <ul class="vertical full">
                                <li class="vertical-item"><label><div><img src="assets/imgs/0t2ft.png" class="sea-condition-images"> 0 to 2 FT</div><input type="radio" name="seawaveheight" value="0-2" required /></label></li>
                                <li class="vertical-item"><label><div><img src="assets/imgs/2.5to4ft.png" class="sea-condition-images"> 2.5 to 4 FT</div><input type="radio" name="seawaveheight" value="2.5-4" required /></label></li>
                                <li class="vertical-item"><label><div><img src="assets/imgs/4.5ft-over.png" class="sea-condition-images"> 4.5 FT  and over</div><input type="radio" name="seawaveheight" value="4-4.5" required /></label></li>
                            </ul>
                            </div>                        
                        </div>
                        <div class="slide">
                            <p class="err-msg"></p>
                            <label class="form-field-title"> Fishing Depth </label>
                            <div class="flex-wrapper values-wrapper">
                                <ul class="vertical full">
                                <li class="vertical-item"><label><div><input type="radio" name="fishingdepth" value="0-50" /> 0'-50'</div></label></li>
                                <li class="vertical-item"><label><div>
                                    <input type="radio" name="fishingdepth" value="50-80" /> 50'-80'</div></label></li>
                                    <li class="vertical-item"><label><div>
                                    <input type="radio" name="fishingdepth" value="80-100" /> 80'-100'</div></label></li>
                                    <li class="vertical-item"><label><div>
                                    <input type="radio" name="fishingdepth" value="100-300" /> 100'-300'</div></label></li>
                                    <li class="vertical-item"><label><div>
                                    <input type="radio" name="fishingdepth" value="300-500" /> 300'-500'</div></label></li>
                                    <li class="vertical-item"><label><div>
                                    <input type="radio" name="fishingdepth" value="500-800" /> 500'-800'</div></label></li>
                                    <li class="vertical-item"><label><div>
                                    <input type="radio" name="fishingdepth" value="800+" /> 800' or more</div></label></li>
                                </ul>
                            </div>                        
                        </div>
                        <div class="slide species-wrapper">
                        <div class="scroll-wrapper">
                            <h2 class="section-title">Species</h2>
                            <p class="err-msg"></p>
                            <div class="values-wrapper full">
                            <div class="search-input"><input type="text" class="autocomplete" /></div>
                                <ul class="vertical full">
                            <?php 
                                foreach($species as $specie){
                                    echo '<li class="vertical-item"><label><div><img src="'.$specie['image_url'].'" alt="'.$specie['attribute_name'].'"/>'.$specie['attribute_name'].'</div><input type="checkbox" name="species[]" value="'.$specie['attribute_id'].'" /></label></li>';
                                }
                            ?>
                            </ul>
                            </div>
                            </div>
                        </div>

                        <div class="slide fishing-types-wrapper">
                        <div class="scroll-wrapper">
                            <h2 class="section-title">Fishing Type</h2>
                            <p class="err-msg"></p>
                            <div class="values-wrapper full">    
                                <div class="search-input"><input type="text" class="autocomplete" /></div>                    
                                <ul class="vertical full">
                                <?php                                 
                                    foreach($alltypes as $specie){    
                                        $individualClass = in_array($specie, $fishing_types)?"tacklebox":'all hide';                                    
                                        echo '<li class="vertical-item '.$individualClass.'"><label><div><img src="'.$specie['image_url'].'" alt="'.$specie['attribute_name'].'"/>';
                                        echo $specie['attribute_name'];
                                        echo '</div>';                                    
                                        echo '<input type="checkbox" name="fishing_types[]" value="'.$specie['attribute_id'].'"></label>';
                                        echo '</li>';
                                    }
                                ?>
                                </ul>
                                <a href="#" class="see_more">See More</a>
                            </div>
                                </div>
                        </div>

                        <div class="slide fishing-technique-wrapper">
                            <div class="scroll-wrapper">
                            <h2 class="section-title">Technique</h2>
                            <p class="err-msg"></p>
                            <div class="values-wrapper full">
                            <div class="search-input"><input type="text" class="autocomplete" /></div>
                                <ul class="vertical full">
                            <?php 
                                foreach($alltechnique as $specie){     
                                    $individualClass = in_array($specie, $techniques)?"tacklebox":'all hide';                                                                                       
                                    echo '<li class="vertical-item '.$individualClass.'"><label><div><img src="'.$specie['image_url'].'" alt="'.$specie['attribute_name'].'"/>'.$specie['attribute_name'].'</div>';
                                    echo '<input type="checkbox" name="fishing_technique[]" value="'.$specie['attribute_id'].'"></label>';
                                    echo '</li>';
                                }
                            ?>
                            </ul>
                            <a href="#" class="see_more">See More</a>
                            </div>
                            </div>
                        </div>
                        <div class="slide your-tackle-box">
                            <div class="scroll-wrapper">
                            <h2 class="section-title">Your Tacklebox</h2><a href="#" class="add_more_tackle btn-primary"><i class="fas fa-pen"></i>Edit tackle box</a>
                            <p class="err-msg"></p>
                            <div class="values-wrapper">
                            <ul class="vertical full">                
                                <?php 
                                    foreach($subcats as $subcat){                        
                                        $filtered_vars = array_filter($variants_in_tacklebox, function($var) use ($subcat){
                                            return $var['sub']==$subcat[0];
                                        });
                                        if(sizeof($filtered_vars)!=0){
                                            echo '<li class="category">'.$subcat[0];                                    
                                            echo '<ul class="sub-vertical">';
                                            foreach($filtered_vars as $variant){
                                                echo '<li class="vertical-item"><label><div><img src="'.$variant['variant_img'].'">'.$variant['brandname'].'-'.$variant['option1_value'].'</div><input type="checkbox" name="selected_variants[]" value="'.$variant['gtin'].'"></label></li>';
                                            }
                                            echo '</ul>';
                                            echo '</li>';
                                        }                                    
                                    }
                                ?>
                                </ul>
                            </div>
                        </div>
                        </div>  
                        <div class="slide memo-box">
                            <h2 class="section-title">Your Memo</h2>
                            <p class="err-msg"></p>
                            <div class="form-control">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"></textarea>
                            </div>
                            <div class="form-control">
                            <label for="description">Video</label>
                            <input type="file" id="memo_video" name="memo_video" accept="video/mp4,video/x-m4v,video/*"/>
                            </div>
                        </div>
                        <div class="rating-wrapper">
                            <h2 class="section-title">Rate your fishing</h2>
                            <p class="err-msg"></p>
                            <div class='rating-stars text-center'>
                                <ul id='stars'>
                                    <li class='star' title='Poor' data-value='1'> <i class='fa fa-star fa-fw'></i> </li>
                                    <li class='star' title='Fair' data-value='2'> <i class='fa fa-star fa-fw'></i> </li>
                                    <li class='star' title='Good' data-value='3'> <i class='fa fa-star fa-fw'></i> </li>
                                    <li class='star' title='Excellent' data-value='4'> <i class='fa fa-star fa-fw'></i> </li>
                                    <li class='star' title='WOW!!!' data-value='5'> <i class='fa fa-star fa-fw'></i> </li>
                                </ul>
                            </div>
                            <input type="hidden" id="hidden_rating" name="rating" required value=""/> 
                            <input type="hidden" id="hidden_img_uploaded" name="image_uploaded" value=""/>
                            <input type="hidden" id="hidden_memo_uploaded" name="memo_uploaded" value=""/>
                        </div>   
                    </div>                 
                </form>            
            </div>   
            <div class="page-footer">
            <div class="flex-wrapper space-between slider-buttons-wrapper">
                <a href="#" class="btn-primary invisible slick-prev">Prev</a>
                <a href="#" class="btn-primary slick-next">Next</a>            
                <a href="#" class="btn-primary hide" id="report_submit">Submit</a>         
            </div>         
            </div>
            <div class='drawer-bottom'>
                <div class="drawer-header">
                    <h2>Add to your tacklebox</h2>
                    <a href="#" class="drawer-close"><i class="fas fa-times"></i></a>
                </div>
                <div class="drawer-slick-wrapper">
                    <div class="drawer-slide">
                        <div class="drawer-scroll-wrapper">
                        <div class="search-input"><input type="text" class="autocomplete" /></div>
                    <ul class="vertical">                
                    <?php 
                        foreach($subcats as $subcat){
                            echo '<li class="vertical-item category"><label><span>'.$subcat[0].'</span><i class="fas fa-chevron-right"></i></label></li>';
                        }
                    ?>
                    </ul>
                    </div>
                    </div>
                    <div class="brands-wrapper">
                    <div class="drawer-scroll-wrapper">
                            <h2 class="section-title">Brands</h2>
                            <div class="search-input"><input type="text" class="autocomplete" /></div>
                            <ul class="vertical">

                            </ul>
                    </div>
                        </div>
                        <div class="products-wrapper">
                        <div class="drawer-scroll-wrapper">
                            <h2 class="section-title">Products</h2>
                            <div class="search-input"><input type="text" class="autocomplete" tabindex="0"></div>
                            <ul class="vertical">

                            </ul>
                    </div>
                        </div>
                        <div class="variants-wrapper">
                        <div class="drawer-scroll-wrapper">
                            <h2 class="section-title">Variants</h2>
                            <div class="search-input"><input type="text" class="autocomplete" /></div>
                            <ul class="vertical"></ul>                            
                        </div>   
                    </div>
                </div>
                <div class="drawer-footer">                    
                    <input type="hidden" id="added_gtin" name="added_gtin" value="<?php echo $selected_gtins[0]['variants_array'];?>"/>
                    <button type="button" class="btn-primary" id="update-tacklebox">Update tacklebox</button>
                </div>
            </div>
            <div class="drawer-overlay hide"></div>
        </div>
            <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
			  	<div class="modal-dialog modal-lg" role="document">
			    	<div class="modal-content">
			      		<div class="modal-header">
			        		<h5 class="modal-title">Crop Image Before Upload</h5>
			        		<a href="#" class="modal-close"><i class="fas fa-times"></i></a>
			      		</div>
			      		<div class="modal-body">
			        		<div class="img-container">
			            		<div class="row">
			                		<div class="col-md-8">
			                    		<img src="" id="sample_image" />
			                		</div>
			                		<div class="col-md-4">
			                    		<div class="preview"></div>
			                		</div>
			            		</div>
			        		</div>
			      		</div>
			      		<div class="modal-footer">
			      			<button type="button" id="crop" class="btn btn-primary">Crop</button>
			        		<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			      		</div>
			    	</div>
			  	</div>

			</div>	
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script src="assets/js/datepicker.min.js"></script>
        <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
        <script type="text/javascript" src="assets/js/jquery.popupoverlay.js"></script>
        <script type="text/javascript" src="assets/js/common.js"></script>
        <script src="assets/js/dropzone.js"></script>
		<script src="assets/js/cropper.js"></script>
        <script type="text/javascript">
            <?php $added_gtin = explode(",",$selected_gtins[0]['variants_array']); ?>            
            let added_gtin = new Array();
            <?php foreach($added_gtin as $key => $val){ ?>
                added_gtin.push('<?php echo $val; ?>');
            <?php } ?>

            var item_length = $('.slick-slider-wrapper > div').length - 1;
            $('.slick-slider-wrapper').slick({
                dots: false,
                infinite: false,
                speed: 300,
                arrows: false,
                slidesToShow: 1,
                swipe: false,                
            });

            $('.drawer-slick-wrapper').slick({
                dots: false,
                infinite: false,
                speed: 300,
                arrows: false,
                slidesToShow: 1,
                swipeToSlide: true
            });

            $('.slick-slider-wrapper').on('afterChange', function(event, slick, currentSlide, nextSlide){
                if(currentSlide==0){
                    $(".slick-prev").addClass('invisible');
                }else{
                    $(".slick-prev").removeClass('invisible');
                }
                if(currentSlide == item_length){
                    $('#report_submit').removeClass('hide');
                    $('.slick-next').addClass('hide');
                }else{
                    $('#report_submit').addClass('hide');
                    $('.slick-next').removeClass('hide');
                }
            })
            
            
        $(document).ready(function(){
            $('[data-toggle="datepicker"]').datepicker({'format':'yyyy/mm/dd'});
            $('[data-toggle="datepicker"]').datepicker('setDate', new Date());

            var $modal = $('#modal');

            var image = document.getElementById('sample_image');

            var cropper;

            $modal.popup({
                onopen:function(){
                    cropper = new Cropper(image, {
                        aspectRatio: 1,
                        viewMode: 3,
                        preview:'.preview'
                    });
                },
                onclose:function(){
                    cropper.destroy();
   		            cropper = null;
                }
            })

            $('#crop').click(function(){
                canvas = cropper.getCroppedCanvas({
                    width:800,
                    height:800
                });

                canvas.toBlob(function(blob){
                    url = URL.createObjectURL(blob);
                    var reader = new FileReader();
                    reader.readAsDataURL(blob);
                    reader.onloadend = function(){
                        var base64data = reader.result;
                        $.ajax({
                            url:'core.php',
                            method:'POST',
                            data:{image:base64data,action:'upload-report-image'},
                            success:function(data)
                            {
                                $modal.popup('hide');
                                $('.thumbnail').removeClass("hide");
                                $('.thumbnail img').attr('src', data);
                                $("#hidden_img_uploaded").val(data);
                            },
                            error: function(err){
                                alert('Sorry but croped image is too large to be uploaded, could you make it a bit smaller please? Thank you');
                            }
                        });
                    };
                });
            });

            $("#memo_video").on('change', function(){
                var file_data = $(this).prop('files')[0];   
                let fileElementorId = $(this).attr('id');
                var form_data = new FormData();                  
                form_data.append('file', file_data);
                form_data.append('action', 'upload-report-memo');
                                     
                $.ajax({
                    url: 'core.php', // point to server-side PHP script 
                    dataType: 'text',  // what to expect back from the PHP script, if anything
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: form_data,                         
                    type: 'post',
                    beforeSend:function(){
                        $(".img-loader").removeClass('hide');
                        $(".slick-next").prop('disabled',true);
                        $(".thumbnail").html('');
                    },
                    success: function(php_script_response){
                        $("#hidden_memo_uploaded").val(php_script_response);
                    }
                });
            })
            
            $("#report_image").on('change', function(){
                var files = event.target.files;

                var done = function(url){
                    image.src = url;
                    $modal.popup('show');
                };

                if(files && files.length > 0)
                {
                    reader = new FileReader();
                    reader.onload = function(event)
                    {
                        done(reader.result);
                    };
                    reader.readAsDataURL(files[0]);
                }
            })
        })

    
        $('#stars li').on('mouseover', function() {
            var onStar = parseInt($(this).data('value'), 10); // The star currently mouse on
            // Now highlight all the stars that's not after the current hovered star
            $(this).parent().children('li.star').each(function(e) {
                if(e < onStar) {
                    $(this).addClass('hover');
                } else {
                    $(this).removeClass('hover');
                }
            });
        }).on('mouseout', function() {
            $(this).parent().children('li.star').each(function(e) {
                $(this).removeClass('hover');
            });
        });
        $('#stars li').on('click', function() {
            var onStar = parseInt($(this).data('value'), 10); // The star currently selected
            var stars = $(this).parent().children('li.star');
            for(i = 0; i < stars.length; i++) {
                $(stars[i]).removeClass('selected');
            }
            for(i = 0; i < onStar; i++) {
                $(stars[i]).addClass('selected');
            }
            var ratingValue = parseInt($('#stars li.selected').last().data('value'), 10);
            $("#hidden_rating").val(ratingValue);
        });

        $('.btn-primary.slick-prev').on('click', function(){
            let currentStep = $('.slick-current').index();
            $('.slick-slider-wrapper').slick('slickPrev');
            // if(fishingReportValidation(currentStep)==true){
            //     $('.slick-slider-wrapper').slick('slickPrev');
            // }else{
            //     $('.slick-current .err-msg').html(fishingReportValidation(currentStep));
            // }
        })

        $('.btn-primary.slick-next').on('click', function(){
            let currentStep = $('.slick-current').index();
            if(fishingReportValidation(currentStep)==true){
                $('.slick-slider-wrapper').slick('slickNext');                 
            }else{
                $('.slick-current .err-msg').html(fishingReportValidation(currentStep));
            }         
        })

        $("#report_submit").on('click', function(){
            let currentStep = $('.slick-current').index();
            if(fishingReportValidation(currentStep)==true){                
                $('form').submit();
            }else{
                $('.slick-current .err-msg').html(fishingReportValidation(currentStep));
            }
        })
        
        function fishingReportValidation(index){
            if(index==0){
                if($("#hidden_img_uploaded").val()=="false"){
                    return "Sorry but failed to upload image, could you upload it again please?";
                }else if($("#hidden_img_uploaded").val()==""){
                    return "You need to upload at least one file";
                }else{
                    return true;
                }
            }else if(index==1){                
                if($("#trip_date").val()!=""){
                    if(isNaN(Date.parse($("#trip_date").val()))){
                        return "Please select valid date";
                    }else if((Date.parse($("#trip_date").val()) - Date.parse(new Date())) > 0){
                        return "Sorry but you can't select upcoming date for report";
                    }else{
                        return true;
                    }
                }else{
                    return "Please fill all required fields";
                }
            }else if(index==2){
                if($('input[name="weatherconditions"]:checked').val()==undefined){
                    return "Please fill all required fields";
                }
                return true;
            }else if(index==3){
                if($('input[name="seawaveheight"]:checked').val()==undefined){
                    return "Please fill all required fields";
                }
                return true;
            }else if(index==4){
                if($('input[name="fishingdepth"]:checked').val()==undefined){
                    return "Please fill all required fields";
                }
                return true;
            }else if(index==5){
                if($('input[name="species[]"]:checked').val()==undefined){
                    return "Please select at least one species";
                }
                return true;            
            }else if(index==6){
                if($('input[name="fishing_types[]"]:checked').val()==undefined){
                    return "Please select at least one fishing type";
                }
                return true;
            }else if(index==7){
                if($('input[name="fishing_technique[]"]:checked').val()==undefined){
                    return "Please select at least one fishing technique";
                }
                return true;
            }else if(index==8){
                if($('input[name="selected_variants[]"]:checked').val()==undefined){
                    return "Please select at least one variant";
                }
                return true;
            }else if(index==9){
                if($("#hidden_memo_uploaded").val()=="false"){
                    if($("#description").val()==""){
                        return "Sorry but failed to upload memo, could you upload it again please?";
                    }else{
                        return true;
                    }
                }else if($("#hidden_memo_uploaded").val()==""){
                    if($("#description").val()==""){
                        return "You need to upload memo file or fill the comment box";   
                    }else{
                        return true;
                    }                
                }else{
                    return true;
                }
            }else if(index==10){
                if($('input#hidden_rating').val()==""){
                    return "Please rate your fishing experience";
                }
                return true;
            }
        }
        
        $(document).on('click','.add_more_tackle', function(){
            $('.drawer-slick-wrapper').slick('slickGoTo',0);
            $('.drawer-bottom').addClass('bottom-drawer-open');
            $('.drawer-overlay').removeClass('hide');
        });

        $('.drawer-close').on('click', function(){
            $('.drawer-bottom').removeClass('bottom-drawer-open');
            $('.drawer-overlay').addClass('hide');
        })

        $(document).on('click','.drawer-bottom li.category', function(){                
            let subCatName = $(this).find('span').html();
            $('.brands-wrapper h2').html(subCatName);
            $('.brands-wrapper ul').html("");                
            $(this).siblings().removeClass('active');                
            $(this).addClass('active');
            $.ajax({
                url: "core.php",
                type: "POST",
                data: {action: "getBrandsFromCategory", subcat:subCatName},
                dataType: "json",
                success: function(result) {
                    if(result.length > 0){                            
                        result.forEach(function(d){
                            $('.brands-wrapper ul').append('<li class="vertical-item" brand-id="'+d.id + '"><label><span><img src="'+d.image_url+'"/>'+d.NAME+'</span><i class="fas fa-chevron-right"></i></label></li>');
                        });                            
                    }
                    $('.drawer-slick-wrapper').slick("slickNext");
                },
                error: function(err) {
                    console.log(err);
                }
            });
        })

        $(document).on('click', '.brands-wrapper li', function(){
            let brandId = $(this).attr('brand-id');
            let subcatText = $('.brands-wrapper h2').html();
            $('.products-wrapper ul').html("");
            $('.drawer-slick-wrapper').slick('slickNext')
            $.ajax({
                url: "core.php",
                type: "POST",
                data: {action: "getProductsFromBrandAndCat", subcat:subcatText,brand:brandId},
                dataType: "json",
                success: function(result) {
                    if(result.length > 0){                            
                        result.forEach(function(d){
                            $('.products-wrapper ul').append('<li class="vertical-item" product-id="'+d.id + '"><label>'+d.name+'<i class="fas fa-chevron-right"></i></label></li>');
                        });                            
                    }
                },
                error: function(err) {
                    console.log(err);
                }
            });
        })

        $(document).on('click', '.products-wrapper li', function(){
            let productId = $(this).attr('product-id');                
            $('.variants-wrapper ul').html("");
            $('.drawer-slick-wrapper').slick('slickNext');
            $.ajax({
                url: "core.php",
                type: "POST",
                data: {action: "getVariantsFromProduct", product_id:productId},
                dataType: "json",
                success: function(result) {
                    if(result.length > 0){                            
                        result.forEach(function(d){
                            let checked = added_gtin.includes(d.gtin)?"checked":"";
                            $('.variants-wrapper ul').append('<li class="single-variant vertical-item" gtin-id="'+d.gtin+'" variant-id="'+d.variant_id + '"><label for="input'+d.variant_id+'"><span><img src="'+d.variant_img+'" alt=""/>'+d.option1_value+'</span><input id="input'+d.variant_id+'" type="checkbox" '+checked+'></label></li>');
                        });                            
                    }
                },
                error: function(err) {
                    console.log(err);
                }
            });
        })

        $(document).on('change', '.single-variant input', function(){
            let cuGtin = $(this).closest('.single-variant').attr('gtin-id');                
            if(added_gtin.includes(cuGtin)){
                const index = added_gtin.indexOf(cuGtin);
                if (index > -1) {
                    added_gtin.splice(index, 1);
                }
            }else{
                added_gtin.push(cuGtin);
            }
            $("#added_gtin").val(added_gtin.join(","));
        })

        $('#update-tacklebox').on('click', function(){
            let added_gtin_str =$("#added_gtin").val();
            let email_id = '<?php echo $_SESSION['id'];?>';
            $.ajax({
                url: "core.php",
                type: "POST",
                data: {action: "update-tacklebox-ajax", added_gtin:added_gtin_str, email_id:email_id},
                dataType: "json",
                success: function(result) {
                    $('.your-tackle-box .values-wrapper ul').html("");
                    if(result.length>0){
                        let definedSubCats = ['Hooks','Line','Lures','Reels','Terminal Tackle'];
                        definedSubCats.sort();
                        let filteredResult = result.filter(function(d){return definedSubCats.includes(d.sub)});
                        
                        definedSubCats.forEach(function(subcat){
                            let filteredSubcat = filteredResult.filter(function(d){return d.sub==subcat});
                            if(filteredSubcat.length!=0){
                                let html ='<li class="category">'+subcat+'<ul class="sub-vertical">';
                                filteredSubcat.forEach(function(variant){
                                    html += '<li class="vertical-item"><label><div><img src="'+variant['variant_img']+'">'+variant['brandname']+'-'+variant['option1_value']+'</div><input type="checkbox" name="selected_variants[]" value="'+variant['gtin']+'"></label></li>';
                                })
                                html += '</ul></li>';
                                $('.your-tackle-box .values-wrapper ul.vertical').append(html);
                            }                                 
                        })                                                
                    }
                    $('.drawer-bottom').removeClass('bottom-drawer-open');
                    $('.drawer-overlay').addClass('hide');
                },
                error: function(err) {
                    console.log(err);
                }
            });
        })
        if ( window.history.replaceState ) {
            window.history.replaceState( null, null, window.location.href );
        }
        </script>
    </body>
</html>