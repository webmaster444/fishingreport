<?php
require_once('vendor/PHPMailer/PHPMailerAutoload.php');
require_once "config.php";
global $mail;
global $mail_pwd;
$mail = new PHPMailer();
$mail->isSMTP();
$mail->SMTPAuth = true;
$mail->SMTPSecure = 'tls';
$mail->Host = 'smtp.gmail.com';
$mail->Port = '587';
$mail->isHTML();
$mail->Username = $mail;
$mail->Password = $mail_pwd;
$mail->SetFrom('drupio@gmail.com','Dru Pio');
$mail->Subject = "Approve new catch log";
$mail->Body = '<a href="https://dru-pio.myshopify.com/admin/products?selectedView=all&product_type=Angler%20Advisor%20%7C%20Fishing%20Reports%20%7C%20Catch%20Logs&order=created_at%20desc">Approve new catch log</a>';

$mail->AddAddress('devwork8888@gmail.com');

$result = $mail->Send();
echo $mail->ErrorInfo;
if($result == 1){
    echo "OK Message";
}
else{
    echo "Sorry. Failure Message";
}
?>