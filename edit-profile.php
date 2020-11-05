<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in, if yes then redirect him to welcome page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
 
// Include config file
require_once "db.php";

global $conn;

$sql = "SELECT * FROM Member WHERE member_email_id = ".$_SESSION['id'];

$loggedin_user = $conn->query($sql);

while($row = $loggedin_user->fetch_array()){
    $loggedin_users[] = $row;
}


$sql = "SELECT member_type_id, member_type FROM MemberType WHERE active = 1";
$membertypes = $conn->query($sql);

while($row = $membertypes->fetch_array()){
    $rows[] = $row;
}

$fname_err = $lname_err = $phone_err = $bio_err = $nickname_err=$address_err= "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    
    // Validate username
    if(empty(trim($_POST["fname"]))){
        $fname_err = "Please enter your first name.";
    }
    
    if(empty(trim($_POST["lname"]))){
        $lname_err = "Please enter your last name.";
    }
    
    if(empty(trim($_POST["phone"]))){
        $phone_err = "Please enter your phone number.";
    }

    if(empty(trim($_POST["nickname"]))){
        $nickname_err = "Please enter your display name";
    }

    if(empty(trim($_POST["bio"]))){
        $bio_err = "Please enter your bio";
    }

    if(empty(trim($_POST["address_changed"]))){
        $address_err = "Please enter your address";
    }else if($_POST['address_changed']=="inputchanges"){
        $address_err = "Please enter a valid address";
    }

    $sql = "SELECT city_id FROM advisor_city WHERE city = '".$_POST['city']."'";
    $cityresult = $conn->query($sql);

    if($cityresult->num_rows==0){
        $sql = "INSERT INTO advisor_city (city, state, country) VALUES (?,?,?)";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "sss", $_POST['city'],$_POST['state'],$_POST['country']);
            if(mysqli_stmt_execute($stmt)){ 
                echo "That city is successfully added";
            }else{
                echo "Could't save that city";
            }
        }
    }

    // Check input errors before inserting in database
    if(empty($fname_err) && empty($lname_err) && empty($phone_err)&& empty($nickname_err)&& empty($bio_err)&& empty($address_err)){
                    
        // Prepare an insert statement
        $sql = "UPDATE Member SET (first_name, last_name, email, phone, address,city,state,postal_code,country,member_type_id,bio,shopify_customer_id,own_boat, nickname, member_email_id,active) VALUES (?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,1) WHERE member_email_id=".$_SESSION['id'];
                 
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssssssssissisi", $param_fname,$param_lname,$param_email,$param_phone,$param_address,$param_city,$param_state,$param_postalcode,$param_country,$param_member_type_id,$param_bio,$param_shopify_customer_id,$param_own_boat,$param_nickname,$param_member_email_id);
            
            // Set parameters
            $param_fname = $_POST['fname'];
            $param_lname = $_POST['lname'];
            $param_email = $_SESSION["username"];
            $param_phone = $_POST['fname'];
            $param_address = $_POST['address'];
            $param_city = $_POST['city'];
            $param_state = $_POST['state'];
            $param_postalcode = $_POST['zipcode'];
            $param_country = $_POST['country'];
            $param_member_type_id = $_POST['membertype'];
            $param_bio = $_POST['bio'];            
            $param_nickname = $_POST['nickname'];
            $param_member_email_id = $_SESSION["id"];
            $param_shopify_customer_id = 11111;
            $param_own_boat = isset($_POST['ownboat'])?1:0;
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){   
                $page_msg = 'Updated Successfully';
                // header("location: index.php");
            } else{
                echo "Something went wrong. Please try again later.";
                echo $stmt->error;
            }
            echo $stmt->error;
            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($conn);
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
    </head>
    <body class="profile-setup">
        <div class="login-content page-content">
            <div class="login-header text-center"><img src="assets/imgs/logo.png" alt="Fish in my best life" /></div>
            <h1 class="page-title">Edit your profile</h2>
            <div class="content">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="login-form-container flex-wrapper">            
                <div class="full">
                    <?php echo isset($page_msg)?$page_msg:"";?>
                    <div class="form-control">
                        <label for="fname">First name <span class="required">*</span></label>
                        <input type="text" name="fname" id="fname" required  value="<?php echo isset($_POST['fname'])?$_POST['fname']:$loggedin_users[0]['first_name']; ?>"/>
                        <p class="err-msg"><?php echo $fname_err; ?></p>
                    </div>
                    <div class="form-control">
                        <label for="lname">Last name <span class="required">*</span></label>
                        <input type="text" name="lname" id="lname" required  value="<?php echo isset($_POST['lname'])?$_POST['lname']:$loggedin_users[0]['last_name']; ?>"/>
                        <p class="err-msg"><?php echo $lname_err; ?></p>
                    </div>
                    <div class="form-control">
                        <label for="phone">Phone<span class="required">*</span></label>
                        <input type="text" name="phone" id="phone" required  value="<?php echo isset($_POST['phone'])?$_POST['phone']:$loggedin_users[0]['phone']; ?>"/>
                        <p class="err-msg"><?php echo $phone_err; ?></p>
                    </div>
                    <div class="form-control">
                        <label for="completeaddress">Address<span class="required">*</span></label>
                        <input type="text" name="completeaddress" id="autocomplete" onFocus="geolocate()" required  value="<?php echo isset($_POST['completeaddress'])?$_POST['completeaddress']:$loggedin_users[0]['address'].' '.$loggedin_users[0]['city'].' '.$loggedin_users[0]['state'].' '.$loggedin_users[0]['country']; ?>"/>
                        <p class="err-msg"><?php echo $address_err; ?></p>
                    </div>
                    <div class="form-control">
                        <label for="nickname">Display name<span class="required">*</span></label>
                        <input type="text" name="nickname" id="nickname" required  value="<?php echo isset($_POST['nickname'])?$_POST['nickname']:$loggedin_users[0]['nickname']; ?>"/>
                        <p class="err-msg"><?php echo $nickname_err; ?></p>
                    </div>
                    <div class="form-control">
                        <label for="nickname">Member Type<span class="required">*</span></label>
                        <select name="membertype" id="membertype">
                            <?php
                            foreach ($rows as $row) {
                                $selected = ($row['member_type_id']==$_POST['membertype']||$row['member_type_id']==$loggedin_users[0]['member_type_id'])?'selected':'';
                                echo '<option value="'.$row['member_type_id'].'" '.$selected.'>'.$row['member_type'].'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-control">
                        <label for="nickname">Bio<span class="required">*</span></label>
                        <textarea name="bio" id="bio" required><?php echo isset($_POST['bio'])?$_POST['bio']:$loggedin_users[0]['bio']; ?></textarea>
                        <p class="err-msg"><?php echo $bio_err; ?></p>
                    </div>
                    <input type="hidden" name="city" id="locality" required value="<?php echo isset($_POST['city'])?$_POST['city']:$loggedin_users[0]['city']; ?>"/>
                    <input type="hidden" name="state" id="administrative_area_level_1" required value="<?php echo isset($_POST['state'])?$_POST['state']:$loggedin_users[0]['state']; ?>"/>
                    <input type="hidden" name="zipcode" id="postal_code" required value="<?php echo isset($_POST['zipcode'])?$_POST['zipcode']:$loggedin_users[0]['postal_code']; ?>"/>
                    <input type="hidden" name="address" id="route" required value="<?php echo isset($_POST['address'])?$_POST['address']:$loggedin_users[0]['address']; ?>"/>
                    <input type="hidden" name="country" id="country" required value="<?php echo isset($_POST['country'])?$_POST['country']:$loggedin_users[0]['country']; ?>"/>
                    <input type="hidden" name="address_changed" id="address_changed" value="<?php echo isset($_POST['address_changed'])?$_POST['address_changed']:'autocompleted' ?>" />
                    <div class="form-control">
                        <label for="ownboat"><input type="checkbox" name="ownboat" id="ownboat" <?php echo isset($_POST['ownboat'])?'checked':''?> />Own boat</label>                        
                    </div>
                    <button type="submit" class="btn-primary">Save</button>    
                </div>                                                                                
            </div>
            </form>
            </div>
        </div>        
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>        
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCSVxstLlVUrrzSNSbZbp-646V3w8TH6PM&callback=initAutocomplete&libraries=places&v=weekly" defer></script>
        <script type="text/javascript">
        let placeSearch;
        let autocomplete;
        const componentForm = {            
            route: "long_name",
            locality: "long_name",
            administrative_area_level_1: "short_name",
            country: "short_name",
            postal_code: "short_name",
        };

        function initAutocomplete() {
            // Create the autocomplete object, restricting the search predictions to
            // geographical location types.
            autocomplete = new google.maps.places.Autocomplete(
            document.getElementById("autocomplete"),
            { types: ["geocode"] }
            );
            // Avoid paying for data that you don't need by restricting the set of
            // place fields that are returned to just the address components.
            autocomplete.setFields(["address_component"]);
            // When the user selects an address from the drop-down, populate the
            // address fields in the form.
            autocomplete.addListener("place_changed", fillInAddress);
        }

        function fillInAddress() {
            $("#address_changed").val("autocompleted");
            // Get the place details from the autocomplete object.
            const place = autocomplete.getPlace();

            for (const component in componentForm) {
            document.getElementById(component).value = "";
            document.getElementById(component).disabled = false;
            }

            // Get each component of the address from the place details,
            // and then fill-in the corresponding field on the form.
            for (const component of place.address_components) {
            const addressType = component.types[0];

            if (componentForm[addressType]) {
                const val = component[componentForm[addressType]];
                document.getElementById(addressType).value = val;
            }
            }
        }

        // Bias the autocomplete object to the user's geographical location,
        // as supplied by the browser's 'navigator.geolocation' object.
        function geolocate() {
            $("#address_changed").val("inputchanges");
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    const geolocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    };
                    const circle = new google.maps.Circle({
                    center: geolocation,
                    radius: position.coords.accuracy,
                    });
                    autocomplete.setBounds(circle.getBounds());
                });
            }else{
                console.log('aaaa');
            }
        }      
    </script>
    </body>
</html>