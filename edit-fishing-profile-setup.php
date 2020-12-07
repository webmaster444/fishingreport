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
$loggedin_users = [];
while($row = $loggedin_user->fetch_array()){
    $loggedin_users[] = $row;
}
$loggedin_user_citys = array();
$sql = "SELECT city_id, city FROM advisor_city WHERE city IN (SELECT city FROM Member WHERE member_email_id = ".$_SESSION['id'].')';
$loggedin_user_city = $conn->query($sql);

while($row = $loggedin_user_city->fetch_array()){
    $loggedin_user_citys[] = $row;
}

$user_fishing_detail = array();
$sql = "SELECT species,fishing_types,fishing_technique FROM member_detail_fishing WHERE email_id = ".$_SESSION['id'];
$user_detail_result = $conn->query($sql);

while($row = $user_detail_result->fetch_array()){
    $user_fishing_detail[] = $row;
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
        <title>Edit fishing profile | Fish in My Best Life</title>
        <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
        <link rel="stylesheet" href="assets/css/all.min.css">
        <link rel="stylesheet" href="assets/css/styles.css">
        <meta name="google-signin-client_id" content="753213052944-4molte8riclfmm373egkuldknat2buh6.apps.googleusercontent.com">
        <script src="https://apis.google.com/js/platform.js" async defer></script>
    </head>
    <body class="fishing-profile-setup" id="edit-fishing-profile-setup-page">
        <div class="page-content">
        <div class="login-header text-center"><a href="index.php"><img src="assets/imgs/logo.png" alt="Fish in my best life" /></a></div>        
        <h1 class="page-title">Edit your fishing profile</h1>
        <div class="back-home"><i class="fas fa-chevron-left"></i></div>
        <div class="back-wrapper hide"><i class="fas fa-chevron-left"></i></div>
        <p class="err-msg"></p>
        <div class="content">
            <div class="scroll-content">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="fishing-profile-form">
                <div class="city-wrapper">
                    <input type="hidden" name="loggedin_user_city" id="city" value='<?php echo $loggedin_user_citys[0]['city_id']; ?>'/>
                </div>
                <div class="slick-slider-wrapper">
                    <div class="species-wrapper"><div class="scroll-wrapper"></div></div>
                    <div class="fishing-types-wrapper"><div class="scroll-wrapper"></div></div>
                    <div class="technique-wrapper"><div class="scroll-wrapper"></div></div>
                </div>
            </form>
            </div>
        </div> 
        <div class="hide hidden-fields-wrapper">
            <input type="hidden" id="selected_species" value='<?php echo $user_fishing_detail[0]['species'];?>' />
            <input type="hidden" id="selected_types" value='<?php echo $user_fishing_detail[0]['fishing_types'];?>' />
            <input type="hidden" id="selected_techniques" value='<?php echo $user_fishing_detail[0]['fishing_technique'];?>' />            
        </div>
        <div class="page-footer">            
            <button type="button" id="getFishingTypesButton" class="btn-primary pull-right hide">Get Fishing Types</button><div class="clearfix"></div>
            <button type="button" class="btn-primary hide" id="getTechniqueButton">Get Fishing Techinique</button><div class="clearfix"></div>
            <button type="submit" class="btn-primary hide" id="createTacklebox">Update Tackle Box</button><div class="clearfix"></div>
        </div>
        </div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script src="assets/js/common.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function(){
        $('.scroll-wrapper').css('max-height',($(window).height()-260));
        <?php $selected_species = explode(",",$user_fishing_detail[0]['species']); ?>            
        let selected_species = new Array();
        <?php foreach($selected_species as $key => $val){ ?>
            selected_species.push('<?php echo $val; ?>');
        <?php } ?>

        <?php $selected_types = explode(",",$user_fishing_detail[0]['fishing_types']); ?>            
        let selected_types = new Array();
        <?php foreach($selected_types as $key => $val){ ?>
            selected_types.push('<?php echo $val; ?>');
        <?php } ?>

        <?php $selected_technique = explode(",",$user_fishing_detail[0]['fishing_technique']); ?>            
        let selected_technique = new Array();
        <?php foreach($selected_technique as $key => $val){ ?>
            selected_technique.push('<?php echo $val; ?>');
        <?php } ?>

        $('.slick-slider-wrapper').slick({
            dots: false,
            infinite: false,
            speed: 300,
            arrows: false,
            slidesToShow: 1,
            swipe: false,                
        });
        
        $('.species-wrapper .scroll-wrapper').html('');      
        $.ajax({
            url: "core.php",
            type: "POST",
            data: {action: "getSpecies", city_id:$('#city').val()},
            dataType: "json",
            success: function(result) {
                if(result.length > 0){                        
                    let selectorHTML='';
                    selectorHTML+='<div class="search-input"><input type="text" class="autocomplete" placeholder="Search" /></div>';
                    selectorHTML+='<ul class="vertical full">';
                    result.forEach(function(d){
                        selectorHTML+='<li class="vertical-item"><label><div>';
                        
                        if(d.image_url){
                            selectorHTML+='<img src="'+d.image_url+'" alt="'+d.attribute_name+'"/>'+d.attribute_name;
                        }
                        let checked = selected_species.includes(d.attribute_id)?"checked":"";
                        selectorHTML += '</div><input type="checkbox" name="species[]" value="'+d.attribute_id+'" '+checked+'/></label>';
                        selectorHTML += '</li>';
                    });                                  
                    selectorHTML += '</ul>';
                    $('.species-wrapper .scroll-wrapper').append(selectorHTML);
                    $("#getFishingTypesButton").removeClass('hide');
                }
            },
            error: function(err) {
                console.log(err);
            }
        });

        $(document).on('click', '#getFishingTypesButton', function(){
            var species = new Array();
            $("input[name='species[]']:checked").each(function() {
                species.push($(this).val());
            });

            if(species.length!=0){
                $('.fishing-types-wrapper .scroll-wrapper').html('');
                $('.slick-slider-wrapper').slick('slickNext');
                $(this).addClass('hide');
                $('.back-wrapper').removeClass('hide');
                $('.back-home').addClass('hide');
                $('.err-msg').html("");
                $.ajax({
                    url: "core.php",
                    type: "POST",
                    data: {action: "getFishingTypes", city_id:$('#city').val(),species:species},
                    dataType: "json",
                    success: function(result) {
                        if(result.length > 0){                        
                            let selectorHTML='<ul class="vertical">';
                            result.forEach(function(d){
                                let checked = selected_types.includes(d.attribute_id)?"checked":"";
                                selectorHTML+='<li class="vertical-item"><label><div><img src="'+d.image_url+'" alt="'+d.attribute_name+'"/>'+d.attribute_name+'</div><input type="checkbox" name="fishingTypes[]" '+checked+' value="'+d.attribute_id + '"></label></li>';
                            });          
                            selectorHTML +='</ul>';
                            $("#getTechniqueButton").removeClass('hide');
                            $('.fishing-types-wrapper .scroll-wrapper').append(selectorHTML);
                        }
                    },
                    error: function(err) {
                        console.log(err);
                    }
                });
            }else{
                $('.err-msg').html('Please select at least one species');
            }            
        });
        $(document).on('click', '#getTechniqueButton', function(){
            let fishingTypes = new Array();
            $("input[name='fishingTypes[]']:checked").each(function() {
                fishingTypes.push($(this).val());
            });
            
            if(fishingTypes.length!=0){
                $('.technique-wrapper .scroll-wrapper').html('');
                $('.slick-slider-wrapper').slick('slickNext');
                $(this).addClass('hide');
                var species = new Array();
                $("input[name='species[]']:checked").each(function() {
                    species.push($(this).val());
                });
                $('.err-msg').html("");
                $.ajax({
                    url: "core.php",
                    type: "POST",
                    data: {action: "getTechnique", city_id:$('#city').val(),species:species,fishing_types:fishingTypes},
                    dataType: "json",
                    success: function(result) {
                        if(result.length > 0){                        
                            let selectorHTML='<ul class="vertical">';
                            result.forEach(function(d){
                                let checked = selected_technique.includes(d.attribute_id)?"checked":"";
                                selectorHTML+='<li class="vertical-item"><label><div><img src="'+d.image_url+'" alt="'+d.attribute_name+'"/>'+d.attribute_name+'</div><input type="checkbox" name="technique[]" '+checked+' value="'+d.attribute_id + '"></label></li>';
                            });          
                            selectorHTML +='</ul>';
                            $('.technique-wrapper .scroll-wrapper').append(selectorHTML);
                            $("#createTacklebox").removeClass('hide');
                        }
                    },
                    error: function(err) {
                        console.log(err);
                    }
                });
            }else{
                $('.err-msg').html('Please select at least one fishing type');
            }            
        });

        $(document).on('click','#createTacklebox',function(){
            var techniques = new Array();
            $("input[name='technique[]']:checked").each(function() {
                techniques.push($(this).val());
            });
            if(techniques.length!=0){
                $("#fishing-profile-form").submit();
            }else{
                $('.err-msg').html("Please select at least one technique");
            }            
        });

        $(document).on('click','.back-wrapper', function(){
            $('.slick-slider-wrapper').slick('slickPrev');
            let cuIndex = $('.slick-current').index();
            if(cuIndex==0){
                $("#getTechniqueButton").addClass('hide');
                $('.back-wrapper').addClass('hide');
                $('.back-home').removeClass('hide');
                $("#getFishingTypesButton").removeClass("hide");
            }else if(cuIndex==1){
                $("#createTacklebox").addClass('hide');
                $("#getTechniqueButton").removeClass('hide');
            }
        })        
    });    
</script>
</body>
</html>