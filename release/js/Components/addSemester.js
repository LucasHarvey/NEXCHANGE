/* global Resources,MessageCode,Modal */
var app = app || {
    startup: [],
    afterStartup: []
};

app.addSemester = {
    uploadInProgress: false,
    
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
        app.addSemester.uploadInProgress = false;

        // Empty the course input fields: 
        document.getElementById("file").value = "";
        document.getElementById("season").selectedIndex = app.addSemester.getDefaultSeason();
        document.getElementById("year").value = new Date().getFullYear();
        document.getElementById("newSemesterStart").value = "";
        document.getElementById("newSemesterEnd").value = "";
        document.getElementById("newMarchBreakStart").value = "";
        document.getElementById("newMarchBreakEnd").value = "";
        document.getElementById("newHideFields").checked = false;
        
        app.addSemester.updateFileLabel();
    },
    
    updateFileLabel: function(){
        var input = document.getElementById("file");
        var label = document.getElementById("fileLabel");
        
        if(!this.files || this.files.length == 0) {
            label.innerText = "Select File (.csv)";
            return;
        }
        
        label.innerText = this.files.length + " File".pluralize(this.files.length)+" Selected";
    },
    
    getDefaultSeason: function() {
        let month = new Date().getMonth();
        let today = new Date().getDate();
        if (month >= 11) return 1; //intersession
        if (month >= 0 && month < 5) return 2; //winter
        if (month >= 5 && month < 8) return 3; //summer
        return 0; //fall
    },
    
    submitSemesterSuccess: function(data) {
        
        // Enable the form
        document.getElementById('submit').disabled = false;
        document.getElementById('addSemester').addEventListener('submit', app.addSemester.submitSemester);
        app.addSemester.uploadInProgress = false;

        // Empty the course input fields: 
        app.addSemester.reset();
        
        new Modal("Courses Added", MessageCode("CoursesCreated") + "<br>" + data.payload.output, {
                    callback: function(){
                        window.location.reload();
                    },
                    text: "Okay"
                }, false).show();
    },
    
    submitSemesterFailure: function(data){
        
        // Enable the form
        document.getElementById('submit').disabled = false;
        document.getElementById('addSemester').addEventListener('submit', app.addSemester.submitSemester);
        app.addSemester.uploadInProgress = false;
        
        app.handleFailure(data);
    },
    
    submitSemester: function(event) {
        event.preventDefault();
        if (this.uploadInProgress) {
            console.warn("Courses are already being uploaded...");
            return;
        }

        let file = document.getElementById('file').files;
        if (file.length == 0) {
            app.handleFailure({ messageCode: "NoFilesUploaded" });
            return;
        }
        
        var newSemesterStart = document.getElementById("newSemesterStart").value;
        var newSemesterEnd = document.getElementById("newSemesterEnd").value;
        var newMarchBreakStart = "";
        var newMarchBreakEnd = "";
        
        if(newSemesterStart != ""){
            newSemesterStart = new Date(newSemesterStart.replace(/-/g, '\/').replace(/T.+/, ''));
        }

        if(newSemesterEnd != ""){
            newSemesterEnd = new Date(newSemesterEnd.replace(/-/g, '\/').replace(/T.+/, ''));
        }

        if(newSemesterEnd != "" && newSemesterStart == ""){
            new Modal("Error", MessageCode("MissingArgumentSemesterStart"), null, {
                text: "Okay"
            }).show();
            return;
        }
            
        if(newSemesterStart != "" && newSemesterEnd == ""){
            new Modal("Error", MessageCode("MissingArgumentSemesterEnd"), null, {
                text: "Okay"
            }).show();
            return;
        }
        
        if(newSemesterStart != "" && newSemesterEnd != "")
        if(newSemesterEnd <= newSemesterStart){
            new Modal("Error", MessageCode("SemesterDatesNotValid"), null, {
                text: "Okay"
            }).show();
            return;
        }
        
        var newMarchBreakEnabled = document.getElementById("newHideFields");

        if(newMarchBreakEnabled.checked){
            
            if(document.getElementById("newMarchBreakStart").value != ""){
                newMarchBreakStart = new Date(document.getElementById("newMarchBreakStart").value.replace(/-/g, '\/').replace(/T.+/, ''));
            } else {
                new Modal("Error", MessageCode("MissingArgumentMarchBreakStart"), null, {
                    text: "Okay"
                }).show();
                return;
            }

            if(document.getElementById("newMarchBreakEnd").value != ""){
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
        }
        
        if(newSemesterStart != "")
            newSemesterStart = app.dateFormatting.parseSubmissionDate(newSemesterStart);
            
        if(newSemesterEnd != "")
            newSemesterEnd = app.dateFormatting.parseSubmissionDate(newSemesterEnd);
            
        if(newMarchBreakStart != "")
            newMarchBreakStart = app.dateFormatting.parseSubmissionDate(newMarchBreakStart);
            
        if(newMarchBreakEnd != "")
            newMarchBreakEnd = app.dateFormatting.parseSubmissionDate(newMarchBreakEnd);

        let seasonSelector = document.getElementById("season");
        var season = seasonSelector.value;
        var year = document.getElementById("year").value;
        
        var semesterCode = app.editSemester.validateSemester(season,year);
        if(!semesterCode) return;
        
        new Modal("Create Semester",
                    "Are you sure you want to upload the selected file?" +
                    "<br>This <b>CANNOT</b> be undone." +
                    "<br>Confirm Admin password: <input type='password' placeholder='Password' autocomplete='false' id='addSemester_uploadSemesterPw'><p class='error'></p>", {
                        text: "Yes, UPLOAD Courses",
                        callback: function() {
                            this.confirmButton.disabled = true;
                            app.addSemester._uploadSemester.call(this, semesterCode, file, newSemesterStart, newSemesterEnd, newMarchBreakStart, newMarchBreakEnd);
                        }
                    }
                ).show();
    },
    
    validateSemester: function(season, year){
        
        let seasonSelector = document.getElementById("semesterSeason");
        let yearInput = document.getElementById("semesterYear");
        
        if (!year) {
            new Modal("Error", MessageCode("MissingArgumentYear"), null, {
                text: "Okay"
            }).show();
            return false;
        }

        if (!season) {
            new Modal("Error", MessageCode("MissingArgumentSeason"), null, {
                text: "Okay"
            }).show();
            return false;
        }

        if (isNaN(year) || year % 1 != 0 || year<0) {
            new Modal("Error", year + " is not a valid year.", null, {
                text: "Okay"
            }).show();
            return false;
        }

        if (!app.dateFormatting.semesterFormatVerification(season, year)) {
            new Modal("Error", seasonSelector.innerText + " " + year + " is not a valid semester.", null, {
                text: "Okay"
            }).show();
            return false;
        }
        
        return season + year;
    },
    
    _uploadSemester: function(semesterCode, file, newSemesterStart, newSemesterEnd, newMarchBreakStart, newMarchBreakEnd){
        
        var that = this;
        
        var pass = document.getElementById("addSemester_uploadSemesterPw").value;
        if (!pass) {
            document.getElementById("addSemester_uploadSemesterPw").nextSibling.innerHTML = "Please enter password.";
            that.confirmButton.disabled = false;
            return;
        }
        
        app.addSemester.uploadInProgress = true;
        
        //Disable the form
        document.getElementById('submit').disabled = true;
        document.getElementById('addSemester').removeEventListener('submit', app.addSemester.submitSemester);

        Resources.Semester.POST(semesterCode, file, newSemesterStart, newSemesterEnd, newMarchBreakStart, newMarchBreakEnd, pass, function(response){
            //Success function
            that.hide();
            app.addSemester.submitSemesterSuccess(response);
        }, function(response){
            //Failure function
            if(response.messageCode == "AuthenticationFailed"){
                
                // Enable the form
                document.getElementById('submit').disabled = false;
                document.getElementById('addSemester').addEventListener('submit', app.addSemester.submitSemester);
                
                that.confirmButton.disabled = false;
                
                app.addSemester.uploadInProgress = false;
                
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
    
    document.getElementById('addSemester').addEventListener('submit', app.addSemester.submitSemester);
    
    document.getElementById("year").value = new Date().getFullYear();
    document.getElementById("season").selectedIndex = app.addSemester.getDefaultSeason();
    
    // Change the file label when files are added
    document.getElementById("file").addEventListener("change", app.addSemester.updateFileLabel);
    app.addSemester.updateFileLabel();
});

