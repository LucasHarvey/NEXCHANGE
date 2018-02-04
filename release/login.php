<?php
include_once "v1/_globals.php";
include_once "v1/_database.php";
include_once "v1/_authentication.php";

function globalErrorHandler($errorNumber, $errorString, $errorFile, $errorLine){
    error_log("$errorNumber - $errorString in $errorFile on line $errorLine", 0);
    exit;
}

error_reporting(E_ALL);
set_error_handler("globalErrorHandler");
    
function echoError($conn, $status, $messageCode, $message = ""){
    error_log("EchoError: $status - $messageCode with M: $message", 0);
    if($conn != null){ //would occur if error happened in a script without need of a DB...?!
        if($GLOBALS['NEXCHANGE_TRANSACTION']){
            if(!database_rollback($conn)){
                $GLOBALS['NEXCHANGE_TRANSACTION'] = false; //Prevent infinite loop of not being able to rollback transaction.
        		echoError($conn, 500, "DatabaseRollbackError", "Could not rollback the transaction");
        	}
        }
        $conn->close();
    }
    exit;
}

$conn = database_connect();
if(authorized($conn)[0]){
    $priv = getUserPrivilege();
    header("Location: https://".$GLOBALS['NEXCHANGE_DOMAIN']."/".$GLOBALS['NEXCHANGE_LANDING_PAGES'][$priv]);
    exit;
}
?>
<!DOCTYPE html>
<html class="loginHtml">

<head>
    <title>Log In | NEXCHANGE</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="js/Polyfills/flexibility.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#3f5374"/>

    <script async type="text/javascript" src="js/app.js"></script>
    <script async type="text/javascript" src="js/MessageCode.js"></script>
    <script async type="text/javascript" src="js/Resources.js"></script>
    <script async type="text/javascript" src="js/Components/user.js"></script>
    <script async type="text/javascript" src="js/Components/modal.js"></script>
</head>

<body class="loginBody">
    <div class="login">
        <div class="loginShading"></div>
    
        <section class="middle">
            <header>
                <img src="./img/jac_logo.png" alt="John Abbott College Logo">
            </header>
            <form id="loginData" class="userform">
                <div class="userfield">
                    <input id="input_userId" type="text" placeholder="User ID" required><br>
                </div>
                <div class="userfield">
                    <input id="input_nexPassword" type="password" placeholder="Password" required><br>
                </div>
                <div class="userfield">
                    <input class="button" type="submit" id="button_login" value="Log In">
                </div>
            </form>
            <a href="./passwordforgot.html" class="forgotPsswd">Forgot your password?</a>
            <div id="errorTray" class="errorTray" style="display: none;"></div>
        </section>
    
    
    </div>
    
    <footer>
        <img src="./img/nexchange_official_logo_white.png" alt="Nexchange Logo">
        <div>
            <small><a href="./license.html">&copy; Copyright 2018 Lucas Harvey All Rights Reserved</a></small>
            <small>Created in collaboration with Zackary Therrien</small>
        </div>
    </footer>
</body>

</html>
