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

// $sql = "SELECT DISTINCT(sub) FROM core_category order by sub";
$sql = "SELECT DISTINCT(sub) FROM core_category WHERE sub IN ('Hooks', 'Line', 'Reels', 'Lures', 'Rods','Terminal Tackle') ORDER BY sub";
$subcat_result = $conn->query($sql);
$subcats = [];
while($row = $subcat_result->fetch_array()){
    $subcats[] = $row;
}

$sql = "SELECT cb.name AS brandname, cg.option1_value, cg.variant_img, cg.gtin, cc.sub FROM core_gtin AS cg JOIN core_fmblvariant AS cv ON cv.gtin = cg.gtin JOIN core_product AS cp ON cp.id = cv.product_id JOIN core_brand AS cb ON cp.brand_id = cb.id JOIN core_category AS cc ON cp.category_id = cc.id AND FIND_IN_SET(cg.gtin, (SELECT variants_array FROM MemberTackleBox WHERE member_email_id = '".$_SESSION['id']."' LIMIT 1));";
$tacklebox_result = $conn->query($sql);

$variants_in_tacklebox = [];
if($tacklebox_result->num_rows!=0){
    while($row = $tacklebox_result->fetch_array()){
        $variants_in_tacklebox[] = $row;
    }    
}

$sql = "SELECT variants_array from MemberTackleBox where member_email_id='".$_SESSION['id']."' LIMIT 1";
$gtinresult = $conn->query($sql);

$selected_gtins = [];
while($row = $gtinresult->fetch_array()){
    $selected_gtins[] = $row;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){   
    $sql = "SELECT * FROM MemberTackleBox WHERE member_email_id ='".$_SESSION['id']."'";

    $tacklebox_result = $conn->query($sql);    
    if($tacklebox_result->num_rows==0){
        $sql = "INSERT INTO MemberTackleBox (member_email_id,variants_array) VALUES (?,?)";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "is", $_SESSION['id'],$_POST['added_gtin']);
            
            if(mysqli_stmt_execute($stmt)){                 
                header("location: tacklebox.php");
            } else{
                echo $stmt->error;
                echo "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }else{
        $sql = "UPDATE MemberTackleBox SET variants_array=? WHERE member_email_id = ".$_SESSION['id'];
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s",$_POST['added_gtin']);
            
            if(mysqli_stmt_execute($stmt)){                 
                header("location: tacklebox.php");
            } else{
                echo "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
};

?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"> 
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"> 
        <title>Fish in My Best Life</title>
        <link rel="stylesheet" href="assets/css/styles.css">
        <link rel="stylesheet" href="assets/css/all.min.css">
        <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    </head>
    <body class="tacklebox-page">
        <div class="page-content">
            <div class="login-header text-center"><a href="index.php"><img src="assets/imgs/logo.png" alt="Fish in my best life" /></a></div>        
            <h1 class="text-center">Tackle box</h1>
            <div class="content">
                <div class="scroll-content">
                <div class="categories-wrapper">
                    <ul class="slider variable-width horizontal-slider">
                    <li class="category active">All</li>
                    <?php 
                        foreach($subcats as $subcat){
                            echo '<li class="category">'.$subcat[0].'</li>';
                        }
                    ?>
                    </ul>
                </div>
                <div class="slick-slider-wrapper">
                <div class="categories-wrapper"><div class="scroll-wrapper">
                    <ul class="vertical">                
                    <?php 
                        foreach($subcats as $subcat){                        
                            $filtered_vars = array_filter($variants_in_tacklebox, function($var) use ($subcat){
                                return $var['sub']==$subcat[0];
                            });
                            echo '<li class="">'.$subcat[0];
                            echo '<ul class="sub-vertical">';
                            foreach($filtered_vars as $variant){
                                echo '<li class="vertical-item"><div><img src="'.$variant['variant_img'].'">'.$variant['brandname'].'-'.$variant['option1_value'].'</div></li>';
                            }
                            echo '</ul>';
                            echo '</li>';
                        }
                    ?>
                    </ul>
                </div></div>
                <?php foreach($subcats as $subcat){ 
                    echo '<div class="subcat-wrapper"><div class="scroll-wrapper">';
                    $filtered_vars = array_filter($variants_in_tacklebox, function($var) use ($subcat){
                        return $var['sub']==$subcat[0];
                    });
                    if(sizeof($filtered_vars)==0){
                        echo '<a href="#" class="add_more_tackle btn-primary"><i class="fas fa-plus-circle"></i>Add more tackle</a>';
                    }else{
                        echo '<ul class="vertical">';
                        foreach($filtered_vars as $variant){
                            echo '<li class="vertical-item"><div><img src="'.$variant['variant_img'].'">'.$variant['brandname'].'-'.$variant['option1_value'].'</div></li>';
                        }
                        echo '</ul>';
                    }
                    echo '</div></div>';
                }
                ?>
                </div>   
                </div>
            </div>
            <div class="page-footer">
            <a href="index.php" class="pull-left btn-primary">Back to home</a>
            <a href="#" class="add_more_tackle pull-right btn-primary"><i class="fas fa-plus-circle"></i>Add more tackle</a>
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
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                        <input type="hidden" id="added_gtin" name="added_gtin" value="<?php echo $selected_gtins[0]['variants_array'];?>"/>
                        <button type="submit" class="btn-primary">Update tacklebox</button>
                    </form>
                </div>
            </div>
            <div class="drawer-overlay hide"></div>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
        <script type="text/javascript" src="assets/js/common.js"></script>
        <script type="text/javascript">
            <?php $added_gtin = explode(",",$selected_gtins[0]['variants_array']); ?>            
            let added_gtin = new Array();
            <?php foreach($added_gtin as $key => $val){ ?>
                added_gtin.push('<?php echo $val; ?>');
            <?php } ?>
            
            $('.variable-width').slick({
                dots: false,
                infinite: false,
                speed: 300,
                arrows: false,
                slidesToShow: 2,
                swipeToSlide: true,
                centerMode: false,
                centerPadding: 0,
                variableWidth: true
            });

            $('.slick-slider-wrapper').slick({
                dots: false,
                infinite: false,
                speed: 300,
                arrows: false,
                slidesToShow: 1,
                swipeToSlide: true,
                swipe: false
            });
            $('.drawer-slick-wrapper').slick({
                dots: false,
                infinite: false,
                speed: 300,
                arrows: false,
                slidesToShow: 1,
                swipeToSlide: true
            });

            $('li.category').on('click', function(){
                $(this).siblings().removeClass('active');
                $(this).addClass('active');                
                $('.slick-slider-wrapper').slick('slickGoTo',$(this).index());
            })

            $('.drawer-close').on('click', function(){
                $('.drawer-bottom').removeClass('bottom-drawer-open');
                $('.drawer-slick-wrapper').slick('slickGoTo',0);
                $('.drawer-overlay').addClass('hide');
            })

            $(document).on('click','.add_more_tackle', function(e){
                if($(this).parent().parent().hasClass('subcat-wrapper')){
                    
                    let pageIndex = $(this).parent().parent().index();
                    console.log(pageIndex);
                    $('.drawer-slide > .drawer-scroll-wrapper > ul > li:nth-child('+pageIndex+')').trigger('click');
                    // $('.drawer-slick-wrapper').slick('slickGoTo',0);
                }else{
                    $('.drawer-slick-wrapper').slick('slickGoTo',0);
                }                
                $('.drawer-bottom').addClass('bottom-drawer-open');
                $('.drawer-overlay').removeClass('hide');
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
                        }else{

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
                        }else{
                            $('.variants-wrapper').append('No variant for this project');
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

        </script>
    </body>
</html>