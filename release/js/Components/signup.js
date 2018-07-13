/* global Resources,MessageCode,Modal,location */
var app = app || {
    startup: [],
    afterStartup: []
};

app.signup = {

    signupSuccess: function(data) {
        
        // Enable the form
        document.getElementById("submit").disabled = false;
        document.getElementById('userData').addEventListener('submit', app.signup.submitSignup);
        
        // Clear the form
        document.getElementById('email').value = "";
        document.getElementById('firstName').value = "";
        document.getElementById('lastName').value = "";
        document.getElementById('studentId').value = "";
        
        var modalContent = "User has been created successfully.\n<p><span>Login ID: </span>" + data.payload.loginId + "</p>";
        modalContent += "\n<p><span>Name: </span>" + data.payload.firstName + " " + data.payload.lastName + "</p>";
        modalContent += "\n<p><span>Temporary Password: </span>" + data.payload.password + "</p>";
        modalContent += "\n<p><span>Email Sent: </span>" + (data.payload.emailSent ? "Successfully" : "Unsuccessfully") + "</p>";

        let confirmButton = {
            text: "Grant User Access",
            callback: function() {
                app.store("userAccessLoginId", data.payload.loginId); //Prepopulate id field in user access
                location.assign("./userAccess");
            }
        };
        new Modal("User Created", modalContent, confirmButton, {
            text: "Okay"
        }).show();
    },
    
    signupFailure: function(response){
        // Enable the form
        document.getElementById("submit").disabled = false;
        document.getElementById('userData').addEventListener('submit', app.signup.submitSignup);
        
        app.handleFailure(response);
    },

    submitSignup: function(event) {
        event.preventDefault();

        let email = document.getElementById('email').value;
        let firstName = document.getElementById('firstName').value;
        let lastName = document.getElementById('lastName').value;
        let studentId = document.getElementById('studentId').value;

        if (!studentId) {
            app.handleFailure({
                messageCode: "MissingArgumentStudentId"
            });
            return;
        }


        if (!firstName) {
            app.handleFailure({
                messageCode: "MissingArgumentFirstName"
            });
            return;
        }
        if (!lastName) {
            app.handleFailure({
                messageCode: "MissingArgumentLastName"
            });
            return;
        }
        
        if(!email){
            app.handleFailure({
                messageCode: "MissingArgumentEmail"
            })
            return;
        }

        if (!this.verifyUserId(studentId)) {
            app.handleFailure({
                messageCode: "UserIdNotValid"
            });
            return;
        }
        if (!this.verifyName(firstName + " " + lastName)) {
            app.handleFailure({
                messageCode: "UserNameNotValid"
            });
            return;
        }

        if (!this.verifyEmail(email)) {
            app.handleFailure({
                messageCode: "EmailNotValid"
            });
            return;
        }
        
        // Disable the form
        document.getElementById("submit").disabled = true;
        document.getElementById('userData').removeEventListener('submit', app.signup.submitSignup);

        Resources.Users.POST(firstName, lastName, studentId, email, this.signupSuccess, this.signupFailure);
    },

    verifyEmail: function(email) {
        return email.validEmail();
    },
    verifyUserId: function(userId) {
        return (userId.length == 7 && !isNaN(userId));
    },
    verifyName: function(name) {
        return (/^[A-Za-z'\-\s]+$/g.test(name));
    }
};

app.startup.push(function signupStartup() {
    app.signup.submitSignup = app.signup.submitSignup.bind(app.signup);
    document.getElementById('userData').addEventListener('submit', app.signup.submitSignup);
});
