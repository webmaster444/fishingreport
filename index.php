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
$membertypes = $conn->query($sql);

while($row = $membertypes->fetch_array()){
    $rows[] = $row;
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
            <div class="login-header text-center"><a href="index.php"><img src="assets/imgs/logo.png" alt="Fish in my best life" /></a></div>
            <h1 class="page-title">Welcome back</h1>
            <div class="content half">
                <a class="btn-primary full" href="edit-profile.php">Edit profile</a>
                <a class="btn-primary full" href="create-tacklebox.php">Create a tacklebox</a>
                <a class="btn-primary full" href="create-fishing-report.php">Create a fishing report</a>
                <a class="btn-primary full" href="tacklebox.php">Your tacklebox</a>
                <a class="btn-secondary full" href="logout.php">Log out</a>
            </div>
        </div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script type="text/javascript">
    $(function(){
        $.ajax({
            url: "core.php",
            type: "POST",
            data: {action: "getAllCities"},
            dataType: "json",
            success: function(result) {
                if(result.length > 0){
                    let selectorHTML = '<select id="citySelect" name="city">';
                    result.forEach(function(d){
                        selectorHTML+='<option value="'+d.city_id + '">'+d.city+'</option>';
                    });
                    selectorHTML+='</select>';
                    $('.city-wrapper').append(selectorHTML);
                }
            },
            error: function(err) {
                console.log(err);
            }
        });

        $(document).on('change','#citySelect', function(){
            $('.species-wrapper').html('');      
            $.ajax({
                url: "core.php",
                type: "POST",
                data: {action: "getSpecies", city_id:$(this).val()},
                dataType: "json",
                success: function(result) {
                    if(result.length > 0){                        
                        let selectorHTML='';
                        result.forEach(function(d){
                            selectorHTML+='<label class="swatch"><input type="checkbox" name="species[]" value="'+d.attribute_id+'" />';
                            if(d.image_url){
                                selectorHTML+='<img src="'+d.image_url+'" alt="'+d.attribute_name+'"/><div class="check"></div>';
                            }
                            selectorHTML += d.attribute_name + '</label>';
                        });                                  
                        $('.species-wrapper').append(selectorHTML);
                        $('<button type="button" id="getFishingTypesButton" class="btn-primary pull-right">Get Fishing Types</button><div class="clearfix"></div>').insertAfter('.species-wrapper');
                    }
                },
                error: function(err) {
                    console.log(err);
                }
        });
        })

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
                data: {action: "getFishingTypes", city_id:$('#citySelect').val(),species:species},
                dataType: "json",
                success: function(result) {
                    if(result.length > 0){                        
                        let selectorHTML='';
                        result.forEach(function(d){
                            selectorHTML+='<input type="checkbox" name="fishingTypes[]" value="'+d.attribute_id + '">'+d.attribute_name+'';
                        });          
                        selectorHTML +='<button type="button" class="btn-primary" id="getTechniqueButton">Get Fishing Techinique</button>'
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
                data: {action: "getTechnique", city_id:$('#citySelect').val(),species:species,fishing_types:fishingTypes},
                dataType: "json",
                success: function(result) {
                    if(result.length > 0){                        
                        let selectorHTML='';
                        result.forEach(function(d){
                            selectorHTML+='<input type="checkbox" name="technique[]" value="'+d.attribute_id + '">'+d.attribute_name+'';
                        });          
                        selectorHTML +='<button type="button" class="btn-primary" id="createTacklebox">Create a tacklebox</button>';
                        $('.technique-wrapper').append(selectorHTML);
                    }
                },
                error: function(err) {
                    console.log(err);
                }
            });
        });

        $(document).on('click','#createTacklebox',function(){
            window.location.href = "http://localhost/shopify-fishinmybestlife.com/php-app/create-tacklebox.php";
        });
    });    
</script>
</body>
</html>