<?php

function send_email($conn, $userid, $to, $subject, $message){
    $headers = 'From: no-reply@nexchange.johnabbott.qc.ca' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    
    mail($to, $subject, $message, $headers);
    
    database_insert($conn, "INSERT INTO log_notifications_sent (user_id, notification_code) values (?,?)", "ss", array($userid, $ip));
}

?>