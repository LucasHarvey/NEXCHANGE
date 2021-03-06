<!DOCTYPE html>
<html>

<head>
    <title>Forgot Password | NEXCHANGE</title>
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

    <script async type="text/javascript" src="js/Components/passwordforgot.js?v=5"></script>
</head>

<body>

    <!-- Header -->
    <header class="header">
        <a class="logo" href="./login"><img src="./img/jac_logo.png" alt="John Abbott Logo"></a>
    </header>

    <div>
        <aside class="aside1"></aside>
        <div class="main">
            
            <h1>Forgot Password</h1>
            <p>
                If the student ID and email match what we have in our databases, we will send you an email with a link to reset your password.
                Please contact the Access Centre for further assistance.
            </p>
            
            <div class="userform-wrapper">
                <form id="userData" class="userform">
                    <div class="userfield">
                        <label for="userid">Student ID:</label>
                        <input type="number" id="userid" name="userid" min="1000000" max="9999999" placeholder="Student ID" autocomplete="off" required>
                    </div>
                    <div class="userfield">
                        <label for="email">Email: </label>
                        <input type="email" id="email" name="email" placeholder="Email" maxlength="255" autocomplete="off" required>
                        
                    </div>
                    <div class="userfield">
                        <input type="submit" id="sendRequest" class="button" name="submit" value="Reset Password" />
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
