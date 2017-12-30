/* global Resources, MessageCode, Modal,getQueryParameterByName */
var app = app || {
    startup: [],
    afterStartup: []
};

app.password = {
    success: function(response){
        // Enable the submit button
        document.getElementById("saveChanges").disabled = false;
        
        let logoutFunction = function() {
            window.location = "./login.html";
        };
        let successData = {
            text: "Okay",
            callback: logoutFunction
        };
        new Modal("Password Change", MessageCode[response.messageCode], successData, false).show();
    },
    
    failure: function(response){
        // Enable the submit button
        document.getElementById("saveChanges").disabled = false;
        
        app.handleFailure(response);
    },
    
    submit: function(e){
        e.preventDefault();
    
        if(!app.password.validatePassword())
            return;
        
        let password = document.getElementById('newPassword').value;
        let code = getQueryParameterByName("q");
        if(!code)
            return;
        
        // Disable the submit button
        document.getElementById("saveChanges").disabled = true;
        
        Resources.Password.PUT(code, password, app.password.success, app.password.failure);
    },
    
    validatePassword: function() {
        let newPassword = document.getElementById('newPassword').value;
        let newPasswordConfirmation = document.getElementById('passwordConfirmation').value;
        // Check if the user submitted a new password and confirmed it
        if (!newPassword || !newPasswordConfirmation) {
            app.handleFailure({
                messageCode: "MissingArgumentsPasswords"
            });
            return false;
        }

        // Check if the new password matches the new password confirmation
        if (newPassword != newPasswordConfirmation) {
            app.handleFailure({
                messageCode: "PasswordsNoMatch"
            });

            return false;
        }
        
        if(!app.password.verifyUserPass(newPassword)){
            app.handleFailure({
                messageCode: "PasswordTooSmall"
            });
            return false;
        }
        return true;
    },
    verifyUserPass: function(password){
        return password && (password.length >= 9);
    },
};

app.startup.push(function passwordStartup() {
    document.getElementById('userData').addEventListener('submit', app.password.submit);
});
