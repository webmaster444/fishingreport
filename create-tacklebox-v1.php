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
        <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
        <style>
html, body{
    margin: 0;
    width: 100%;
    height: 100%;
}
*, *:before, *:after{
    box-sizing: border-box;
}

body{
    background: #909090;
    position: relative;
}

.login-content{
    /* position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white; */
}
a{
    text-decoration: none;
    color: #1a81c6;
}
a:hover, a:focus, a:active{
    color: #1a81c6;
    text-decoration: none;  
    outline: none;
}
button:active, button:hover, button:focus{
    outline: none;
}
.login-header{
    background-color: #0259FE;
    padding: 15px 50px;
    position: sticky;
    top: 0;
    z-index: 100;
}

.flex-wrapper{
    display: flex;
}

.wrap-items{
    flex-wrap: wrap;
}
.half{
    width: 50%;
}

.full{
    width: 100%;
}
.ct-form-control input{
    width: 100%;
    padding: 10px 15px;
}

.ct-form-control{
    margin: 5px 0;
}

.btn-primary{
    background: #0259FE;
    color: white;
    border: none;
    padding: 15px 20px;    
    cursor: pointer;
    line-height: 20px;
    display: inline-block;
}

.login-form-container{
    /* padding: 20px; */
}
.half{
    padding: 15px;
}

.half:first-child{
    padding-left: 0;
}
.half:last-child{
    padding-right: 0;
}

.text-center{
    text-align: center;
}

.inline-field{
    width: auto;
}

input[type=checkbox], input[type="radio"]{
    width: auto;
}

select{
    padding: 10px 15px;
    width: 100%;
}

textarea{
    width: 100%;
    min-height: 80px;
}

p.err-msg{
    color: red;
}

label.swatch {
    position: relative;
    padding: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-basis: 150px;
    background: white;
    margin: 10px;
    text-align: center;
}

.swatch img {
	margin-bottom: 10px;
	height: 100px;
}

.swatch input[type="radio"],
.swatch input[type="checkbox"] {
	display: none;
}

input:checked ~ .check {
	display: inline-block;
	transform: rotate(45deg);
	height: 24px;
	width: 12px;
	border-bottom: 7px solid red;
	border-right: 7px solid red;
	position: absolute;
	right: 3px;
}

.species-wrapper{
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
}

.pull-right{
    float: right;
}

.clearfix{
    float: none;
    clear: both;
}

.hide{
    display: none !important;
}

.container{
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    background: white;
    margin-top: 50px;
}

ul{
    padding-left: 0;
    margin: 0;
}

ul li{
    list-style-type: none;
    float: left;
}

ul.slider li{
    cursor: pointer;
    padding: 10px 20px;
    border-left: 1px solid #eee;
}

ul.slider{
    display: flex;
    flex-wrap: wrap;
    background: white;
}

ul li:focus, ul li:active{
    outline: none;
}

.horizontal-slider li.active{
    color:#1a81c6;
}
.tacklebox-page .slick-slide{
    /* max-height: 520px; */
}
.slick-slider-wrapper{
    max-height: 100%;
    height: 100%;
}
.categories-wrapper{
    /* max-width: 600px;
    margin: 0 auto; */
}
.subcat-wrapper{
    position: relative;
}
.subcat-wrapper .add_more_tackle{
    position: absolute;
    left: 50%;
    top: 40%;
    transform: translate(-50%,-50%);
}
li.category.active{
    /* color:#1a81c6; */
}
.add_more_tackle i{
    padding-right: 10px;
}
ul.vertical li{
    float: none;
    border-bottom: 1px solid #aaa;
    padding: 10px 0;
}

ul.vertical li:last-child{
    border: none;
}
.vertical-item img{
    max-width: 90px;
    padding: 0 10px;
    margin-right: 15px;
    border: 1px solid black;
    border-radius: 5px;
}

li.vertical-item label{
    display: flex;
    align-items: center;
    justify-content: space-between;
}

li.vertical-item a,li.vertical-item span{
    display: flex;
    align-items: center;
    font-size: 20px;
    font-weight: bold;
}
.vertical-item i{
    padding-right: 10px;
    color: lightgray;
}
/* .create-tacklebox-page .container{
    min-height: 500px;
} */

.slick-slider:active, .slick-slider:focus, .slick-slide:active, .slick-slide:focus{
    outline: none;
}

a:active, a:focus{
    outline:none;
}

*:active, *:focus{
    outline: none;
}

.add_variant{
    font-size: 30px;
    border: 1px solid #1a81c6;
    border-radius: 30px;
    padding: 2px 7px;
    font-weight: bold;
    line-height: 30px;
}

.checkbox-container {
	display: inline-block;
	position: relative;
	padding-left: 25px;
	cursor: pointer;
	font-size: 22px;
	-webkit-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
	min-height: 20px;
}
  
  /* Hide the browser's default checkbox */
.checkbox-container input {
	position: absolute;
	opacity: 0;
	cursor: pointer;
	height: 0;
	width: 0;
}
  
  /* Create a custom checkbox */
.checkmark {
	position: absolute;
	top: 0;
	left: 0;
	height: 20px;
	width: 20px;
	background-color:transparent;
	border: 2px solid #707070;
}
  
  /* When the checkbox is checked, add a blue background */  
  
  /* Create the checkmark/indicator (hidden when not checked) */
.checkmark:after {
	content: "";
	position: absolute;
	display: none;
}
  
  /* Show the checkmark when checked */
.checkbox-container input:checked ~ .checkmark:after {
	display: block;
}
  

.checkbox-container .checkmark:after {
	left: 6px;
	top: 2px;
	width: 5px;
	height: 10px;
	border: solid #8dc73f;
	border-width: 0 3px 3px 0;
	-webkit-transform: rotate(45deg);
	-ms-transform: rotate(45deg);
	transform: rotate(45deg);
}

.add_variants{
    
}

.page-footer{
    position: fixed;
    bottom: 20px;
    width: 100%;
    max-width: 600px;
}
.btn-primary:hover, .btn-primary:focus{
    background: #0259FE;
    color: white;
    padding: 15px 20px;
}
.slick-track
{
    display: flex !important;
}
.page-content > p.err-msg{
    padding: 0 20px;
}
.slick-slide
{
    height: inherit !important;
    overflow: auto;
}

.drawer-scroll-wrapper{
    max-height: calc(100vh - 255px);
    overflow: auto;
}
.drawer-footer{
    position: absolute;
    bottom: 20px;    
}
.variants-wrapper{
    position: relative;
}

.page-content{
    max-width: 600px;
    margin: 0 auto;
    background: white;
    min-height: 100vh;
    max-height: 100vh;
    overflow-y: hidden;
    position: relative;
    height: 100%;
}

.content{
    padding: 140px 20px 80px;
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
}
.scroll-content{
    overflow-y: auto;    
}
.no-slider .scroll-content{
    max-height: calc(100vh - 155px);
}
.slick-slide img.img-loader{
    width: 30px;
    display: inline-block;
}

.thumbnail img{
    max-width: 100%;
    height: auto;
}
h1{
    font-size: 30px;
    line-height: 32px;
    text-align: center;
    padding-left: 20px;
    padding-right: 20px;
    margin: 10px 0;
}

.rating-stars ul {
	list-style-type: none;
	padding: 0;
	-moz-user-select: none;
	-webkit-user-select: none;
}

.rating-stars ul > li.star {
	display: inline-block;
}
.rating-stars ul > li.star > i.fa {
	font-size: 2.5em;
	/* Change the size of the stars */
	color: #ccc;
	/* Color on idle state */
}
.rating-stars ul > li.star.hover > i.fa {
	color: #FFCC36;
}
.rating-stars ul > li.star.selected > i.fa {
	color: #FF912C;
}

.space-between{
    justify-content: space-between;
}

.slider-buttons-wrapper{
    /* padding: 20px 0; */
}

.swatch label.checkbox-container {
    position: absolute;
    right: 0;
    top: 5px;
}

.tacklebox-page .sub-vertical{
    padding-left: 20px;
}

.vertical-item div{
    align-items: center;
    display: flex;
    font-size: 20px;
    font-weight: bold;
    overflow: hidden;
}

input.autocomplete{
    padding:10px 10px 10px 40px;    
    position: relative;
    width: 100%;
    font-size: 20px;
    border: 1px solid black;
    margin-bottom: 0;
}

.search-input:after{
    content: "";
    background: url(../imgs/Icon_search_black.png) no-repeat center center;
    background-size: 100%;
    width: 20px;
    height: 20px;
    display: block;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    left: 10px;
}

.search-input{
    position: relative;
    width: 100%;
}
#description{
    max-height: 500px;
    resize: none;
    font-size: 20px;
    -webkit-user-select: initial;
    user-select: initial;
}
.note-editable{
    -webkit-user-select: initial;
    user-select: initial;
}
input[type='checkbox'] ,input[type='radio'] {
    -webkit-appearance:none;
    width:30px;
    height:30px;
    background:white;  
    border:2px solid #555;
}
input[type='checkbox']{
    border-radius: 5px;
    vertical-align: middle;
}
input[type="radio"]{
    border-radius: 15px;
}
input[type='checkbox']:checked,input[type='radio']:checked {
    background: #abd;
}
input[type=file]:focus, input[type=checkbox]:focus, input[type=radio]:focus{
    outline: none;
}
::-webkit-scrollbar {
    width: 8px;
}
  
/* Track */
::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 5px;
}

/* Handle */
::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 5px;
}

/* Handle on hover */
::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.btn-secondary{
    background: white;
    color: #0259FE;
    border: none;
    padding: 15px 20px;
    cursor: pointer;
    display: inline-block;
    text-align: center;
}

.index-page .btn-primary, .index-page .btn-secondary{
    margin: 5px 0;
}
.invisible{
    visibility: hidden;
}

.notification{
    margin: 5px;    
    padding: 5px;
}
.notifications-wrapper{
    background:#5ef3002e;
}

.see_more{
    margin-top: 10px;
    display: block;
}

.drawer-bottom{
    position: fixed;
    z-index: 110;
    height: calc(100vh - 100px);
    bottom: -1000px;
    padding: 20px;
    background: white;
    overflow: auto;
    max-width: 600px;
    width: 100%;
    border-top-left-radius: 30px;
    border-top-right-radius: 30px;
    /* box-shadow: -1px -10px #e8dcdc42; */
}

.bottom-drawer-open{
    bottom:0;
    transition: all 0.3s;
}

.drawer-header{
    position: relative;
}

.drawer-close{
    position: absolute;
    right: 10px;
    top: 10px;
}

.drawer-overlay{
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    z-index: 102;
    background: #909090d1;
}

.back-wrapper,.back-home{
    position: absolute;
    top: 90px;
    left: 15px;
    cursor: pointer;
    z-index: 101;
}

/* crop modal */
#modal{
    max-width: 600px;
    background: white;
    padding: 20px;
}

#modal img{
    max-width: 100%;
}

.preview {
    display: none;
    overflow: hidden;
    width: 160px;
    height: 160px;
    margin: 10px 0;
    border: 1px solid red;
}
#sample_image{
    max-height: 350px;
}
.modal-buttons-wrapper{
    margin: 10px 0;
    display: flex;
}
#modal .img-container{
    padding: 20px;
}

.modal-close{
    position: absolute;
    color: black;
    right: 10px;
    top: 10px;
}

h5.modal-title{
    font-size: 25px;
    margin: 0;
}

.your-tackle-box .scroll-wrapper{
    position: relative;
}

.your-tackle-box .scroll-wrapper .add_more_tackle{
    position: absolute;
    right: 0;
    top: 0px;
    padding: 10px;
}

.facebook{
    color:#3B5998;
}

.google{
    
}

.vertical-item .trash i{
    color: black;
}
.social-login{
    width: 100%;
    box-shadow: none;
    cursor: pointer;    
    display: flex;
    align-items: center;
    padding: 0;
    margin: 5px 0;
}
.social-login i{
    width: 40px;
    background-color: #fff;
    /* padding: 12px 15px 11px 13px; */
    font-size: 25px;
    height: 40px;
}

.google i{
    background:url(../imgs/google.svg) 50% no-repeat;
    background-size: 55%;
    text-indent: -9999px;
    background-color: white;
}

.facebook div{
    background:#3B5998;
    color: white;
    width: 100%;
    line-height: 40px;
}
.google div{
    background: #4285F4;
    width: 100%;
    color: white;
    line-height: 40px;
}

.social-login.google{
    border: 1px solid #4285F4; 
    background-color: #4285F4;
}

.social-login.facebook{
    border: 1px solid #3B5998;     
    background-color: #3B5998;
}

.facebook i{
    font-size: 20px;
    padding-top: 10px;
}

button[disabled] {
    opacity: 0.5;
}
#modal .img-container .row .col-md-4{
    display: flex;
}
.no-slider .content{
    top: 140px;
}
.login-form-container.content{
    padding-bottom: 20px;
}
h2{
    font-size: 28px;
    line-height: 30px;
    margin-top: 10px;
}
.slick-slide {
    outline: none;
    padding: 0 3px;
}
p,span, body,a,button, label{
    font-size: 20px;
}
input[type="text"],input[type="email"]{
    font-size: 20px;
}
.fishing-report-page .your-tackle-box .add_more_tackle i{
    padding-right: 0;
}
body {
    /* min-height: 100vh; */
    /* mobile viewport bug fix */
    /* min-height: -webkit-fill-available; */
}  
html{
    /* height: -webkit-fill-available; */
}
.vertical-item input[type="checkbox"],.vertical-item input[type="radio"]{
    min-width: 30px;
}
/* Manage Users Page*/
#member-emails-table_length{
    display: none;
}

#member-emails-table_filter{
    float: left;
}
.dataTables_wrapper .dataTables_paginate{
    float: left;
}
.dataTables_wrapper .dataTables_paginate .paginate_button{
    padding: 10px;
}
#change-pwd-modal{
    background: white;
    padding: 20px;
}
@media screen and (max-width:767px){
    h1{
        font-size: 25px;
        line-height: 30px;
    }

    .page-footer{
        padding: 10px;
    }
    .no-slider .scroll-content form{
        padding-bottom: 50px;
    }


    h5.modal-title{
        font-size: 22px;        
    }
    #modal{
        width: 95%;       
    }
    .thumbnail img {
        max-height: calc(100vh - 300px);
    }

    .tacklebox-page .sub-vertical{
        padding-left: 10px;    
    }
    .vertical-item .trash i{
        padding-left: 10px;
    }
}

@media screen and (max-width: 767px) and (max-height: 815px){
    .scroll-wrapper{
        max-height: 600px;
    }
}

@media screen and (max-width: 767px) and (max-height: 740px){
    .scroll-wrapper{
        max-height: 525px;
    }
}
@media screen and (max-width: 767px) and (max-height: 670px){
    .scroll-wrapper{
        max-height: 455px;
    }
}
@media screen and (max-width: 400px){
    .vertical-item img{
        margin-left: 5px;
    }
}
        </style>
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