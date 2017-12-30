/* global Resources, MessageCode, Modal */
var app = app || {
    startup: [],
    afterStartup: []
};

app.signup = {

    signupSuccess: function(data) {
        var modalContent = "User has been created successfully.\n<p><span>Login ID: </span>" + data.payload.loginId + "</p>";
        modalContent += "\n<p><span>Name: </span>" + data.payload.firstName + " " + data.payload.lastName + "</p>";
        modalContent += "\n<p><span>Temporary Password: </span>" + data.payload.password + "</p>";

        let confirmButton = {
            text: "Grant User Access",
            callback: function() {
                app.store("userAccessLoginId", data.payload.loginId); //Prepopulate id field in user access
                window.location.href = "userAccess.html";
            }
        };
        new Modal("User Created", modalContent, confirmButton, {
            text: "Okay"
        }).show();
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

        if (!app.signup.verifyUserId(studentId)) {
            app.handleFailure({
                messageCode: "UserIdNotValid"
            });
            return;
        }
        if (!app.signup.verifyName(firstName + " " + lastName)) {
            app.handleFailure({
                messageCode: "UserNameNotValid"
            });
            return;
        }

        if (!app.signup.verifyEmail(email)) {
            app.handleFailure({
                messageCode: "EmailNotValid"
            });
            return;
        }

        Resources.Users.POST(firstName, lastName, studentId, email, app.signup.signupSuccess);
    },

    verifyEmail: function(email) {
        return email.validEmail();
    },
    verifyUserId: function(userId) {
        return (userId.length == 7 && !isNaN(userId));
    },
    verifyName: function(name) {
        return (/^[A-Za-z\-\s]+$/g.test(name));
    }
};

app.startup.push(function signupStartup() {
    document.getElementById('userData').addEventListener('submit', app.signup.submitSignup);
});
