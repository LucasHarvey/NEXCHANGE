<?php

include_once("./_EMAIL_TASKS/_generic_email.php");

function temporary_password_email($conn, $userid, $email, $token){
    if(empty($email)){
        echoError(null, 500, "InternalServerError", "No email sent to temporary password task.");
    }
    if(empty($token)){
        echoError(null, 500, "InternalServerError", "No token sent to temporary password task.");
    }
    
    $link = $GLOBALS['NEXCHANGE_DOMAIN'] . "/login";
    
    $subject = 'No-Reply: NEXCHANGE Account Created';
    $message = "Your NEXCHANGE account was created!\n Your login ID is your student ID, and your temporary password is: $token\n You can login at: $link";
    
    return send_email($conn, 4, $userid, $email, $subject, $message);
}


?>