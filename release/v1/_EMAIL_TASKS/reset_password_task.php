<?php
$WEBSERVER_ADDRESS = "https://note-share-lucasharvey.c9users.io/html/";

$email_password_token;
$email_password_email;

if(empty($email_password_email)){
    echoError(null, 500, "InternalServerError", "No email sent to password reset task.");
}
if(empty($email_password_token)){
    echoError(null, 500, "InternalServerError", "No token sent to password reset task.");
}
$link = $WEBSERVER_ADDRESS."passwordreset.html?q=".$email_password_token;

$to      = $email_password_email;
$subject = 'No-Reply: Password Reset Request';
$message = "We've received a request to reset your account linked with this email. If this was not you, ignore this message. Follow this link to reset your password: <br> <a href='$link'>$link</a>";
$headers = 'From: no-reply@nexchange.johnabbott.qc.ca' . "\r\n" . 'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);

?>