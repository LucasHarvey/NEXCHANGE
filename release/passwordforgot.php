<!DOCTYPE html>
<html>

<head>
    <title>Forgot Password | NEXCHANGE</title>
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

    <script async type="text/javascript" src="js/Components/passwordforgot.js"></script>
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
            
            <h1>Forgot Password</h1>
            <p>
                If the email and student ID match what we have in our databases, we will send you an email with a link to reset your password.
                Contact ITS or the student Access Centre for assistance.
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
        <img src="./img/nexchange_official_logo.png" alt="Nexchange Logo">
        <div>
            <small><a href="./license">&copy; Copyright 2018 Lucas Harvey All Rights Reserved</a></small>
            <small>Created in collaboration with Zackary Therrien</small>
        </div>
    </footer>
</body>

</html>
