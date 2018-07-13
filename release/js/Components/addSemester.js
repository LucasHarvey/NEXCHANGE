/* global Resources,MessageCode,Modal,datePolyFillStart */
var app = app || {
    startup: [],
    afterStartup: []
};

app.addSemester = {
    
    toggleMarchBreak: function(){
        let checkbox = document.getElementById("newHideFields");
        let newMarchBreakFields = document.getElementById("newMarchBreakFields");
        if(checkbox.checked){
            newMarchBreakFields.style.display = "block";
        } else {
            newMarchBreakFields.style.display = "none";
        }
    },

    reset: function() {
        app.semesterController.uploadInProgress = false;

        // Empty the course input fields: 
        document.getElementById("newFile").value = "";
        document.getElementById("newSeason").selectedIndex = app.addSemester.getDefaultSeason();
        document.getElementById("newYear").value = new Date().getFullYear();
        document.getElementById("newSemesterStart").value = "";
        document.getElementById("newSemesterEnd").value = "";
        document.getElementById("newMarchBreakStart").value = "";
        document.getElementById("newMarchBreakEnd").value = "";
        document.getElementById("newHideFields").checked = false;
        app.addSemester.toggleMarchBreak();
        app.addSemester.updateFileLabel();
    },
    
    updateFileLabel: function(){
        var input = document.getElementById("newFile");
        var label = document.getElementById("newFileLabel");
        
        if(!this.files || this.files.length == 0) {
            label.innerText = "Select File (.csv)";
            return;
        }
        
        label.innerText = this.files.length + " File".pluralize(this.files.length)+" Selected";
    },
    
    getDefaultSeason: function() {
        let month = new Date().getMonth();
        if (month >= 10) return 1; //intersession
        if (month >= 0 && month < 3) return 2; //winter
        if (month >= 3 && month < 7) return 3; //summer
        return 0; //fall
    },
    
    submitSemesterSuccess: function(data) {
        
        // Enable the form
        document.getElementById('submit').disabled = false;
        document.getElementById("semesterData").addEventListener('submit', app.semesterController.submitSemester);
        app.semesterController.uploadInProgress = false;

        // Empty the course input fields: 
        app.addSemester.reset();
        
        new Modal("Courses Added", MessageCode("CoursesCreated") + "<br>" + data.payload.output, null, null, "Okay").show();
    },
    
    submitSemesterFailure: function(data){
        
        // Enable the form
        document.getElementById('submit').disabled = false;
        document.getElementById("semesterData").addEventListener('submit', app.semesterController.submitSemester);
        app.semesterController.uploadInProgress = false;
        
        app.handleFailure(data);
    },
    
    submitSemester: function() {
        if (app.semesterController.uploadInProgress) {
            console.warn("Courses are already being uploaded...");
            return;
        }

        let file = document.getElementById('newFile').files;
        if (file.length == 0) {
            app.handleFailure({ messageCode: "NoCourseFilesUploaded" });
            return;
        }
        
        var newSemesterStart = document.getElementById("newSemesterStart").value;
        var newSemesterEnd = document.getElementById("newSemesterEnd").value;
        var newMarchBreakStart = document.getElementById("newMarchBreakStart").value;
        var newMarchBreakEnd = document.getElementById("newMarchBreakEnd").value;
        
        if(newSemesterStart == ""){
            new Modal("Error", MessageCode("MissingArgumentSemesterStart"), null, {
                text: "Okay"
            }).show();
            return;
        }
        
        if(newSemesterEnd == ""){
            new Modal("Error", MessageCode("MissingArgumentSemesterEnd"), null, {
                text: "Okay"
            }).show();
            return;
        }
            
        newSemesterStart = new Date(newSemesterStart.replace(/-/g, '\/').replace(/T.+/, ''));
        newSemesterEnd = new Date(newSemesterEnd.replace(/-/g, '\/').replace(/T.+/, ''));
        
        if(newSemesterEnd <= newSemesterStart){
            new Modal("Error", MessageCode("SemesterDatesNotValid"), null, {
                text: "Okay"
            }).show();
            return;
        }
        
        var newMarchBreakEnabled = document.getElementById("newHideFields");

        if(newMarchBreakEnabled.checked){
            
            if(newMarchBreakStart != ""){
                newMarchBreakStart = new Date(document.getElementById("newMarchBreakStart").value.replace(/-/g, '\/').replace(/T.+/, ''));
            } else {
                new Modal("Error", MessageCode("MissingArgumentMarchBreakStart"), null, {
                    text: "Okay"
                }).show();
                return;
            }

            if(newMarchBreakEnd != ""){
                newMarchBreakEnd = new Date(document.getElementById("newMarchBreakEnd").value.replace(/-/g, '\/').replace(/T.+/, ''));
            } else {
                new Modal("Error", MessageCode("MissingArgumentMarchBreakEnd"), null, {
                    text: "Okay"
                }).show();
                return;
            }
            
            if(newMarchBreakEnd <= newMarchBreakStart){
                new Modal("Error", MessageCode("MarchBreakNotValid"), null, {
                    text: "Okay"
                }).show();
                return;
            }
            
            if(newMarchBreakStart < newSemesterStart){
                new Modal("Error", MessageCode("MarchBreakStartNotValid"), null, {
                text: "Okay"
                }).show();
                return;
            }
            
            if(newMarchBreakEnd > newSemesterEnd){
                new Modal("Error", MessageCode("MarchBreakEndNotValid"), null, {
                text: "Okay"
                }).show();
                return;
            }
        }
        
        newSemesterStart = app.dateFormatting.parseSubmissionDate(newSemesterStart);
        newSemesterEnd = app.dateFormatting.parseSubmissionDate(newSemesterEnd);
            
        if(newMarchBreakStart != "")
            newMarchBreakStart = app.dateFormatting.parseSubmissionDate(newMarchBreakStart);
            
        if(newMarchBreakEnd != "")
            newMarchBreakEnd = app.dateFormatting.parseSubmissionDate(newMarchBreakEnd);

        let seasonSelector = document.getElementById("newSeason");
        var season = seasonSelector.value;
        let yearInput = document.getElementById("newYear");
        var year = yearInput.value;
        
        var semesterCode = app.semesterController.validateSemester(seasonSelector, yearInput, season,year);
        if(!semesterCode) return;
        
        new Modal("Create Semester",
                    "Are you sure you want to upload the selected file?" +
                    "<br>This <b>CANNOT</b> be undone." +
                    "<br>Confirm Admin password: <input type='password' placeholder='Password' autocomplete='false' id='addSemester_uploadSemesterPw'><p class='error'></p>", {
                        text: "Yes, CREATE Semester",
                        callback: function() {
                            this.confirmButton.disabled = true;
                            app.addSemester._uploadSemester.call(this, semesterCode, file, newSemesterStart, newSemesterEnd, newMarchBreakStart, newMarchBreakEnd);
                        }
                    }
                ).show();
    },
    
    _uploadSemester: function(semesterCode, file, newSemesterStart, newSemesterEnd, newMarchBreakStart, newMarchBreakEnd){
        
        var that = this;
        
        var pass = document.getElementById("addSemester_uploadSemesterPw").value;
        if (!pass) {
            document.getElementById("addSemester_uploadSemesterPw").nextSibling.innerHTML = "Please enter password.";
            that.confirmButton.disabled = false;
            return;
        }
        
        app.semesterController.uploadInProgress = true;
        
        //Disable the form
        document.getElementById('submit').disabled = true;
        document.getElementById("semesterData").removeEventListener('submit', app.semesterController.submitSemester);

        Resources.Semester.POST(semesterCode, file, newSemesterStart, newSemesterEnd, newMarchBreakStart, newMarchBreakEnd, pass, function(response){
            //Success function
            that.hide();
            app.addSemester.submitSemesterSuccess(response);
        }, function(response){
            //Failure function
            if(response.messageCode == "AuthenticationFailed"){
                
                // Enable the form
                document.getElementById('submit').disabled = false;
                document.getElementById("semesterData").addEventListener('submit', app.semesterController.submitSemester);
                
                that.confirmButton.disabled = false;
                
                app.semesterController.uploadInProgress = false;
                
                document.getElementById("addSemester_uploadSemesterPw").nextSibling.innerHTML = "Incorrect Password.";
                return;
            }
            that.hide();
            app.addSemester.submitSemesterFailure(response);
            
        });
    }
};

app.startup.push(function addSemesterStartup() {
    app.addSemester.submitSemester = app.addSemester.submitSemester.bind(app.addSemester);
    
    document.getElementById("newHideFields").addEventListener("change", app.addSemester.toggleMarchBreak);
    
    datePolyFillStart();
    
    document.getElementById("newYear").value = new Date().getFullYear();
    document.getElementById("newSeason").selectedIndex = app.addSemester.getDefaultSeason();
    
    // Change the file label when files are added
    document.getElementById("newFile").addEventListener("change", app.addSemester.updateFileLabel);
    app.addSemester.updateFileLabel();
});

