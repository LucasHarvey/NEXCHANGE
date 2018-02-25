<?php
requiredParams($conn, $_JSON, array("message"));
$message = $_JSON['message'];

logFrontEnd($message);

echoSuccess(null, array("messageCode" => "LoggedSuccessfully"), 201);
?>