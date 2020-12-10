<?php
require_once('vendor/PHPMailer/PHPMailerAutoload.php');

$mail = new PHPMailer();
$mail->isSMTP();
$mail->SMTPAuth = true;
$mail->SMTPSecure = 'ssl';
$mail->Host = 'smtp.gmail.com';
$mail->Port = '465';
$mail->isHTML();
$mail->Username = 'jlmobile710@gmail.com';
$mail->Password = '#aaaa';
$mail->SetFrom('your-email-id@gmail.com','Your Name To Be Displayed');
$mail->Subject = "Your email Subject";
$mail->Body = 'Any HTML content';

$mail->AddAddress('jlmobile710@gmail.com');

$result = $mail->Send();
echo $mail->ErrorInfo;
if($result == 1){
    echo "OK Message";
}
else{
    echo "Sorry. Failure Message";
}
?>