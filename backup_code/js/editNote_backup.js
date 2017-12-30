/* global Resources,MessageCode,Modal */
var app = app || {
    startup: [],
    afterStartup: []
};

app.editNote = {
    noteId: undefined, //ID of currently editing note
    originalNote: undefined, //Original note data
    submitNote: function(event) {
        //preventDefault appropriate??
        event.preventDefault();
        let newName = document.getElementById("noteName").value;
        let newDesc = document.getElementById("description").value;
        let newDate = new Date(document.getElementById("date").value);
        let oldDate = new Date(this.originalNote.taken_on.replace(/-/g, '\/'));

        var changes = {};
        if (newName != this.originalNote.name)
            changes.name = newName;
        if (newDesc != this.originalNote.description)
            changes.desc = newDesc;
        if (newDate.getFullYear() - oldDate.getFullYear() != 0 ||
            newDate.getMonth() - oldDate.getMonth() != 0 ||
            newDate.getDate() - oldDate.getDate() != 0)
            changes.taken_on = newDate;

        if (changes != {}) {
            Resources.Notes.PUT(this.noteId, changes.name, changes.desc, changes.taken_on, this.successEdit);
        }
    },
    successEdit: function() {
        new Modal("Note Updated", MessageCode["NoteUpdated"], {
            text: "Back to Home page",
            callback: function() {
                app.store("editNoteNoteId", null);
                window.location = "./home.html";
            }
        }).show();
    },
    deleteNote: function() {
        let successBtn = {
            text: "Delete Note",
            callback: function() {
                Resources.Notes.DELETE(app.editNote.noteId, function(e) {
                    app.store("editNoteNoteId", null);
                    new Modal("Delete Note", MessageCode[e.payload.messageCode], null, {
                        text: "Okay",
                        callback: function() {
                            window.location = "./home.html"
                            this.hide();
                        }
                    }).show();
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
    }
};

app.startup.push(function editNoteStartup() {
    document.getElementById('noteData').addEventListener('submit', app.editNote.submitNote.bind(app.editNote));
    document.getElementById('deleteNote').addEventListener('click', app.editNote.deleteNote.bind(app.editNote));
});

app.afterStartup.push(function editNoteAfterStartup() {
    let noteId = app.getStore("editNoteNoteId");
    if (noteId) {
        app.editNote.noteId = noteId;
        Resources.Notes.GET_id(noteId, app.editNote.getNote);
    } else {
        window.location = "./home.html";
    }
});
