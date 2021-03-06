<!DOCTYPE html>
<html>

<head>
    <title>My Profile | NEXCHANGE</title>
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
    <script async type="text/javascript" src="js/navbar.js?v=5"></script>
    <script async type="text/javascript" src="js/Components/modal.js?v=5"></script>
    <script async type="text/javascript" src="js/Components/user.js?v=5"></script>

    <script async type="text/javascript" src="js/Components/settings.js?v=5"></script>
</head>

<body>

    <!-- Header -->
    <header class="header">
        <a class="logo" href="
<?php

include_once "./v1/_globals.php";
include_once "./v1/_authentication.php"; 
if(getUserPrivilege() == "ADMIN"){
    echo "./signup";
} else {
    echo "./home";
    
}

?>
        
        "><img src="./img/jac_logo.png" alt="John Abbott Logo"></a>
        <nav>
            <div id="navbar">
                <aside></aside>
                <div id="navmain">
                    <a id="logout">LOG OUT</a>
                </div>
                <aside></aside>
            </div>
        </nav>
    </header>

    <div>
        <aside class="aside1"></aside>
        <div class="main">

            <h1>My Profile</h1>

            <div class="userform-wrapper">
                <form id="userData" class="userform">
                    <div class="userfield">
                        <label for="name">Name: </label>
                        <span id="userName"></span>
                    </div>

                    <div class="userfield">
                        <label for="studentId">Student ID: </label>
                        <span id="studentId"></span>
                    </div>

                    <div class="userfield">
                        <label for="email">Email: </label>
                        <div class="subfield">
                            <input class="grow" type="email" id="email" placeholder="Email" disabled autocomplete="off" maxlength="255" />
                            <input type="button" class="button" id="modifyEmailButton" value="Edit">
                        </div>
                    </div>

                    <div class="userfield">
                        <label for="password" id="passwordLabel">Password: </label>
                        <div class="subfield">
                            <input class="grow" type="password" id="password" value="**********" placeholder="Password" disabled autocomplete="off" />
                            <input type="button" id="modifyPasswordButton" class="button" value="Edit"/>
                        </div>
                    </div>
                    <div class="userfield" id="modifyPasswordField" style='display:none;'>
                        <label for="password">Enter Again: </label>
                        <input type="password" id="passwordConfirmation" placeholder="Confirm Password" autocomplete="off" />
                    </div>
                    
                    <div class="userfield" id="dataConfirmation" style="display:none;">
                        <label for="password">Current Password: </label>
                        <div class="subfield">
                            <input class="grow" type="password" id="currentPassword" placeholder="Current Password" autocomplete="off" required>
                            <input type="submit" id="saveChanges" class="button" value="Save">
                        </div>
                    </div>
                    
                    <div class="userfield">
                        <input type="button" class="button" id="logoutEverywhere" value="Log out from other devices">
                    </div>
                    
                    <div class="userform-wrapper forgotPsswdLink">
                        <a class="passwordLink" href="./forgotpassword">Forgot your password?</a>
                    </div>
                    
                    <div class="userform-wrapper bugReportLink">
                        <a class="feedbackLink" target="_blank" href="https://goo.gl/forms/QbWXdBqwvv6H0Tkq1">Feedback and Bug Report</a>
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
