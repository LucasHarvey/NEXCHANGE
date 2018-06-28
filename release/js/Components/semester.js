/* global Resources,MessageCode,Modal */

var app = app || {
    startup: [],
    afterStartup: []
};

app.semester = {
    originalDates: undefined,
    toggleMarchBreak: function(){
        let checkbox = document.getElementById("hideFields");
        let marchBreakFields = document.getElementById("marchBreakFields");
        if(checkbox.checked){
            marchBreakFields.style.display = "block";
        } else {
            marchBreakFields.style.display = "none";
        }
    },
    
    submitDatesSuccess: function(response) {
        // Enable the form
        document.getElementById('semesterDates').addEventListener('submit', app.semester.submitDates);
        document.getElementById("submitDates").disabled = false;
        
        new Modal("Semester Dates Updated", MessageCode(response.payload.messageCode), {
            text: "Okay",
            callback: function() {
                window.location.reload();
            }
        }, false).show();
        
    },
    
    submitDates: function(event){
        event.preventDefault();
    
        var year = document.getElementById("semesterYear").value;
        var season = document.getElementById("semesterSeason").value;
        
        var semesterCode = app.semester.validateSemester(season,year);
        if(!semesterCode) return;
        
        var oldSemesterStart = null;
        var oldSemesterEnd = null;
        var oldMarchBreakStart = null;
        var oldMarchBreakEnd = null;
        
        if(this.originalDates.semesterStart)
            oldSemesterStart = new Date(this.originalDates.semesterStart.replace(/-/g, '\/').replace(/T.+/, ''));
        if(this.originalDates.semesterEnd)
            oldSemesterEnd = new Date(this.originalDates.semesterEnd.replace(/-/g, '\/').replace(/T.+/, ''));
        if(this.originalDates.marchBreakStart)
            oldMarchBreakStart = new Date(this.originalDates.marchBreakStart.replace(/-/g, '\/').replace(/T.+/, ''));
        if(this.originalDates.marchBreakEnd)
            oldMarchBreakEnd = new Date(this.originalDates.marchBreakEnd.replace(/-/g, '\/').replace(/T.+/, ''));
        
        var semesterStart = document.getElementById("semesterStart").value;
        var semesterEnd = document.getElementById("semesterEnd").value;
        var marchBreakStart = null;
        var marchBreakEnd = null;
        
        if(semesterStart == "") semesterStart = null;
        if(semesterEnd == "") semesterEnd = null;
        
        if(semesterStart != null)
            semesterStart = new Date(semesterStart.replace(/-/g, '\/').replace(/T.+/, ''));
        if(semesterEnd != null)
            semesterEnd = new Date(semesterEnd.replace(/-/g, '\/').replace(/T.+/, ''));
            
        if(semesterStart != null && semesterEnd != null){
            if(semesterEnd <= semesterStart){
                new Modal("Error", MessageCode("SemesterDatesNotValid"), null, {
                    text: "Okay"
                }).show();
                return;
            }
        }
        
        var marchBreakEnabled = document.getElementById("hideFields");

        if(marchBreakEnabled.checked){
            if(document.getElementById("marchBreakStart").value){
                marchBreakStart = new Date(document.getElementById("marchBreakStart").value.replace(/-/g, '\/').replace(/T.+/, ''));
            } else {
                new Modal("Error", MessageCode("MissingArgumentMarchBreakStart"), null, {
                    text: "Okay"
                }).show();
                return;
            }

            if(document.getElementById("marchBreakEnd").value){
                marchBreakEnd = new Date(document.getElementById("marchBreakEnd").value.replace(/-/g, '\/').replace(/T.+/, ''));
            } else {
                new Modal("Error", MessageCode("MissingArgumentMarchBreakEnd"), null, {
                    text: "Okay"
                }).show();
                return;
            }
            
            if(marchBreakEnd <= marchBreakStart){
                new Modal("Error", MessageCode("MarchBreakNotValid"), null, {
                    text: "Okay"
                }).show();
                return;
            }
        }
        
        var changes = {
            semesterStart: undefined,
            semesterEnd: undefined,
            marchBreakStart: undefined,
            marchBreakEnd: undefined
        };
        
        // SEMESTER START VALIDATION
        
        if(semesterStart != null){
            if(oldSemesterStart == null){
                changes.semesterStart = app.dateFormatting.parseSubmissionDate(semesterStart);
            } else {
                if (semesterStart.getFullYear() - oldSemesterStart.getFullYear() != 0 ||
                semesterStart.getMonth() - oldSemesterStart.getMonth() != 0 ||
                semesterStart.getDate() - oldSemesterStart.getDate() != 0){
                    changes.semesterStart = app.dateFormatting.parseSubmissionDate(semesterStart);
                }
            }
        }
        
        if(semesterStart == null && oldSemesterStart != null){
            changes.semesterStart = null;
        }
        
        // SEMESTER END VALIDATION
        
        if(semesterEnd != null){
            if(oldSemesterEnd == null){
                changes.semesterEnd = app.dateFormatting.parseSubmissionDate(semesterEnd);
            } else {
                if (semesterEnd.getFullYear() - oldSemesterEnd.getFullYear() != 0 ||
                semesterEnd.getMonth() - oldSemesterEnd.getMonth() != 0 ||
                semesterEnd.getDate() - oldSemesterEnd.getDate() != 0){
                    changes.semesterEnd = app.dateFormatting.parseSubmissionDate(semesterEnd);
                }
            }
        }

        if(semesterEnd == null && oldSemesterEnd != null){
            changes.semesterEnd = null;
        }
        
        // MARCH BREAK START VALIDATION
        
        if(marchBreakStart != null){
            if(oldMarchBreakStart == null){
                changes.marchBreakStart = app.dateFormatting.parseSubmissionDate(marchBreakStart);
            } else {
                if (marchBreakStart.getFullYear() - oldMarchBreakStart.getFullYear() != 0 ||
                marchBreakStart.getMonth() - oldMarchBreakStart.getMonth() != 0 ||
                marchBreakStart.getDate() - oldMarchBreakStart.getDate() != 0){
                    changes.marchBreakStart = app.dateFormatting.parseSubmissionDate(marchBreakStart);
                }
            }
        }
        
        if(marchBreakStart == null && oldMarchBreakStart != null){
            changes.marchBreakStart = null;
        }
        
        // MARCH BREAK END VALIDATION
        
        if(marchBreakEnd != null){
            if(oldMarchBreakEnd == null){
                changes.marchBreakEnd = app.dateFormatting.parseSubmissionDate(marchBreakEnd);
            } else {
                if (marchBreakEnd.getFullYear() - oldMarchBreakEnd.getFullYear() != 0 ||
                marchBreakEnd.getMonth() - oldMarchBreakEnd.getMonth() != 0 ||
                marchBreakEnd.getDate() - oldMarchBreakEnd.getDate() != 0){
                    changes.marchBreakEnd = app.dateFormatting.parseSubmissionDate(marchBreakEnd);
                }
            }
        }
        
        if(marchBreakEnd == null && oldMarchBreakEnd != null){
            changes.marchBreakEnd = null;
        }
        
        if(changes.semesterStart !== undefined || changes.semesterEnd !== undefined || changes.marchBreakStart !== undefined || changes.marchBreakEnd !== undefined){
            // Disable the form
            document.getElementById('semesterDates').removeEventListener('submit', app.semester.submitDates);
            document.getElementById("submitDates").disabled = true;
            
            Resources.Semester.POST(semesterCode, changes.semesterStart, changes.semesterEnd, changes.marchBreakStart, changes.marchBreakEnd, app.semester.submitDatesSuccess);
        } else {
            new Modal("No Changes", MessageCode("NoChangesToMake"), null, null, "Okay").show();
        }
    },
    
    populateDates: function(response){
        
        // Enable the form
        document.getElementById('semesterDates').addEventListener('submit', app.semester.submitDates);
        document.getElementById("submitDates").disabled = false;
        
        let data = response.payload;
        
        this.originalDates = data;
        
        var semesterStartField = document.getElementById("semesterStart");
        var semesterEndField = document.getElementById("semesterEnd");
        var marchBreakStartField = document.getElementById("marchBreakStart");
        var marchBreakEndField = document.getElementById("marchBreakEnd");
        var marchBreakEnabled = document.getElementById("hideFields");
        
        semesterStartField.value = data.semesterStart;
        semesterEndField.value = data.semesterEnd;
        marchBreakStartField.value = data.marchBreakStart;
        marchBreakEndField.value = data.marchBreakEnd;
    
        if(data.marchBreakStart || data.marchBreakEnd){
            marchBreakEnabled.checked = true;
        } else {
            marchBreakEnabled.checked = false;
        }
        
        app.semester.toggleMarchBreak();
        
    },
    
    getDefaultSeason: function() {
        let month = new Date().getMonth();
        let today = new Date().getDate();
        if (month >= 11 || (month == 0 && today < 15)) return 1; //intersession
        if (month >= 0 && month < 5) return 2; //winter
        if (month >= 5 && month < 8) return 3; //summer
        return 1; //fall
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
    
    updateFields: function(){
        // Disable the form
        document.getElementById('semesterDates').removeEventListener('submit', app.semester.submitDates);
        document.getElementById("submitDates").disabled = true;
            
        var year = document.getElementById("semesterYear").value;
        var season = document.getElementById("semesterSeason").value;
        
        var semesterCode = app.semester.validateSemester(season,year);
        if(!semesterCode){
            // Enable the form
            document.getElementById('semesterDates').addEventListener('submit', app.semester.submitDates);
            document.getElementById("submitDates").disabled = false;
            return;
        }
    
        Resources.Semester.GET(semesterCode, app.semester.populateDates);
    }
}

app.startup.push(function semesterStartup() {
    app.semester.submitDates = app.semester.submitDates.bind(app.submitDates);
    
    document.getElementById("hideFields").addEventListener("change", app.semester.toggleMarchBreak);
    document.getElementById("semesterDates").addEventListener("submit", app.semester.submitDates);
    
    document.getElementById("semesterSeason").selectedIndex = app.semester.getDefaultSeason();
    document.getElementById("semesterYear").value = new Date().getFullYear();
    
    document.getElementById("semesterSeason").addEventListener("change", app.semester.updateFields);
    document.getElementById("semesterYear").addEventListener("change", app.semester.updateFields);
    
});


app.afterStartup.push(function semesterAfterStartup() {
    //POPULATE THE FIELDS
    app.semester.updateFields();
});


