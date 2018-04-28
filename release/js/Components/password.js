/* global Resources, MessageCode, Modal,getQueryParameterByName,location */
var app = app || {
    startup: [],
    afterStartup: []
};

app.password = {
    success: function(response){
       // Enable the form
        document.getElementById("saveChanges").disabled = false;
        document.getElementById('userData').addEventListener('submit', app.password.submit);
        
        
        let logoutFunction = function() {
            location.assign("./login");
        };
        let successData = {
            text: "Okay",
            callback: logoutFunction
        };
        new Modal("Password Changed", MessageCode(response.payload.messageCode), successData, false).show();
    },
    
    failure: function(response){
        // Enable the form
        document.getElementById("saveChanges").disabled = false;
        document.getElementById('userData').addEventListener('submit', app.password.submit);
        
        app.handleFailure(response);
    },
    
    submit: function(e){
        e.preventDefault();
    
        if(!this.validatePassword())
            return;
        
        let password = document.getElementById('newPassword').value;
        let code = getQueryParameterByName("q");
        if(!code)
            return;
        
        // Disable the form
        document.getElementById("saveChanges").disabled = true;
        document.getElementById('userData').removeEventListener('submit', app.password.submit);
        
        Resources.Password.PUT(code, password, this.success, this.failure);
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
    app.password.submit = app.password.submit.bind(app.password);
    document.getElementById('userData').addEventListener('submit', app.password.submit);
});
