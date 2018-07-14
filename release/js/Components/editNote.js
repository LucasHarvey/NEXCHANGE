/* global Resources,MessageCode,datePolyFillStart,Modal,location,getExtension */
var app = app || {
    startup: [],
    afterStartup: []
};

app.editNote = {
    noteId: undefined, //ID of currently editing note
    originalNote: undefined, //Original note data
    uploadInProgress: false,
    
    reset: function() {
        app.editNote.uploadInProgress = false;

        document.getElementById("pb").style.width = 0;
        document.getElementById("pt").innerText = "";

    },
    
    setProgress: function(percent) {
        percent = percent || 0;
        let progressBar = document.getElementById("pb");
        let progressText = document.getElementById("pt");

        if (progressBar !== undefined) {
            progressBar.style.width = percent + "%";
        }

        if (progressText !== undefined) {
            progressText.innerText = percent + "%";
            if(percent == 100){
                progressText.innerText += " - Upload Complete";
            }
        }
    },
    
    addFile: function(event) {
        // Reset the progress bar and progress text to 0 when the user clicks on the button to select files
        document.getElementById("pb").style.width = 0;
        document.getElementById("pt").innerText = "";
    },
    
    submitNote: function(event) {
        event.preventDefault();
        document.getElementById("barContainer").style.display = "none";
        if (app.editNote.uploadInProgress) {
            console.warn("Notes are already being uploaded...");
            return;
        }
        // Reset the progress bar
        app.editNote.reset();
        
        let newName = document.getElementById("noteName").value;
        let newDesc = document.getElementById("description").value;
        let newDate = new Date(document.getElementById("date").value.replace(/-/g, '\/').replace(/T.+/, ''));
        let files = document.getElementById('file').files;
        let oldDate = new Date(this.originalNote.taken_on.replace(/-/g, '\/').replace(/T.+/, ''));
        
        if(!newDate){
            app.handleFailure({
                messageCode: "MissingArgumentDate",
                status: 400
            });
            return;
        }
        if(files.length > 20) {
            app.handleFailure({
                messageCode: "TooManyFiles",
                status: 400
            });
            return;
        }

        var changes = {
            name: undefined,
            desc: undefined,
            taken_on: undefined,
            files: undefined
        };
        if (newName != this.originalNote.name)
            changes.name = newName;
        if (newDesc != this.originalNote.description)
            changes.desc = newDesc;
        if (newDate.getFullYear() - oldDate.getFullYear() != 0 ||
            newDate.getMonth() - oldDate.getMonth() != 0 ||
            newDate.getDate() - oldDate.getDate() != 0){
                var validatedDate = app.dateFormatting.isPastDate(newDate);
                if (!validatedDate) {
                    app.handleFailure({
                        messageCode: "DateNotValid",
                        status: 400
                    });
                    return;
                }
                var dateToSubmit = app.dateFormatting.parseSubmissionDate(newDate);
                changes.taken_on = dateToSubmit;
        }
        if(files.length>0){
            changes.files = files;
            document.getElementById("barContainer").style.display = "block";
        }
        for(var i=0; i<files.length; i++){
            var extension = getExtension(files[i].name);
            if(!app.DEFAULTS.ALLOWED_EXTENSIONS.includes(extension)){
                new Modal("Error", MessageCode("NoteExtensionUnauthorized"), null, {
                text: "Okay"
                }).show();
                return;
            }
        }

        if (changes.name !== undefined || changes.desc !== undefined || changes.taken_on !== undefined || changes.files !== undefined) {
            
            app.editNote.uploadInProgress = true;
        
            // Disable the form
            document.getElementById('noteData').removeEventListener('submit', app.editNote.submitNote);
            document.getElementById("submit").disabled = true;
            
            Resources.NotesEdit.POST(this.noteId, changes.name, changes.desc, changes.taken_on, changes.files, this.successEdit, this.failureEdit, function(event) {
                if (event.lengthComputable === true) {
                    let percent = Math.round((event.loaded / event.total) * 100);
                    app.editNote.setProgress(percent);
                }
            });
        } else {
            new Modal("No Changes", MessageCode("NoChangesToMake"), null, null, "Okay").show();
        }
        
    },
    successEdit: function(response) {
        
        // Clear the file input
        var fileSelector = document.getElementById("file");
        fileSelector.parentNode.removeChild((fileSelector));
        
        var newFileSelector = document.createElement("INPUT");
        newFileSelector.type = "file";
        newFileSelector.id = "file";
        newFileSelector.name = "file[]";
        newFileSelector.className = "inputFile";
        newFileSelector.multiple = true;
        newFileSelector.addEventListener("change", app.editNote.updateFileLabel);
        
        var fileUserField = document.getElementById("fileUserField");
        
        var fileLabel = document.getElementById("fileLabel");
        
        fileUserField.insertBefore(newFileSelector, fileLabel);
        
        app.editNote.updateFileLabel();
        
        
        app.editNote.uploadInProgress = false;
        
        // Enable the form
        document.getElementById('noteData').addEventListener('submit', app.editNote.submitNote);
        document.getElementById("submit").disabled = false;
        
        // Update the original note
        let note = response.payload.note;
        app.editNote.originalNote = note;
        
        // Update the edit inputs
        document.getElementById("noteName").value = note.name;
        document.getElementById("description").value = note.description;
        document.getElementById("date").value = note.taken_on;
        app.editNote.updateCharacterLimit();
        
        let successes = response.payload.succeeded;

        var modalText = "Note has been updated.";

        if (successes.length > 0) {
            modalText += '<p>Uploaded file'.pluralize(successes.length) + ':</p><ul>';
            for (var x = 0; x < successes.length; x++) {
                modalText += "<li><span>" + successes[x].name + "</span></li>";
            }
            modalText += "</ul>";
        }

        
        new Modal("Note Updated", modalText, {
            text: "Back To Home Page",
            callback: function() {
                app.store("editNoteNoteId", null);
                location.assign("./home");
            }
        }, null, "Okay").show();
    },
    
    failureEdit: function(data){
        app.editNote.uploadInProgress = false;
        
        // Enable the form
        document.getElementById('noteData').addEventListener('submit', app.editNote.submitNote);
        document.getElementById("submit").disabled = false;
        
        app.handleFailure(data);
    },
    deleteNote: function() {
        let successBtn = {
            text: "Delete Note",
            callback: function(e) {
                var deleteButton = e.target;
                deleteButton.disabled = true;
                
                Resources.Notes.DELETE(app.editNote.noteId, function(response) {
                    app.store("editNoteNoteId", null);
                    new Modal("Delete Note", MessageCode(response.payload.messageCode), {
                        text: "Okay",
                        callback: function() {
                            location.assign("./home");
                            this.hide();
                        }
                    }, false).show();
                }, function(response){
                    // Failure Callback
                    deleteButton.disabled = false;
                    app.handleFailure(response);
                    
                });
            }
        };
        new Modal("Delete Note", "Are you sure you want to delete this note?", successBtn).show();
    },
    getNote: function(data) {
        let note = data.payload;
        app.editNote.originalNote = note;
        document.getElementById("noteName").value = note.name;
        document.getElementById("description").value = note.description;
        document.getElementById("date").value = note.taken_on;
        app.editNote.updateCharacterLimit();
    },
    
    // Determines how many characters are left for the description field
    updateCharacterLimit: function() {
        let description = document.getElementById('description');
        var charactersUsed = description.value.length;
        var charactersLeft = 500 - charactersUsed;
        if (charactersLeft < 0) {
            app.handleFailure({
                messageCode: "DescriptionNotValid",
                status: 400
            });
            return;
        }

        document.getElementById("characterCount").innerHTML = "Characters left: " + charactersLeft;
    },
    
    updateFileLabel: function(){
        var input = document.getElementById("file");
        var label = document.getElementById("fileLabel");
        
        if(input.files.length == 0) {
            label.innerText = "Select File(s)";
            return;
        }
        
        label.innerText = input.files.length + " File".pluralize(input.files.length)+" Selected";
    }
};

app.startup.push(function editNoteStartup() {
    app.editNote.submitNote = app.editNote.submitNote.bind(app.editNote);
    
    document.getElementById('noteData').addEventListener('submit', app.editNote.submitNote);
    document.getElementById('deleteNote').addEventListener('click', app.editNote.deleteNote);
    
    document.getElementById('file').addEventListener('click', app.editNote.addFile);
    
    // Character counting for description field
    document.getElementById('description').addEventListener('input', app.editNote.updateCharacterLimit);
    document.getElementById('description').addEventListener('keyup', app.editNote.updateCharacterLimit);
    
    // Change the file label when files are added
    document.getElementById("file").addEventListener("change", app.editNote.updateFileLabel);
    app.editNote.updateFileLabel();
});

app.afterStartup.push(function editNoteAfterStartup() {
    let noteId = app.getStore("editNoteNoteId");
    datePolyFillStart();
    if (noteId) {
        app.editNote.noteId = noteId;
        Resources.Notes.GET_id(noteId, app.editNote.getNote);
    } else {
        location.assign("./home");
    }
});
