let MessageCode = {
    UnknownResourceMethod: "The request method doesn't exist on the server. Contact ITS if this problem persists.",
    UnknownServerError: "A server error occured. Please contact ITS if this problem persists.",
    JSONParseException: "Unable to parse a response from the server. Please contact ITS if this problem persists.",
    MalformedBody: "The request body was not formed properly. Contact ITS if this problem persists.",
    RequestTimedout: "A request took too long to process and has timed out. Ensure you have internet connectivity.",
    UnknownFileUploadError: "An error occured while uploading a file. Contact ITS if this problem persists.",
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
    KeyNotFound: "Malformed request body",

    UserUpdated: "Your settings have been updated successfully.",
    PasswordUpdateFailure: "Your password could not be updated. Please change it in the settings page.",
    PasswordTooSmall: "Password must be 9 characters or more.",
    UserDeleted: "User deleted.",
    NoteDeleted: "Note has been deleted",
    NoteUpdated: "Note has been updated",
    UserAccessDeleted: "User access deleted.",
    UserAccessUpdated: "Notifications toggled.",
    UserAccessNotUpdated: "The user was not granted access to any courses.",
    NoFilesUploaded: "You must upload files when creating notes.",
    UserCreateNotesDenied: "You have not been granted rights to upload notes for this course.",
    UserDownloadNotesDenied: "You have not been granted rights to download notes for this course.",
    UserAlreadyRegisteredInCourse: "This user is already signed up to be a notetaker or a student in this course.",

    MissingArgumentFirstName: "First name was left empty.",
    MissingArgumentLastName: "Last name was left empty.",
    MissingArgumentPassword: "Password was left empty.",
    MissingArgumentPasswords: "Password(s) left empty.",
    MissingArgumentStudentId: "Student ID was left empty.",
    MissingArgumentNoteName: "Note name was left empty.",
    MissingArgumentCourseName: "Course name was left empty.",
    MissingArgumentCourseNumber: "Course Code was left empty.",
    MissingArgumentSection: "Section was left empty.",
    MissingArgumentTeacher: "Teacher name was left empty.",
    MissingArgumentCourseId: "Course ID was left empty.",
    MissingArgumentCourse: "Please select a course.",
    MissingArgumentRole: "Please select a role.",
    MissingArgumentUserId: "User ID was left empty.",
    MissingArgumentNoteId: "Note ID is missing from the request. Contact ITS if this problem persists.",
    MissingArgumentTimestamp: "Timestamp was left empty, contact ITS if this problem persists.",
    MissingArgumentNotifications: "You must specify if you want notifications.",
    MissingSortingMethod: "No sorting method was selected.",
    MissingArgumentCourses: "Please select at least one course.",
    MissingArgumentYear: "Please select a year",
    MissingArgumentSeason: "Please select a season.",
    MissingArgumentYearExpiry: "Please select an expiry year.",
    MissingArgumentSeasonExpiry: "Please select an expiry season.",

    NoteNameNotValid: "Note name is too long: maximum 60 characters.",
    DescriptionNotValid: "Description is too long: maximum 500 characters.",
    DateNotValid: "The selected date is not valid.",
    MissingArgumentExpiryDate: "Please select an expiry date.",
    UserIdNotValid: "User ID must be exactly 7 digits.",
    UserNameNotValid: "The student name is not valid.",
    FutureSemester: "Please choose a past or current semester.",
    PastSemester: "Please choose a future or current semester.",
    // Settings error codes
    MissingArgumentEmail: "Email was left empty.",
    EmailNotValid: "The email address is not valid.",
    NoChangesEmail: "The email address is not valid.",
    MissingArgumentsPasswords: "Please fill out fields to change password.",
    PasswordsNoMatch: "Passwords do not match.",
    MissingArgumentCurrentPassword: "Please confirm current password.",


    UserRegistered: "has been signed up successfully. They have been sent an email with their credentials.",
    UserAlreadyExists: "The student ID is already registered to a user. Ensure the Student ID was entered correctly.",
    CourseAlreadyExists: "The course already exists.",
    CourseCreated: "The course has been created successfully.",
    CourseEdited: "The course has been edited successfully.",
    AuthorizationFailed: "You are not logged in or you do not have permission to do this.",
    AuthenticationFailed: "User ID or Password is incorrect",
    AuthenticationExpired: "You have been logged out for prolonged inactivity.",
    AuthorizationTokenMismatch: "Your credentials are invalid. Login again.",
    UserAuthenticated: "User authenticated successfully",
    UserUnauthenticated: "You have been unauthenticated successfully",
    UserNoPrivilege: "User is not an Admin or a Student.",
    UserNoAccessCourse: "User has no access to this course",
    UserNoCoursesAccessible: "User is not signed up for any courses.",
    NoNotesForCourse: "There are no notes available for this course.",
    NoNotesForUser: "There are no notes available to you.",
    NoteExtensionUnauthorized: "Note extension is not allowed. Allowed extensions are: "
    + "<ul><li>.pdf</li><li>.docx</li><li>.doc</li><li>.pptx</li><li>.ppt</li><li>.xlsx</li><li>.jpeg</li><li>.jpg</li><li>.png</li><li>.txt</li><li>zip</li></ul>",
    NoFilesForNote: "No files are attached to this note package.",
    FileIsTooBig: "The selected file is too large.",
    UnknownFileUploadError: "An unknown error occured when uploading a file.",
    DownloadConfirmationFailed: "The file download confirmation failed.",
    FileCorruptedFrontEnd: "The file you are attempting to download is corrupted.",
    ModuleAccessError: "You do not have access to this module.",

    DatabaseError: "Error executing query...",
    DatabasePrepError: "Error preparing database query...",
    DatabaseConnectError: "Error occurred while connecting to the database",
    DatabaseExecuteError: "Unable to query database.",
    DatabaseSelectError: "Error occurred while selecting from the database",
    DatabaseInsertError: "Error occurred while inserting into the database",
    DatabaseDeleteError: "Error occurred while deleting from the database",
    DatabaseUpdateError: "Error occurred while updating an entry from the database",
    DatabaseDuplicationError: "This entry already exists in the database.",

    //THIS IS REALLY A TRANSLATIONS PROBLEM
    AuthorizationFailedButton: "Okay",
    AuthenticationExpiredButton: "Login Again",
    AuthenticationFailedButton: "Logout"
};