<?php

include_once("./_generic_email.php");

function temporary_password_email($conn, $userid, $email, $token){
    if(empty($email)){
        echoError(null, 500, "InternalServerError", "No email sent to temporary password task.");
    }
    if(empty($token)){
        echoError(null, 500, "InternalServerError", "No token sent to temporary password task.");
    }
    
    $WEBSERVER_ADDRESS = "https://note-share-lucasharvey.c9users.io/html/";
    $link = $WEBSERVER_ADDRESS."passwordreset.html?q=".$email_password_token;

    $subject = 'No-Reply: NEXCHANGE Account Created';
    $message = "Your NEXCHANGE account was created!\n Your login ID is your student ID, and your temporary password is: $token\n You can login at: $link";
    
    send_email($conn, $userid, $email, $subject, $message);
}


?>