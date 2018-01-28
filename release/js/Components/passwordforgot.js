/* global Resources, MessageCode, Modal,getQueryParameterByName */
var app = app || {
    startup: [],
    afterStartup: []
};

app.passwordForgot = {
    success: function(response){
        
        // Enable the submit button
        document.getElementById("sendRequest").disabled = false;
        document.getElementById('userData').addEventListener('submit', app.passwordForgot.submit);
        
        let logoutFunction = function() {
            window.location = "./login.html";
        };
        let successData = {
            text: "Okay",
            callback: logoutFunction
        };
        new Modal("Password Reset Requested", MessageCode[response.messageCode], successData, false).show();
    },
    
    failure: function(response){
        
        // Enable the submit button
        document.getElementById("sendRequest").disabled = false;
        document.getElementById('userData').addEventListener('submit', app.passwordForgot.submit);
        
        app.handleFailure(response);
    },
    submit: function(e){
        e.preventDefault();
        
        var studentId = document.getElementById("userid").value;
        if (!studentId) {
            app.handleFailure({
                messageCode: "MissingArgumentStudentId"
            });
            return;
        }
        if (!this.verifyUserId(studentId)) {
            app.handleFailure({
                messageCode: "UserIdNotValid"
            });
            return;
        }
        
        var email = document.getElementById("email").value;
        if(!email){
            app.handleFailure({
                messageCode: "MissingArgumentEmail"
            })
            return;
        }
        if (!this.verifyEmail(email)) {
            app.handleFailure({
                messageCode: "EmailNotValid"
            });
            return;
        }
        
        // Disable the form
        document.getElementById("sendRequest").disabled = true;
        document.getElementById('userData').removeEventListener('submit', app.passwordForgot.submit);
        
        Resources.Password.POST(studentId, email, this.success, this.failure);
    },
    verifyUserId: function(userId) {
        return (userId.length == 7 && !isNaN(userId) && userId);
    },
    verifyEmail: function(email) {
        return email.validEmail();
    }
};

app.startup.push(function passwordForgotStartup() {
    app.passwordForgot.submit = app.passwordForgot.submit.bind(app.passwordForgot);
    document.getElementById('userData').addEventListener('submit', app.passwordForgot.submit);
});
