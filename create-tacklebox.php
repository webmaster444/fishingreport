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
        <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    </head>
    <body class="create-tacklebox-page">
        <div class="page-content">
            <div class="login-header text-center"><a href="index.php"><img src="assets/imgs/logo.png" alt="Fish in my best life" /></a></div>
            <h1 class="page-title">Create a tackle box</h1>
            <div class="content">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="categories-wrapper">
                        <ul class="slider variable-width">                
                        <?php 
                            foreach($subcats as $subcat){
                                echo '<li class="category">'.$subcat[0].'</li>';
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
                        <div class="products-wrapper">
                            <h2 class="section-title">Products</h2>
                            <ul class="vertical">

                            </ul>
                        </div>
                        <div class="variants-wrapper">
                            <h2 class="section-title">Variants</h2>
                            <ul class="vertical"></ul>
                            <!-- <button type="submit" class="btn-primary add_variants">Add selected variants to tackle box</button> -->
                        </div>                
                    </div>    
                    <input type="hidden" id="added_gtin" name="added_gtin" />
                </form>
            </div>       
            <div class="page-footer">
                <button type="submit" class="btn-primary add_variants full">Add selected variants to tackle box</button>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
        <script type="text/javascript" src="assets/js/common.js"></script>
        <script type="text/javascript">
            let added_gtin = [];
            $('.variable-width').slick({
                dots: false,
                infinite: true,
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
                swipeToSlide: true
            });

            $(document).on('click','li.category', function(){                
                let subCatName = $(this).html();
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
                                $('.brands-wrapper ul').append('<li brand-id="'+d.id + '">'+d.NAME+'</li>');
                            });                            
                        }
                        $('.slick-slider-wrapper').slick("slickGoTo", 0);
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
                $('.slick-slider-wrapper').slick('slickNext')
                $.ajax({
                    url: "core.php",
                    type: "POST",
                    data: {action: "getProductsFromBrandAndCat", subcat:subcatText,brand:brandId},
                    dataType: "json",
                    success: function(result) {
                        if(result.length > 0){                            
                            result.forEach(function(d){
                                $('.products-wrapper ul').append('<li product-id="'+d.id + '">'+d.name+'</li>');
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
                                $('.variants-wrapper ul').append('<li class="single-variant vertical-item" gtin-id="'+d.gtin+'" variant-id="'+d.variant_id + '"><label><a href="'+d.url+'"><img src="'+d.variant_img+'" alt=""/>'+d.option1_value+'</a><input type="checkbox" '+checked+'></label></li>');
                            });                            
                        }
                    },
                    error: function(err) {
                        console.log(err);
                    }
                });
            })

            $('.add_variants').on('click', function(){
                // $('.variants-wrapper input[type="checkbox"]').each(function(){
                //     if($(this).prop('checked')){
                //         console.log($(this).closest('li').attr('variant-id'));
                //     }
                // })
                $('form').submit();
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