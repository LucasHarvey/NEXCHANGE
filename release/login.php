<!DOCTYPE html>
<html class="loginHtml">

<head>
    <title>Log In | NEXCHANGE</title>
    <link rel="shortcut icon" type="image/png" href="./img/favicon.png"/>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/style.css?v=5">
    <script src="js/Polyfills/flexibility.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#3f5374"/>

    <script async type="text/javascript" src="js/app.js?v=5"></script>
    <script async type="text/javascript" src="js/MessageCode.js?v=5"></script>
    <script async type="text/javascript" src="js/Resources.js?v=5"></script>
    <script async type="text/javascript" src="js/Components/user.js?v=5"></script>
    <script async type="text/javascript" src="js/Components/modal.js?v=5"></script>
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
            <a href="./forgotpassword" class="forgotPsswd">Forgot your password?</a>
            <div id="errorTray" class="errorTray" style="display: none;"></div>
        </section>
    
    
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
