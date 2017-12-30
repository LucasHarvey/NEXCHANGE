/* global Resources, MessageCode, Modal,getQueryParameterByName */
var app = app || {
    startup: [],
    afterStartup: []
};

app.passwordForgot = {
    success: function(response){
        
        // Enable the submit button
        document.getElementById("sendRequest").disabled = false;
        document.getElementById('userData').addEventListener('submit', app.password.submit.bind(app.passwordForgot));
        
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
        document.getElementById('userData').addEventListener('submit', app.password.submit.bind(app.passwordForgot));
        
        app.handleFailure(response);
    },
    submit: function(e){
        e.preventDefault();
        
        var studentId = document.getElementById("userid");
        var email = document.getElementById("email");
        
        // Disable the form
        document.getElementById("sendRequest").disabled = true;
        document.getElementById('userData').removeEventListener('submit', app.password.submit.bind(app.passwordForgot));
        
        Resources.Password.POST(studentId, email, this.success, this.failure);
    }
};

app.startup.push(function passwordForgotStartup() {
    document.getElementById('userData').addEventListener('submit', app.passwordForgot.submit.bind(app.passwordForgot));
});
