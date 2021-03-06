/* global Resources,MessageCode,Modal,location */
var app = app || {
    startup: [],
    afterStartup: []
};

app.user = {
    xsrfToken: null,
    loginId: null,
    loginSuccess: function(data) {
        
        // Enable the form
        document.getElementById("button_login").disabled = false;
        document.getElementById("loginData").addEventListener("submit", app.user.login);
        
        // Note: The JWT is stored in Cookie and only accessible through HTTP
        // The xsrfToken is retrieved from Cookie and store in app.user.xsrfToken
        app.user.xsrfToken = app.getCookie("xsrfToken");
        var nextWindowLocation = data.payload.redirect.url;
        app.user.loginId = data.payload.loginId;
        if(app.getStore("loginId") == app.user.loginId){
            //Is the previous user and the current user the same? If so use the next location if it exists
            nextWindowLocation = app.getStore("login_nextLocation") || data.payload.redirect.url;
        } else {
            app.store("navbar", null);
        }
        app.store("loginId", app.user.loginId);
        document.getElementById("errorTray").style.display = 'none';

        if (data.payload.messageCode == "UserAuthenticated" && data.payload.mustChangePass) {
            let modalContent = "This is the first time you login and you must change your password." +
                "<table><tr align='right'><td align='left'>New password</td><td><input type='password' id='newPassword' placeholder='New Password'></td></tr>" +
                "<tr align='right'><td align='left'>Confirm password</td><td><input type='password' id='confirmPassword' placeholder='Again'></td></tr></table><div id='modalErrorTray'></div>";
            let successBtn = {
                text: "Change Password",
                callback: function(event) {
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
                        document.getElementById("modalErrorTray").className = "error";
                        return;
                    }
                    
                    // Disable the "Change Password" button
                    event.target.disabled = true;
                    
                    document.getElementById("modalErrorTray").innerHTML = "";
     
                    Resources.Users.PUT(null, newPass, curPass, function() {
                        location.assign(data.payload.redirect.url);
                    }, function(d) {
                        let successBtn = {
                            text: "Okay",
                            // Callback for the modal which tells the user that password update failed
                            callback: function() {
                                location.assign("./profile");
                            }
                        };
                        
                        // The modal has a confirm button which redirects the user to "My Profile"
                        new Modal("Error", MessageCode("PasswordUpdateFailure"), successBtn, false).show();
                    });
                    
                    // Hide the "Change Password" modal
                    this.hide();
                }
            };
            
            new Modal("Change Password", modalContent, successBtn, false).show();

            return;
        }
      
        app.store("login_nextLocation", null);
        location.assign(nextWindowLocation);
    },
    failure: function(data) {
        document.getElementById("errorTray").style.display = 'block';
        document.getElementById("errorTray").innerHTML = MessageCode(data.messageCode) + "<br>";
        
        // Enable the form
        document.getElementById("button_login").disabled = false;
        document.getElementById("loginData").addEventListener("submit", app.user.login);
    },
    logout: function(e, forced) {
        e.preventDefault();
        console.log("Logging out.");

        Resources.Auth.DELETE(function() {
            app.store("navbar", null);
            app.store("login_nextLocation", null);
            location.assign("./login");
        }, function(data) {
            if (data.statusCode == 401 || data.statusCode == 403) {
                console.warn("Already signed out...");
                location.assign("./login");
                return;
            }
            new Modal("Error", "You could not be signed out because: " + MessageCode(data.messageCode), null, { text: "Okay" }).show();
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
        if (!this.verifyUserPass(password)) {
            app.user.failure({
                messageCode: "PasswordTooSmall"
            });
            return;
        }

        if (!this.verifyUserId(studentId)) {
            app.user.failure({
                messageCode: "UserIdNotValid"
            });
            return;
        }
        
        // Disable the form
        document.getElementById("button_login").disabled = true;
        document.getElementById("loginData").removeEventListener("submit", app.user.login);
        
        Resources.Auth.POST(studentId, password, this.loginSuccess, this.failure, { disableAuthResult: true });
    },

    verifyUserId: function(userId) {
        return userId && (userId.length == 7);
    },
    
    verifyUserPass: function(password){
        return password && (password.length >= 9);
    }
};

app.startup.push(function userStartup() {
    app.user.login = app.user.login.bind(app.user);
    
    let logout = document.getElementById('logout');
    if (logout) {
        logout.addEventListener('click', app.user.logout);
    }

    let loginForm = document.getElementById("loginData");
    if (loginForm) {
        loginForm.addEventListener("submit", app.user.login);
    }

    app.user.xsrfToken = app.getCookie("xsrfToken");
    app.user.loginId = app.getStore("loginId");
    (document.getElementById("input_userId") || {}).value = app.user.loginId || "";
});
