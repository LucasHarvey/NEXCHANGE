<?php
class AsyncEmail extends Thread {

    public function __construct($to, $subject, $message, $headers){
        $this->$to = $to;
        $this->$subject = $subject;
        $this->$message = $message;
        $this->$headers = $headers;
    }

    public function run() {
        mail($this->$to, $this->$subject, $this->$message, $this->$headers);
    }
}

function send_email($conn, $notificationCode, $userid, $to, $subject, $message, $wait = true){
    $headers = "From: no-reply.nexchange@johnabbott.qc.ca\r\n";
    
    if($wait){
        if(mail($to, $subject, $message, $headers)){
            database_insert($conn, "INSERT INTO log_notifications_sent (user_id, notification_code) values (?,?)", "si", array($userid, $notificationCode));
            return true;
        }
        return false;
    }else{
        database_insert($conn, "INSERT INTO log_notifications_sent (user_id, notification_code) values (?,?)", "si", array($userid, $notificationCode*10));
        $aEmail = new AsyncEmail($to, $subject, $message, $headers);
        $aEmail->start();
    }
}
?>