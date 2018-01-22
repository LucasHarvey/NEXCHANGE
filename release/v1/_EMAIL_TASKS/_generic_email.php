<?php

function send_email($conn, $to, $subject, $message){
    $headers = 'From: no-reply@nexchange.johnabbott.qc.ca' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    
    mail($to, $subject, $message, $headers);
}

?>