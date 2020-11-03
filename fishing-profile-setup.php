<?php
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "db.php";

global $conn;

$sql = "SELECT * FROM Member WHERE member_email_id = ".$_SESSION['id'];

$loggedin_user = $conn->query($sql);

while($row = $loggedin_user->fetch_array()){
    $loggedin_users[] = $row;
}
$loggedin_user_citys = array();
$sql = "SELECT city_id, city FROM advisor_city WHERE city IN (SELECT city FROM Member WHERE member_email_id = ".$_SESSION['id'].')';
$loggedin_user_city = $conn->query($sql);

while($row = $loggedin_user_city->fetch_array()){
    $loggedin_user_citys[] = $row;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){        
    // implode(",", $array);
    $sql = "SELECT * FROM member_detail_fishing WHERE email_id ='".$_SESSION['id']."'";

    $fishing_profile_detail = $conn->query($sql);
    if($fishing_profile_detail->num_rows==0){
        $sql = "INSERT INTO member_detail_fishing (email_id,city_id,species,fishing_types,fishing_technique) VALUES (?,?,?,?,?)";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "iisss", $_SESSION['id'],$_POST['loggedin_user_city'],implode(",",$_POST['species']), implode(",",$_POST['fishingTypes']), implode(',',$_POST['technique']));
            
            if(mysqli_stmt_execute($stmt)){                 
                header("location: create-tacklebox.php");
            } else{
                echo "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }else{
        $sql = "UPDATE member_detail_fishing SET city_id=?,species=?,fishing_types=?,fishing_technique=? WHERE email_id = ".$_SESSION['id'];
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "isss",$_POST['loggedin_user_city'],implode(",",$_POST['species']), implode(",",$_POST['fishingTypes']), implode(',',$_POST['technique']));
            
            if(mysqli_stmt_execute($stmt)){                 
                header("location: create-tacklebox.php");
            } else{
                echo "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
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
        <link rel="stylesheet" href="assets/css/styles.css">
        <meta name="google-signin-client_id" content="753213052944-4molte8riclfmm373egkuldknat2buh6.apps.googleusercontent.com">
        <script src="https://apis.google.com/js/platform.js" async defer></script>
    </head>
    <body class="index-page">
        <div class="page-content">
        <div class="login-header text-center"><img src="assets/imgs/logo.png" alt="Fish in my best life" /></div>        
        <h1 class="page-title">Setup your fishing profile</h1>
        <div class="content">
            <div class="scroll-content">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="fishing-profile-form">
                <div class="city-wrapper">
                    <input type="hidden" name="loggedin_user_city" id="city" value='<?php echo $loggedin_user_citys[0]['city_id']; ?>'/>
                    <?php echo "You are located in ".$loggedin_user_citys[0]['city'];?>
                </div>
                <div class="species-wrapper"></div>
                <div class="fishing-types-wrapper"></div>
                <div class="technique-wrapper"></div>
                <div class="tacklbox-wrapper"></div>                
            </form>
            </div>
        </div> 
        <div class="page-footer">
        </div>
        </div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script src="assets/js/common.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function(){
        // $.ajax({
        //     url: "core.php",
        //     type: "POST",
        //     data: {action: "getAllCities"},
        //     dataType: "json",
        //     success: function(result) {
        //         if(result.length > 0){
        //             let selectorHTML = '<select id="citySelect" name="city">';
        //             result.forEach(function(d){
        //                 selectorHTML+='<option value="'+d.city_id + '">'+d.city+'</option>';
        //             });
        //             selectorHTML+='</select>';
        //             $('.city-wrapper').append(selectorHTML);
        //         }
        //     },
        //     error: function(err) {
        //         console.log(err);
        //     }
        // });

        // $(document).on('change','#citySelect', function(){
            $('.species-wrapper').html('');      
            $.ajax({
                url: "core.php",
                type: "POST",
                data: {action: "getSpecies", city_id:$('#city').val()},
                dataType: "json",
                success: function(result) {
                    if(result.length > 0){                        
                        let selectorHTML='';
                        selectorHTML+='<div class="search-input"><input type="text" class="autocomplete"/></div>';
                        selectorHTML+='<ul class="vertical full">';
                        result.forEach(function(d){
                            selectorHTML+='<li class="vertical-item"><label><div>';
                            
                            if(d.image_url){
                                selectorHTML+='<img src="'+d.image_url+'" alt="'+d.attribute_name+'"/>'+d.attribute_name;
                            }
                            selectorHTML += '</div><input type="checkbox" name="species[]" value="'+d.attribute_id+'" /></label>';
                            selectorHTML += '</li>';
                        });                                  
                        $('.species-wrapper').append(selectorHTML);
                        $('.page-footer').append('<button type="button" id="getFishingTypesButton" class="btn-primary pull-right">Get Fishing Types</button><div class="clearfix"></div>');
                    }
                },
                error: function(err) {
                    console.log(err);
                }
        });
        // })

        $(document).on('click', '#getFishingTypesButton', function(){
            $('.fishing-types-wrapper').html('');
            $('.species-wrapper').addClass('hide'); 
            $(this).addClass('hide');
            var species = new Array();
            $("input[name='species[]']:checked").each(function() {
                species.push($(this).val());
            });
            $.ajax({
                url: "core.php",
                type: "POST",
                data: {action: "getFishingTypes", city_id:$('#city').val(),species:species},
                dataType: "json",
                success: function(result) {
                    if(result.length > 0){                        
                        let selectorHTML='<ul class="vertical">';
                        result.forEach(function(d){
                            selectorHTML+='<li class="vertical-item"><label><div><img src="'+d.image_url+'" alt="'+d.attribute_name+'"/>'+d.attribute_name+'</div><input type="checkbox" name="fishingTypes[]" value="'+d.attribute_id + '"></label></li>';
                        });          
                        selectorHTML +='</ul>';
                        $('.page-footer').append('<button type="button" class="btn-primary" id="getTechniqueButton">Get Fishing Techinique</button><div class="clearfix"></div>');
                        $('.fishing-types-wrapper').append(selectorHTML);
                    }
                },
                error: function(err) {
                    console.log(err);
                }
            });
        });
        $(document).on('click', '#getTechniqueButton', function(){
            $('.technique-wrapper').html('');
            $('.fishing-types-wrapper').addClass('hide');
            $(this).addClass('hide');
            var species = new Array();
            $("input[name='species[]']:checked").each(function() {
                species.push($(this).val());
            });

            let fishingTypes = new Array();
            $("input[name='fishingTypes[]']:checked").each(function() {
                fishingTypes.push($(this).val());
            });

            $.ajax({
                url: "core.php",
                type: "POST",
                data: {action: "getTechnique", city_id:$('#city').val(),species:species,fishing_types:fishingTypes},
                dataType: "json",
                success: function(result) {
                    if(result.length > 0){                        
                        let selectorHTML='<ul class="vertical">';
                        result.forEach(function(d){
                            selectorHTML+='<li class="vertical-item"><label><div><img src="'+d.image_url+'" alt="'+d.attribute_name+'"/>'+d.attribute_name+'</div><input type="checkbox" name="technique[]" value="'+d.attribute_id + '"></label></li>';
                        });          
                        selectorHTML +='</ul>';
                        $('.technique-wrapper').append(selectorHTML);
                        $('.page-footer').append('<button type="submit" class="btn-primary" id="createTacklebox">Save</button><div class="clearfix"></div>');                        
                    }
                },
                error: function(err) {
                    console.log(err);
                }
            });
        });

        $(document).on('click','#createTacklebox',function(){
            $("#fishing-profile-form").submit();
            // window.location.href = "http://localhost/shopify-fishinmybestlife.com/php-app/create-tacklebox.php";
        });
    });    
</script>
</body>
</html>