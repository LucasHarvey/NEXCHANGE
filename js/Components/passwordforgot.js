/* global Resources, MessageCode, Modal,getQueryParameterByName */
var app = app || {
    startup: [],
    afterStartup: []
};

app.passwordForgot = {
    success: function(response){
        
        // Enable the submit button
        document.getElementById("sendRequest").disabled = false;
        
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
        
        app.handleFailure(response);
    },
    submit: function(e){
        e.preventDefault();
        
        var studentId = document.getElementById("userid");
        var email = document.getElementById("email");
        
        // Disable the submit button
        document.getElementById("sendRequest").disabled = true;
        
        Resources.Password.POST(studentId, email, app.passwordForgot.success, app.passwordForgot.failure);
    }
};

app.startup.push(function passwordForgotStartup() {
    document.getElementById('userData').addEventListener('submit', app.password.submit);
});
