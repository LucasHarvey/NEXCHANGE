<?php
$WEBSERVER_ADDRESS = "https://note-share-lucasharvey.c9users.io/html/";

if(empty($email_password_email)){
    echoError(null, 500, "InternalServerError", "No email sent to password reset task.");
}
if(empty($email_password_token)){
    echoError(null, 500, "InternalServerError", "No token sent to password reset task.");
}
$link = $WEBSERVER_ADDRESS."login.html";

$to      = $email_password_email;
$subject = 'No-Reply: NEXCHANGE Account Created';
$message = "Your NEXCHANGE account was created! Your login ID is your student ID, and your temporary password is: $email_password_token<br> You can login at: <a href='$link'>$link</a>";
$headers = 'From: no-reply@nexchange.johnabbott.qc.ca' . "\r\n" . 'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);

//TODO: Log me.

?>