<?php
function send_email($conn, $notificationCode, $userid, $to, $subject, $message){
    $headers = "From: no-reply.nexchange@johnabbott.qc.ca\r\n";
    
    if(mail($to, $subject, $message, $headers)){
        database_insert($conn, "INSERT INTO log_notifications_sent (user_id, notification_code) values (?,?)", "si", array($userid, $notificationCode));
        return true;
    }
    return false;
}
?>