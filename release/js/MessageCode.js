function MessageCode(code){
    let MessageCode_Codes = {
        MessageCodeNotFound: "The message code translation was not found. Contact the Access Centre if this problem persists.",
        UnknownResourceMethod: "The request method doesn't exist on the server. Contact the Access Centre if this problem persists.",
        UnknownServerError: "A server error occured. Please contact the Access Centre if this problem persists.",
        InternalServerError: "An unknown server error occurred. Please contact the Access Centre if this problem persists.",
        JSONParseException: "Unable to parse a response from the server. Please contact the Access Centre if this problem persists.",
        MalformedBody: "The request body was not formed properly. Contact the Access Centre if this problem persists.",
        RequestTimedout: "A request took too long to process and has timed out. Please ensure that you have internet connectivity.",
        NoInternet: "A request could not be sent. Please ensure that you have internet connectivity.",
        PopUpBlocked: "A new window was blocked. Please ensure that pop-ups are allowed in your browser settings.",
        UnknownFileUploadError: "An error occured while uploading a file. Contact the Access Centre if this problem persists.",
        OneFileAllowed: "Only one file per upload is allowed.",
        StartupError: "An error occurred during startup of a component.",
        RoleNotFound: "Role was not found.",
        UserAccessNotFound: "The user access was not found.",
        UserNotFound: "User was not found.",
        NoteNotFound: "Note was not found.",
        NoteFileNotFound: "Note file was not found.",
        CourseNotFound: "The course was not found.",
        CourseSearchEmpty: "No course was found.",
        StudentNotFound: "Student was not found.",
        NoChangesToMake: "No changes were made. Make sure you modified a field.",
        KeyNotFound: "Malformed request body.",
    
        UserUpdated: "Your profile has been updated successfully.",
        PasswordUpdateFailure: 'Your password could not be updated. Please change it in the "My Profile" page.',
        PasswordUpdateLinkFailure: "Your password could not be updated. The reset link may be expired or may have already been used.",
        PasswordResetFailed: "The student ID and email do not match what we have in our databases.",
        PasswordResetRequested: "If the email entered matches the email on record, you will receive an email with the steps to reset your password.",
        PasswordReset: "Password has been reset successfully.",
        PasswordTooSmall: "Password must be 9 characters or more.",
        UserDeleted: "User deleted.",
        NoteDeleted: "Note has been deleted.",
        NoteUpdated: "Note has been updated.",
        UserAccessDeleted: "User access deleted.",
        UserAccessUpdated: "Notifications toggled.",
        UserAccessNotUpdated: "The user was not granted access to any courses.",
        NoNoteFilesUploaded: "You must upload file(s) when creating notes.",
        TooManyFiles: "You cannot upload more than 20 files at once for a single note.",
        NoCourseFilesUploaded: "You must upload a .csv file to upload courses.",
        UserCreateNotesDenied: "You have not been granted rights to upload notes for this course.",
        UserDownloadNotesDenied: "You have not been granted rights to download notes for this course.",
        UserAlreadyRegisteredInCourse: "This user is already signed up to be a notetaker or a student in this course.",
        SemesterSettingsUpdated: "The semester settings have been updated successfully.",
        SemesterUpdateFailedDNE: 'The semester dates could not be updated because the semester has not been created yet. To create the semester, select "Create New Semester".',
        AdditionalCoursesFailedDNE: 'The new courses could not be uploaded because the semester has not been created yet. To create the semester, select "Create New Semester".',
        SemesterFetchFailedDNE: 'The semester dates could not be loaded because the semester has not been created yet. To create the semester, select "Create New Semester".',
        SemesterExists: 'The semester already exists. To change the semester dates, select "Edit Semester". To upload more courses, select "Upload Additional Courses".',
        SemesterOutdated: "There already exists a more recent semester.",
    
        MissingArgument: "An argument is missing from the request. Make sure all fields were entered properly.",
        MissingArgumentFirstName: "First name was left empty.",
        MissingArgumentLastName: "Last name was left empty.",
        MissingArgumentPassword: "Password was left empty.",
        MissingArgumentPasswords: "Password(s) left empty.",
        MissingArgumentStudentId: "Student ID was left empty.",
        MissingArgumentNoteName: "Note name was left empty.",
        MissingArgumentTakenOn: "The date the notes were take on was left empty.",
        MissingArgumentCourseName: "Course name was left empty.",
        MissingArgumentCourseNumber: "Course Code was left empty.",
        MissingArgumentSection: "Section was left empty.",
        MissingArgumentTeacher: "Teacher name was left empty.",
        MissingArgumentCourseId: "Course ID was left empty.",
        MissingArgumentCourse: "Please select a course.",
        MissingArgumentRole: "Please select a role.",
        MissingArgumentUserId: "User ID was left empty.",
        MissingArgumentNoteId: "Note ID is missing from the request. Contact the Access Centre if this problem persists.",
        MissingArgumentTimestamp: "Timestamp was left empty, contact the Access Centre if this problem persists.",
        MissingArgumentNotifications: "You must specify if you want notifications.",
        MissingSortingMethod: "No sorting method was selected.",
        MissingArgumentCourses: "Please select at least one course.",
        MissingArgumentYear: "Please enter a year.",
        MissingArgumentYearCourseEdited: "Please select a year.",
        MissingArgumentSeason: "Please select a season.",
        MissingArgumentYearExpiry: "Please select an expiry year.",
        MissingArgumentSeasonExpiry: "Please select an expiry season.",
        MissingArgumentSemesterStart: "Please select the semester start date.",
        MissingArgumentSemesterEnd: "Please select the semester end date.",
        MissingArgumentMarchBreakStart: "Please select a start date for the March break.",
        MissingArgumentMarchBreakEnd: "Please select an end date for the March break.",
        MissingArgumentSemesterCode: "Please enter a valid semester",
    
        NoteNameNotValid: "Note name is too long: maximum 60 characters.",
        DescriptionNotValid: "Description is too long: maximum 500 characters.",
        DateNotValid: "The selected date is not valid.",
        CourseIdNotValid: "The course ID is not valid.",
        ExpiryDateNotValid: "The expiry date is not valid.",
        MissingArgumentExpiryDate: "Please select an expiry date.",
        UserIdNotValid: "User ID must be exactly 7 digits.",
        UserNameNotValid: "The student name is not valid.",
        FutureSemester: "Please choose a past or current semester.",
        PastSemester: "Please choose a future or current semester.",
        SemesterNotValid: "The semester you entered is not valid.",
        SemesterStartNotValid: "The semester start date is not valid.",
        SemesterEndNotValid: "The semester end date is not valid.",
        SemesterDatesNotValid: "The semester end date must be after the start date.",
        MarchBreakNotValid: "The March break end date must be after the start date.",
        MarchBreakStartNotValid: "The March break cannot start before the semester starts.",
        MarchBreakEndNotValid: "The March break cannot end after the semester ends.",
        MarchBreakStartFormatNotValid: "The March break start date is not valid.",
        MarchBreakEndFormatNotValid: "The March break end date is not valid.",
        NotificationNotValid: "Notifications can only be set to on or off.",
        FirstNameNotValid: "The first name is too long: maximum 40 characters.",
        LastNameNotValid: "The last name is too long: maximum 60 characters.",
        // Settings error codes
        MissingArgumentEmail: "Email was left empty.",
        EmailNotValid: "The email address is not valid.",
        EmailTooLong: "The email address is too long: maximum 255 characters.",
        SectionNotValid: "The section entered is not valid.",
        NoChangesEmail: "Please enter a new email address.",
        MissingArgumentsPasswords: "Please fill out fields to change password.",
        PasswordsNoMatch: "Passwords do not match.",
        MissingArgumentCurrentPassword: "Please confirm current password.",
        NoFilesUploaded: "No file was uploaded.",
        ErrorParsingCourseFile: "Error parsing the courses.csv file uploaded... Make sure it is formatted properly.",
        ErrorUploadingParsedCourseFile: "An error occurred while trying to upload the parsed course file to the database.",
        
        CourseDeleted: "The course has been deleted",
        UserRegistered: "has been signed up successfully. They have been sent an email with their credentials.",
        UserAlreadyExists: "The student ID is already registered to a user. Please ensure that the Student ID was entered correctly.",
        CourseAlreadyExists: "The course already exists.",
        CourseCreated: "The course has been created successfully.",
        CoursesCreated: "The courses have been created successfully.",
        CourseEdited: "The course has been edited successfully.",
        AuthorizationFailed: "You are not logged in or you do not have permission to do this.",
        AuthenticationFailed: "User ID or Password is incorrect.",
        AuthenticationExpired: "You have been logged out for prolonged inactivity.",
        AuthenticationDenied: "Too many attempts. Please try again later.",
        AuthorizationTokenMismatch: "Your credentials are invalid. Login again.",
        UserAuthenticated: "User authenticated successfully",
        UserUnauthenticated: "You have been unauthenticated successfully.",
        UserNoPrivilege: "User is not an Admin or a Student.",
        UserNoAccessCourse: "User has no access to this course.",
        UserNoCoursesAccessible: "User is not signed up for any courses.",
        NoNotesForCourse: "There are no notes available for this course.",
        NoNotesForUser: "There are no notes available to you.",
        CourseExtensionUnauthorized: "Extension of course file provided is not allowed. Allowed extension is: <ul><li>.csv</li></ul>",
        NoteExtensionUnauthorized: "Note extension is not allowed. Allowed extensions are: "
        + "<ul><li>.pdf</li><li>.docx</li><li>.doc</li><li>.pptx</li><li>.ppt</li><li>.xlsx</li><li>.csv</li><li>.jpeg</li><li>.jpg</li><li>.png</li><li>.txt</li><li>.zip</li></ul>",
        NoFilesForNote: "No files are attached to this note package.",
        FileIsTooBig: "The selected file is too large.",
        NoteFileDeleteFailure: "A note file could not be deleted successfully.",
        DownloadConfirmationFailed: "The file download confirmation failed.",
        FileCorruptedFrontEnd: "The file you are attempting to download is corrupted.",
        ModuleAccessError: "You do not have access to this module.",
    
        DatabaseError: "Error executing query...",
        DatabasePrepError: "Error preparing database query...",
        DatabaseConnectError: "Error occurred while connecting to the database.",
        DatabaseExecuteError: "Unable to query database.",
        DatabaseSelectError: "Error occurred while selecting from the database.",
        DatabaseInsertError: "Error occurred while inserting into the database.",
        DatabaseDeleteError: "Error occurred while deleting from the database.",
        DatabaseUpdateError: "Error occurred while updating an entry from the database.",
        DatabaseDuplicationError: "This entry already exists in the database.",
        DatabaseRollbackError: "A database error occurred. Contact the Access Centre if this persists.",
        DatabaseCommitError: "A database error occurred. Try again. Contact the Access Centre if this persists.",
    
        //THIS IS REALLY A TRANSLATIONS PROBLEM
        AuthorizationFailedButton: "Okay",
        AuthenticationExpiredButton: "Login Again",
        AuthenticationFailedButton: "Logout"
    };
    
    if(!MessageCode_Codes.hasOwnProperty(code)){
        if(window.app){
            window.app.logUi("MessageCode not found! Code: '"+code+"'");
            return MessageCode_Codes["MessageCodeNotFound"];
        }
    }
    return MessageCode_Codes[code];
}
