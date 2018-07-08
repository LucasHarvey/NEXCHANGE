<?php

include_once "./v1/_database.php";

$conn = database_connect();

$code = $_GET["q"];

if($code == null){
    header('Location: ./invalidLink');
    die();
}

$result = database_get_row($conn, "SELECT id FROM users WHERE passresetcode=? AND privilege='USER' AND DATE_ADD(passresetcreated, INTERVAL 15 MINUTE) > NOW()", "s", $code);

if(!$result){
    header('Location: ./invalidLink');
    die();
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Reset Password | NEXCHANGE</title>
    <link rel="shortcut icon" type="image/png" href="./img/favicon.png"/>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/style.css?v=5">
    <script src="js/Polyfills/flexibility.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#002b80"/>

    <script async type="text/javascript" src="js/MessageCode.js?v=5"></script>
    <script async type="text/javascript" src="js/Resources.js?v=5"></script>
    <script async type="text/javascript" src="js/app.js?v=5"></script>
    <script async type="text/javascript" src="js/Components/modal.js?v=5"></script>

    <script async type="text/javascript" src="js/Components/password.js?v=5"></script>
</head>

<body>

    <!-- Header -->
    <header class="header">
        <a class="logo" href="./login"><img src="./img/jac_logo.png" alt="John Abbott Logo"></a>
    </header>

    <div>
        <aside class="aside1"></aside>
        <div class="main">
            
            <h1>Reset Password</h1>
            
            <div class="userform-wrapper">
                <form id="userData" class="userform">
                    <div class="userfield">
                        <label for="newPassword">New Password: </label>
                        <input type="password" id="newPassword" placeholder="New Password" autocomplete="off" />
                    </div>
                    <div class="userfield">
                        <label for="confirmPassword">Enter Again: </label>
                        <input type="password" id="passwordConfirmation" placeholder="Confirm Password" autocomplete="off" />
                    </div>
                    <div class="userfield">
                        <input type="submit" id="saveChanges" class="button" value="Reset Password" />
                    </div>
                </form>
            </div>

        </div>
        <aside class="aside2"></aside>
    </div>
    
    <footer>
        <a href="https://nexchange.ca" target="_blank"><img src="./img/nexchange_official_logo_white.png" alt="Nexchange Logo"></a>
        <div>
            <small><a href="./license">&copy; Copyright 2018 Lucas Harvey All Rights Reserved</a></small>
            <small>Created in collaboration with Zackary Therrien</small>
        </div>
    </footer>
</body>

</html>
