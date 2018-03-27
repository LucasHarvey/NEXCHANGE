<?php
$to = $argv[1];
$subject = $argv[2];
$message = $argv[3];

$subject = html_entity_decode($subject);
$message = html_entity_decode($message);
        
$headers = "From: no-reply.nexchange@johnabbott.qc.ca\r\nContent-type: text/plain\r\n";
mail($to, $subject, $message, $headers);
?>