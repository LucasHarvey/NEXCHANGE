<?php

include_once("./_generic_email.php");

function reset_password_email($conn, $userid, $email, $token){
    if(empty($email)){
        echoError(null, 500, "InternalServerError", "No email sent to password reset task.");
    }
    if(empty($token)){
        echoError(null, 500, "InternalServerError", "No token sent to password reset task.");
    }
    
    $link = $WEBSERVER_ADDRESS."passwordreset.html?q=".$email_password_token;

    $subject = 'No-Reply: Password Reset Request';
    $message = "We've received a request to reset your account password linked with this email.\n\nThis request will expire after 15 minutes. Follow this link to reset your password: $link\n\nIf this was not you, ignore this message.";
    
    send_email($conn, $userid, $email, $subject, $message);
}

?>