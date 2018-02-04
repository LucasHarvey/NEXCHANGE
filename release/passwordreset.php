<!DOCTYPE html>
<html>

<head>
    <title>Reset Password | NEXCHANGE</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="js/Polyfills/flexibility.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#002b80"/>

    <script async type="text/javascript" src="js/MessageCode.js"></script>
    <script async type="text/javascript" src="js/Resources.js"></script>
    <script async type="text/javascript" src="js/app.js"></script>
    <script async type="text/javascript" src="js/Components/modal.js"></script>

    <script async type="text/javascript" src="js/Components/password.js"></script>
</head>

<body>

    <!-- Header -->
    <header class="header">
        <a class="logo" href="./login"><img src="./img/jac_logo.png" alt="John Abbott Logo"></a>
    </header>

    <div>
        <aside class="aside1"></aside>
        <div class="main">
            
            <div class="userform-wrapper">
                <a class="feedbackLink" href="https://goo.gl/forms/QbWXdBqwvv6H0Tkq1">Feedback and Bug Report</a>
            </div>
            
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
        <img src="./img/nexchange_official_logo.png" alt="Nexchange Logo">
        <div>
            <small><a href="./license">&copy; Copyright 2018 Lucas Harvey All Rights Reserved</a></small>
            <small>Created in collaboration with Zackary Therrien</small>
        </div>
    </footer>
</body>

</html>
