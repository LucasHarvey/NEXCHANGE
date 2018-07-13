/* global  MessageCode,Modal */
var app = app || {
    startup: [],
    afterStartup: []
};

app.semesterController = {
    
    uploadInProgress: false,
    
    validateSemester: function(seasonSelector, yearInput, season, year){
        
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
    
    _getSearchType: function() {
        let doWhat = document.getElementsByName("doWhat");
        for (var i = 0; i < doWhat.length; i++) {
            if (doWhat[i].checked) {
                return doWhat[i].value;
            }
        }
        return "create";
    },
    
    toggleSearchFields: function() {
        let options = document.getElementsByName("doWhat");
        for (var i = 0; i < options.length; i++) {
            var inputRows = document.getElementsByClassName("doWhat_" + options[i].value);
            if (options[i].checked) {
                for (var x = 0; x < inputRows.length; x++) {
                    inputRows[x].style.display = "";
                }
                
                switch(options[i].value){
                    case "create": 
                        document.getElementById("submit").value = "Create Semester";
                        break;
                    case "edit": 
                        document.getElementById("submit").value = "Save Changes";
                        //POPULATE THE FIELDS
                        app.editSemester.updateFields();
                        break;
                    case "upload":
                        document.getElementById("submit").value = "Upload Courses";
                        break;
                }
                
                continue;
            }
            for (var x = 0; x < inputRows.length; x++) {
                inputRows[x].style.display = "none";
            }
        }
    },
    
    submitSemester: function(e){
        e.preventDefault();
        
        let doWhat = app.semesterController._getSearchType();
        switch (doWhat) {
            case "create":
                app.addSemester.submitSemester();
                break;
            case "edit":
                app.editSemester.editSemester();
                break;
            case "upload":
                app.addCourses.submitCourses();
                break;
        }
    }

    
};

app.startup.push(function semesterControllerStartup() {
    
    app.semesterController.submitSemester = app.semesterController.submitSemester.bind(app.semesterController);
    
    document.getElementById("semesterData").addEventListener('submit', app.semesterController.submitSemester);
    
    let doWhat = document.getElementsByName("doWhat");
    for (var i = 0; i < doWhat.length; i++) {
        doWhat[i].addEventListener('change', app.semesterController.toggleSearchFields);
    }
    app.semesterController.toggleSearchFields();

});