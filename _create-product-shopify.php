<?php
require_once "config.php";

$products_array = array(
    "product" => array( 
        "title"        => "Fishing Report Temp",
        "vendor"       => "FishinMyBestLife",
        "product_type" => "Angler Advisor | Fishing Reports | Main",
        'template_suffix' => "report",
        "published"    => false ,
        'status'       => 'draft',
        "metafields"   => array(array("key"=> "new","value"=> "newvalue","value_type"=> "string","namespace"=> "report"),array("key"=> "new2","value"=> "newvalue2","value_type"=> "string","namespace"=> "report"))
    )
);



echo date_format(date_create("10/29/2020"),"M-d-Y");
// var_dump($products_array['product']);
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

// var_dump(json_decode($response,true));
echo array_key_exists ('errors', json_decode($response));
?>