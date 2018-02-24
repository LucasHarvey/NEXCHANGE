<?php
function send_email($conn, $notificationCode, $userid, $to, $subject, $message, $async = false){
    $headers = "From: no-reply.nexchange@johnabbott.qc.ca\r\n";
    
    if(!$async){
        if(mail($to, $subject, $message, $headers)){
            database_insert($conn, "INSERT INTO log_notifications_sent (user_id, notification_code) values (?,?)", "si", array($userid, $notificationCode));
            return true;
        }
        return false;
    }else{
        $cmd = "sendmail -oi -t <<____HERE
$headers To: $email
Subject: $subject

$message
____HERE";
        database_insert($conn, "INSERT INTO log_notifications_sent (user_id, notification_code) values (?,?)", "si", array($userid, 10 + $notificationCode));
        execInBackground($cmd);
    }
}

function execInBackground($cmd) { 
    exec($cmd . " > /dev/null &");   
} 
?>