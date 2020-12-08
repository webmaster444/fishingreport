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
$sql = "SELECT DISTINCT(sub) FROM core_category WHERE sub IN ('Baits', 'Lures', 'Reels','Rods', 'Terminal Tackle', 'Accessories')";
$subcat_result = $conn->query($sql);

while($row = $subcat_result->fetch_array()){
    $subcats[] = $row;
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
        <title>Create a tacklebox | Fish in My Best Life</title>
        <link rel="stylesheet" href="assets/css/all.min.css">
        <link rel="stylesheet" href="assets/css/styles.css">
        <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    </head>
    <body class="create-tacklebox-page" id="create-tacklebox-page">
        <div class="page-content">
            <div class="login-header text-center"><a href="index.php"><img src="assets/imgs/logo.png" alt="Fish in my best life" /></a></div>
            <h1 class="page-title">Create a tackle box</h1>
            <div class="back-home"><i class="fas fa-chevron-left"></i></div>
            <div class="back-wrapper hide"><i class="fas fa-chevron-left"></i></div>
            <div class="content">
                <div class="scroll-content">                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="slick-slider-wrapper">
                        <div class="categories-wrapper">
                        <div class="scroll-wrapper">                            
                            <h2 class="section-title">Category</h2>    
                            <p class="err-msg"></p>
                            <div class="search-input"><input type="text" class="autocomplete"  placeholder="Search" /></div>                        
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
                            <div class="scroll-wrapper">                            
                            <h2 class="section-title">Brands</h2>
                            <p class="err-msg"></p>
                            <div class="search-input"><input type="text" class="autocomplete"  placeholder="Search" /></div>
                            <ul class="vertical">

                            </ul>
                            </div>
                        </div>
                        <div class="products-wrapper">
                        <div class="scroll-wrapper">                            
                            <h2 class="section-title">Products</h2>
                            <p class="err-msg"></p>
                            <div class="search-input"><input type="text" class="autocomplete"  placeholder="Search" tabindex="0"></div>
                            <ul class="vertical">

                            </ul>
                            </div>
                        </div>
                        <div class="variants-wrapper">
                        <div class="scroll-wrapper">                            
                            <h2 class="section-title">Variants</h2>
                            <p class="err-msg"></p>
                            <div class="search-input"><input type="text" class="autocomplete" placeholder="Search"  /></div>
                            <ul class="vertical"></ul>
                            </div>
                        </div>                
                    </div>    
                    <input type="hidden" id="added_gtin" name="added_gtin" />
                </form>
                </div>
            </div>       
            <div class="page-footer">
                <button type="submit" class="btn-primary add_variants full">Add selected variants to tackle box</button>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
        <script type="text/javascript" src="assets/js/common.js"></script>
        <script type="text/javascript">
            $('.scroll-wrapper').css('max-height',($(window).height()-260));

            let added_gtin = [];            

            $('.slick-slider-wrapper').slick({
                dots: false,
                infinite: false,
                speed: 300,
                arrows: false,
                slidesToShow: 1,
            });

            $(document).on('click','li.category', function(){
                $('.err-msg').html('');
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
                        $('.slick-slider-wrapper').slick("slickNext");
                        $('.back-home').addClass('hide');
                        $('.back-wrapper').removeClass('hide');
                    },
                    error: function(err) {
                        console.log(err);
                    }
                });
            })

            $(document).on('click', '.brands-wrapper li', function(){
                $('.err-msg').html('');
                let brandId = $(this).attr('brand-id');
                let subcatText = $('.brands-wrapper h2').html();
                $('.products-wrapper ul').html("");
                $('.slick-slider-wrapper').slick('slickNext')
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
                $('.err-msg').html('');
                let productId = $(this).attr('product-id');                
                $('.variants-wrapper ul').html("");
                $('.slick-slider-wrapper').slick('slickNext');
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

            $('.add_variants').on('click', function(){
                if($('#added_gtin').val()!=""){
                    $('form').submit();
                }else{
                    $('.err-msg').html('You need to select at least one variant');
                }                
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
            
            $(document).on('click','.back-wrapper', function(){
                $('.slick-slider-wrapper').slick('slickPrev');
                let cuIndex = $('.slick-current').index();
                if(cuIndex==0){                    
                    $('.back-wrapper').addClass('hide');
                    $('.back-home').removeClass('hide');
                }else{
                    $('.back-home').addClass('hide');
                }
            })
        </script>
    </body>
</html>