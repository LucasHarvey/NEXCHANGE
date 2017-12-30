/* global Resources, MessageCode, Modal */
var app = app || {
    startup: [],
    afterStartup: []
};

app.user = {
    authToken: null,
    userId: null,
    loginSuccess: function(data) {
        
        //Enable the login button
        document.getElementById("button_login").disabled = false;
        
        app.user.authToken = data.payload.token;
        app.user.loginId = data.payload.loginId;
        app.store("authToken", app.user.authToken);
        app.store("loginId", app.user.loginId);
        document.getElementById("errorTray").style.display = 'none';

        if (data.payload.messageCode == "UserAuthenticated" && data.payload.mustChangePass) {
            let modalContent = "This is the first time you login and you must change your password." +
                "<table><tr align='right'><td align='left'>New password</td><td><input type='password' id='newPassword' placeholder='New Password'></td></tr>" +
                "<tr align='right'><td align='left'>Confirm password</td><td><input type='password' id='confirmPassword' placeholder='Again'></td></tr></table><div id='modalErrorTray'></div>";
            let successBtn = {
                text: "Change Password",
                callback: function() {
                    var curPass = document.getElementById("input_nexPassword").value;
                    var newPass = document.getElementById("newPassword").value;
                    var confPassword = document.getElementById("confirmPassword").value;
                    if (confPassword != newPass) {
                        document.getElementById("modalErrorTray").innerHTML = "Passwords do not match.";
                        document.getElementById("modalErrorTray").className = "error";
                        return;
                    }
                    if(!app.user.verifyUserPass(newPass)){
                        document.getElementById("modalErrorTray").innerHTML = "Password must be 9 characters or more.";
                        return;
                    }
                    document.getElementById("modalErrorTray").innerHTML = "";
                    Resources.Users.PUT(null, newPass, curPass, function() {
                        window.location = data.payload.redirect.url;
                    }, function(d) {
                        new Modal("Error", MessageCode["PasswordUpdateFailure"], null, {
                            text: "Okay",
                            // Callback for the modal which tells the user that password update failed
                            callback: function() {
                                window.location = "./settings.html";
                            }
                        }).show();
                    });
                }
            };
            
            new Modal("Change Password", modalContent, successBtn, false).show();

            return;
        }
        var nextLoc = app.getStore("login_nextLocation") || data.payload.redirect.url;
        app.store("login_nextLocation", null);
        window.location = nextLoc;
    },
    failure: function(data) {
        document.getElementById("errorTray").style.display = 'block';
        document.getElementById("errorTray").innerHTML = MessageCode[data.messageCode] + "<br>";
        
        //Enable the login button
        document.getElementById("button_login").disabled = false;
    },
    logout: function(e, forced) {
        e.preventDefault();
        console.log("Logging out.");

        Resources.Auth.DELETE(function() {
            app.store("authToken", null);
            window.location = "./login.html";
        }, function(data) {
            if (data.statusCode == 401 || data.statusCode == 403) {
                console.warn("Already signed out...");
                app.store("authToken", null);
                window.location = "./login.html";
                return;
            }
            new Modal("Error", "You could not be signed out because: " + MessageCode[data.messageCode], null, { text: "Okay" }).show();
        });
    },
    login: function(e) {
        e.preventDefault();
        document.getElementById("errorTray").style.display = 'none';
        document.getElementById("errorTray").innerText = "";


        let studentId = document.getElementById("input_userId").value;
        let password = document.getElementById("input_nexPassword").value;
        if (!studentId) {
            app.user.failure({
                messageCode: "MissingArgumentUserId"
            });
            return;
        }
        if (!password) {
            app.user.failure({
                messageCode: "MissingArgumentPassword"
            });
            return;
        }
        if (!app.user.verifyUserPass(password)) {
            app.user.failure({
                messageCode: "PasswordTooSmall"
            });
            return;
        }

        if (!app.user.verifyUserId(studentId)) {
            app.user.failure({
                messageCode: "UserIdNotValid"
            });
            return;
        }
        document.getElementById("button_login").disabled = true;
        Resources.Auth.POST(studentId, password, app.user.loginSuccess, app.user.failure, { disableAuthResult: true });
    },

    verifyUserId: function(userId) {
        return userId && (userId.length == 7);
    },
    
    verifyUserPass: function(password){
        return password && (password.length >= 9);
    }
};

app.startup.push(function userStartup() {
    let logout = document.getElementById('logout');
    if (logout) {
        logout.addEventListener('click', app.user.logout);
    }

    let loginForm = document.getElementById("loginData");
    if (loginForm) {
        loginForm.addEventListener("submit", app.user.login);
    }

    app.user.authToken = app.getStore("authToken");
    app.user.loginId = app.getStore("loginId");
    (document.getElementById("input_userId") || {}).value = app.user.loginId || "";
});
