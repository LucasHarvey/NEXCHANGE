<?php

include_once "./v1/_globals.php";
include_once "./v1/_authentication.php";

// Verify that the user is an admin
if(getUserPrivilege() != "ADMIN"){
    http_response_code(403);
    die();
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>User Sign Up | NEXCHANGE</title>
    <link rel="shortcut icon" type="image/png" href="./img/favicon.png"/>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/style.css?v=4">
    <script src="js/Polyfills/flexibility.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#002b80"/>

    <script async type="text/javascript" src="js/MessageCode.js?v=4"></script>
    <script async type="text/javascript" src="js/Resources.js?v=4"></script>
    <script async type="text/javascript" src="js/app.js?v=4"></script>
    <script async type="text/javascript" src="js/navbar.js?v=4"></script>
    <script async type="text/javascript" src="js/Components/modal.js?v=4"></script>
    <script async type="text/javascript" src="js/Components/user.js?v=4"></script>

    <script async type="text/javascript" src="js/Components/signup.js?v=4"></script>
</head>

<body>

    <!-- Header -->
    <header class="header">
        <a class="logo" href="./signup"><img src="./img/jac_logo.png" alt="John Abbott Logo"></a>
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
            
            <h1>Sign Up</h1>
            
            <div class="userform-wrapper">
                <form id="userData" class="userform">
                    <div class="userfield">
                        <input type="text" id="firstName" name="firstName" placeholder="First Name" maxlength="40" required>
                        <input type="text" id="lastName" name="lastName" placeholder="Last Name" maxlength="60" required>
                    </div>

                    <div class="userfield">
                        <input type="number" id="studentId" name="studentId" min="1000000" max="9999999" placeholder="Student ID" required>
                    </div>

                    <div class="userfield">
                        <input type="email" id="email" name="email" placeholder="Email" maxlength="255" required>
                    </div>

                    <div class="userfield">
                        <input class="button" type="submit" id="submit" name="submit" value="Sign Up">
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
