<?php

include_once("./_generic_email.php");

function reset_password($conn, $userid, $email, $token){
    if(empty($email)){
        echoError(null, 500, "InternalServerError", "No email sent to password reset task.");
    }
    if(empty($token)){
        echoError(null, 500, "InternalServerError", "No token sent to password reset task.");
    }
    
    $link = $WEBSERVER_ADDRESS."passwordreset.html?q=".$email_password_token;

    $subject = 'No-Reply: Password Reset Request';
    $message = "We've received a request to reset your account linked with this email. If this was not you, ignore this message. Follow this link to reset your password: <br> <a href='$link'>$link</a>";
    
    send_email($conn, $userid, $email, $subject, $message);
}

?>