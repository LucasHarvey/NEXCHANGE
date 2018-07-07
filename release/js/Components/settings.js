/* global Resources,MessageCode,Modal */
var app = app || {
    startup: [],
    afterStartup: []
};

app.settings = {

    editingEmail: false,
    editingPassword: false,
    previousEmail: undefined,
    lastStoredEmail: undefined,

    saveChangesSuccess: function(data) {
        
        // Enable the form
        document.getElementById("saveChanges").disabled = false;
        document.getElementById('userData').addEventListener('submit', app.settings.saveChanges);

        // Do not allow the user to dismiss the modal
        new Modal("User Updated", MessageCode(data.payload.messageCode), {
            text: "Okay",
            callback: function() {
                window.location.reload();
            }
        }, false).show();
    },

    saveChangesFailure: function(data) {
        
        // Enable the form
        document.getElementById("saveChanges").disabled = false;
        document.getElementById('userData').addEventListener('submit', app.settings.saveChanges);


        app.handleFailure(data);
        return;
    },

    modifyEmail: function(event) {
        event.preventDefault();
        app.settings.editingEmail = !app.settings.editingEmail;
        if (!app.settings.editingEmail) {
            //We're done editing the email.
            document.getElementById("modifyEmailButton").value = "Edit";
            document.getElementById("email").disabled = true;
            document.getElementById("email").value = app.settings.lastStoredEmail;
            if (!app.settings.editingPassword) {
                document.getElementById("dataConfirmation").style.display = "none";
            }
            return;
        }
        //Editing the email now.
        app.settings.lastStoredEmail = document.getElementById('email').value;
        document.getElementById("modifyEmailButton").value = "Cancel";
        document.getElementById("email").disabled = false;
        document.getElementById("email").select();
        document.getElementById("dataConfirmation").style.display = "flex";
    },

    modifyPassword: function(event) {
        event.preventDefault();
        app.settings.editingPassword = !app.settings.editingPassword;
        if (!app.settings.editingPassword) {
            //We're done editing the password
            document.getElementById("passwordLabel").innerHTML = "Password: ";
            document.getElementById("modifyPasswordButton").value = "Edit";
            document.getElementById("modifyPasswordField").style.display = "none";
            document.getElementById("password").disabled = true;
            document.getElementById("password").value = "**********";
            document.getElementById("passwordConfirmation").value = "";
            if (!app.settings.editingEmail) {
                document.getElementById("dataConfirmation").style.display = "none";
            }
            return;
        }

        //User is editing the password
        document.getElementById("passwordLabel").innerHTML = "New Password: ";
        document.getElementById("password").disabled = false;
        document.getElementById("password").select();
        document.getElementById("password").value = "";
        document.getElementById("passwordConfirmation").value = "";
        document.getElementById("modifyPasswordButton").value = "Cancel";
        document.getElementById("modifyPasswordField").style.display = "flex";
        document.getElementById("dataConfirmation").style.display = "flex";
    },

    validateEmail: function() {
        let email = document.getElementById('email').value;
        // Check if the user submitted an email 
        if (!email) {
            // Warn the user that the email field was left empty (done by modal)
            app.handleFailure({
                messageCode: "MissingArgumentEmail"
            });
            return false;
        }

        // Check if the email is different from the one on the server
        if (email == app.settings.previousEmail) {
            // Warn the user that the email is not new
            app.handleFailure({
                messageCode: "NoChangesEmail"
            });
            return false;
        }
        if (!email.validEmail()) {
            app.handleFailure({
                messageCode: "EmailNotValid"
            });
            return false;
        }
        return true;
    },

    //CHANGE IN PASSWORD.JS TOO.
    validatePassword: function() {
        let newPassword = document.getElementById('password').value;
        let newPasswordConfirmation = document.getElementById('passwordConfirmation').value;
        // Check if the user submitted a new password and confirmed it
        if (!newPassword || !newPasswordConfirmation) {
            app.handleFailure({
                messageCode: "MissingArgumentsPasswords"
            });
            return false;
        }
        
        if(newPassword.length < 9 || newPasswordConfirmation.length < 9){
            app.handleFailure({
                messageCode: "PasswordTooSmall"
            });
            return;
        }

        // Check if the new password matches the new password confirmation
        if (newPassword != newPasswordConfirmation) {
            app.handleFailure({
                messageCode: "PasswordsNoMatch"
            });

            return false;
        }
        return true;
    },

    saveChanges: function(event) {
        event.preventDefault();

        var email = null;
        if (this.editingEmail) {
            if (!app.settings.validateEmail()) {
                return;
            }
            email = document.getElementById('email').value;
        }

        var password = null;
        if (this.editingPassword) {
            if (!app.settings.validatePassword()) {
                return;
            }
            password = document.getElementById('password').value;
        }

        if (!(this.editingPassword || this.editingEmail)) {
            return;
        }

        let currentPassword = document.getElementById('currentPassword').value;
        if (!currentPassword) {
            app.handleFailure({
                messageCode: "MissingArgumentCurrentPassword"
            });
            return;
        }
        
        if(currentPassword.length < 9){
            app.handleFailure({
                messageCode: "PasswordTooSmall"
            });
            return;
        }
        
        // Disable the form
        document.getElementById("saveChanges").disabled = true;
        document.getElementById('userData').removeEventListener('submit', app.settings.saveChanges);

        Resources.Users.PUT(email, password, currentPassword, this.saveChangesSuccess, this.saveChangesFailure);
    },

    populateUserFields: function(data) {
        let user = data.payload.user;
        app.settings.previousEmail = user.email;
        document.getElementById("userName").innerText = user.firstName + " " + user.lastName;
        document.getElementById("studentId").innerText = user.studentId;
        document.getElementById("email").value = user.email;
    },

    logoutEverywhere: function() {
        
        // Disable the "Logout everywhere" button
        document.getElementById("logoutEverywhere").disabled = true;
        
        Resources.Auth.DELETEALL(function(response) {
            
            // Enable the "Logout everywhere" button
            document.getElementById("logoutEverywhere").disabled = false;

            new Modal("User Unauthenticated", MessageCode(response.payload.messageCode), null, {
                text: "Okay"
            }).show();
        }, function(response){
            // Failure callback
            
            // Enable the "Logout everywhere" button
            document.getElementById("logoutEverywhere").disabled = false;
            
            app.handleFailure(response)
        });
    }
};

app.startup.push(function settingsStartup() {
    app.settings.saveChanges = app.settings.saveChanges.bind(app.settings);
    document.getElementById("logoutEverywhere").addEventListener('click', app.settings.logoutEverywhere);
    document.getElementById("modifyEmailButton").addEventListener('click', app.settings.modifyEmail);
    document.getElementById("modifyPasswordButton").addEventListener('click', app.settings.modifyPassword);
    document.getElementById('userData').addEventListener('submit', app.settings.saveChanges);

});

app.afterStartup.push(function settingsAfterStartup() {
    Resources.Users.GET(app.settings.populateUserFields);
});
