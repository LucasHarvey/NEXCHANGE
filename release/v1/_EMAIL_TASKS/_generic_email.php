<?php
function send_email($conn, $notificationCode, $userid, $to, $subject, $message, $async = false){
    $headers = "From: no-reply.nexchange@johnabbott.qc.ca\r\nContent-type: text/plain\r\n";
    
    if(!$async){
        if(mail($to, $subject, $message, $headers)){
            database_insert($conn, "INSERT INTO log_notifications_sent (user_id, notification_code) values (?,?)", "si", array($userid, $notificationCode));
            return true;
        }
        return false;
    }else{
        database_insert($conn, "INSERT INTO log_notifications_sent (user_id, notification_code) values (?,?)", "si", array($userid, 10 + $notificationCode));
        //Replace all single and double quotes with escaped quote.
        $subject = htmlentities($subject, ENT_QUOTES);
        $message = htmlentities($message, ENT_QUOTES);
        
        execInBackground("php _EMAIL_TASKS/_background_email.php $to '$subject' '$message'");
    }
}

function execInBackground($cmd) { 
    exec($cmd . " > /dev/null &");   
} 
?>
