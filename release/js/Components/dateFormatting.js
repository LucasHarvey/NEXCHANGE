/* global Resources,MessageCode,Modal */
var app = app || {
    startup: [],
    afterStartup: []
};

app.dateFormatting = {
    
    // Format the semester code into a readable semester
    formatSemester: function(semesterCode){
        let season = semesterCode[0];
        let year = semesterCode.substring(1);
        var prettySeason= "";
        
        switch (season) {
            case "F": 
                prettySeason = "Fall";
            break;
            case "W": 
                prettySeason = "Winter";
            break;
            case "I": 
                prettySeason = "Intersession";
            break;
            default: 
                prettySeason = "Summer";
        }
        
        return prettySeason + " " + year;
    },
    
    isPastDate: function(date) {
        return (date <= new Date());
    },
    
    parseSubmissionDate: function(date) {
        if (date) {
            let year = date.getFullYear();
            let month = date.getMonth() + 1;
            let day = date.getDate();
            return year + "-" + month + "-" + day;
        }
        return undefined;
    },
    
    semesterFormatVerification: function(season, year) {
        let semesters = ["F", "W", "I", "S"];
        if (!semesters.includes(season)) {
            return false;
        }

        return true;
    },
    
    formatExpiryDate: function(season, year) {

        if (season == "I") return (parseInt(year) + 1) + "-" + 1 + "-" + 31;


        if (season == "W") return year + "-" + 5 + "-" + 31;


        if (season == "S") return year + "-" + 8 + "-" + 31;

        //default expiry is fall semester
        return year + "-" + 12 + "-" + 31;

    },
    
    validateExpiryDate: function(date) {
        let dateComponents = date.split("-");

        var now = new Date;
        if (dateComponents[0] < now.getFullYear()) {
            return false;
        } else if (dateComponents[0] == now.getFullYear()) {
            if (dateComponents[1] < now.getMonth() + 1) {
                return false
            } else if (dateComponents[1] == now.getMonth() + 1) {
                if (dateComponents[2] < now.getDate()) {
                    return false
                }
            }
        }
        return true;
    },
    
    isPastSemester: function(season, year) {
        // false means that the semester is in the future 
        var thisYear = new Date().getFullYear();
        var thisMonth = new Date().getMonth() + 1;
        //should we add a check for older than 2000? if so, we need to change the messageCode
        if (year > thisYear) {
            return false;
        }
        if (year == thisYear) {
            // Don't need to check for winter semester because month can't be smaller than 1
            if ((season == "S" && thisMonth < 5) || (season == "F" && thisMonth < 7) || (season == "I" && thisMonth < 12)) {
                return false;
            }
        }

        return true;
    }
};