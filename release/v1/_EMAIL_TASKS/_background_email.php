<?php
$to = $argv[1];
$subject = $argv[2];
$message = $argv[3];

$headers = "From: no-reply.nexchange@johnabbott.qc.ca\r\n";
mail($to, $subject, $message, $headers);
?>