/* global Resources,MessageCode,Modal,getQueryParameterByName,generateDeleteConfirmationModal,debounce */
var app = app || {
    startup: [],
    afterStartup: []
};

app.notes = {
    courseId: null,
    studentId: null,
    pagesLoaded: 0,
    paginationEnd: false,
    
    getSortMethod: function(){

        let sortSelector = document.getElementById("sortDrop");
        var sortBy = sortSelector.value;
        var returnValue = ["created", "DESC"];
        switch (sortBy) {
            case "newestUpload":
                return(["created", "DESC"]) ;
            case "oldestUpload":
                return(["created", "ASC"]);
            case "newestTakenOn":
                return (["taken_on", "DESC"]);
            case "oldestTakenOn":
                return (["taken_on", "ASC"]);
            case "noteNameAscending":
                return (["noteName", "ASC"]);
            case "noteNameDescending":
                return (["noteName", "DESC"]);
            case "noteTakerAscending": 
                return (["author", "ASC"]);
            case "noteTakerDescending": 
                return (["author", "DESC"]);
            default:
                return (["created", "DESC"]);
        }
    },
    getNotes: function() {
        document.getElementById("notesSearchHeader").innerHTML = "Loading Search Results...";
        app.notes.getNotesPaged(true);
    },
    getNotesPaged: function(forced){
        let scrollPosition = document.body.scrollTop / ((document.body.scrollHeight - document.body.clientHeight) || 1) ;
        if(forced === true || (scrollPosition > 0.9 && !app.notes.paginationEnd)){
            let sorting = app.notes.getSortMethod();
            let sortMethod = sorting[0];
            let sortDirection = sorting[1];
            Resources.Notes.SEARCH(sortMethod, sortDirection, app.notes.courseId, app.notes.studentId, app.notes.pagesLoaded, app.notes.getNotesSuccess, app.notes.getNotesFailed);
        }
    },
    getNotesFailed: function(data) {
        app.handleFailure(data);
        document.getElementById("notesSearchHeader").innerHTML = "Error while loading search results.";
    },
    getNotesSuccess: function(data) {
        var notes = data.payload.notes;
        var previous = notes.length;
        if(app.notes.pagesLoaded != 0){
            var previousValue = parseInt(document.querySelector("#notesSearchHeader > span").innerHTML);
            if(!isNaN(previousValue))
                previous += previousValue
        }
        document.getElementById("notesSearchHeader").innerHTML = "Search Results: <span>" + previous + "</span> note".pluralize(previous) + " found.";

        if (notes.length == 0) {
            app.notes.paginationEnd = true;
            if(app.notes.pagesLoaded == 0){
                var notesContainer = document.getElementById("notesContainer");
                notesContainer.appendChild(app.notes._generateEmptyArticle());
            }
            return;
        }
        
        app.notes.pagesLoaded++;

        var notesContainer = document.getElementById("notesContainer");
        var nonotesArticle = document.getElementById("NONOTES");
        if(nonotesArticle)
            nonotesArticle.parentNode.removeChild(nonotesArticle);

        for (var i = 0; i < notes.length; i++) {
            var article = app.notes._generateArticle(notes[i]);
            notesContainer.appendChild(article);
        }
    },
    _generateEmptyArticle: function() {
        let article = document.createElement("ARTICLE");
        article.id = "NONOTES";

        let articleHeader = document.createElement("HEADER");
        articleHeader.innerHTML = "<span class='title'>No Notes</span>";
        article.appendChild(articleHeader);

        let articleSection = document.createElement("SECTION");
        article.appendChild(articleSection);

        let descriptionP = document.createElement("P");
        descriptionP.innerHTML = "No notes were found.";
        descriptionP.className = "description";
        articleSection.appendChild(descriptionP);

        return article;
    },
    _generateArticle: function(noteData) {
        let article = document.createElement("ARTICLE");


        let articleHeader = document.createElement("HEADER");
        let articleHeaderSpan = document.createElement("SPAN");
        articleHeaderSpan.className = "title";
        articleHeaderSpan.innerText = noteData.name;
        articleHeader.appendChild(articleHeaderSpan);
        article.appendChild(articleHeader);

        let articleSection = document.createElement("SECTION");
        article.appendChild(articleSection);

        let descriptionP = document.createElement("P");
        descriptionP.innerText = noteData.description;
        descriptionP.className = "description";
        articleSection.appendChild(descriptionP);

        let authorP = generatePTag("Notes taken by", noteData.user_name || "Anonymous", true);
        articleSection.appendChild(authorP);

        var section =  (noteData.sectionStart + "").padStart(5, "0");
        if(noteData.sectionStart != noteData.sectionEnd){
            section += " to " + (noteData.sectionEnd + "").padStart(5, "0");
        }
        let courseP = generatePTag("Notes taken for", noteData.course_name + "("+noteData.course_number + " : " + section +")", true);
        articleSection.appendChild(courseP);
        
        let dateP = generatePTag("Notes taken on", new Date(noteData.taken_on.replace(/-/g, '\/')).toPrettyDate(), true);
        articleSection.appendChild(dateP);

        let uploadDateP = generatePTag("Notes uploaded on", new Date(noteData.created).toPrettyDate(), true);
        articleSection.appendChild(uploadDateP);

        var numberDownloadCount = (parseInt(noteData.download_count) || 0);
        var numberDistinctCount = (parseInt(noteData.distinct_downloads) || 0);
        let downloadCount = document.createElement("P");
        downloadCount.innerHTML = "Downloaded <span>" + numberDownloadCount + "</span> time".pluralize(numberDownloadCount) + " by <span>" + numberDistinctCount + "</span> " +
            (numberDistinctCount > 1 ? "different " : "") + "user".pluralize(numberDistinctCount);
        articleSection.appendChild(downloadCount);

        let noteDownload = document.createElement("BUTTON");
        noteDownload.id = noteData.id + "_download";
        noteDownload.innerHTML = "Download Notes";
        noteDownload.addEventListener("click", this.downloadNote);
        articleSection.appendChild(noteDownload);

        let noteDelete = document.createElement("INPUT");
        noteDelete.type = "button";
        noteDelete.id = noteData.id + "_delete";
        noteDelete.value = "Delete Note";
        noteDelete.className = "warning button";
        noteDelete.addEventListener("click", this.deleteNote);
        articleSection.appendChild(noteDelete);

        return article;
    },

    deleteNote: function(e) {
        let id = this.id;
        let that = this;
        generateDeleteConfirmationModal("Are you sure you want to delete this note?", function(event){
            
            // Disable the confirm button 
            event.target.disabled = true;
            
            // Hide the confirmation modal
            this.hide();
            
            Resources.Notes.DELETE(id.substring(0, id.indexOf("_delete")), function(e) {
                var noteDiv = that.parentNode.parentNode;
                //Remove the node from the parent to show it has been deleted.
                noteDiv.parentNode.removeChild(noteDiv);
                let notesContainer = document.getElementById("notesContainer");
                if (notesContainer.children.length == 0) {
                    document.getElementById("notesSearchHeader").innerHTML = "";
                    notesContainer.appendChild(app.notes._generateEmptyArticle());
                    return;
                }
                var span = document.getElementById("notesSearchHeader").getElementsByTagName("SPAN")[0];
                var newCount = parseInt(span.innerHTML) - 1;
                document.getElementById("notesSearchHeader").innerHTML = "Search Results: <span>" + newCount + "</span> note".pluralize(newCount) + " found.";
            });
        }).show();
    },

    downloadNote: function(e) {
        let id = this.id;
        let downloadFunc = function(resp, req){
            let a = document.createElement("a");
            let url = window.URL.createObjectURL(resp);
            a.download = req.getResponseHeader("Content-Disposition").match("\"(.+)\"")[1];
            a.href = url;
            a.click();
            window.URL.revokeObjectURL(url);
            document.getElementById(id).disabled = false;
            document.getElementById(id).innerText = "Download Notes";
        };
        let successFunction = function(resp, req) {
            var myRe = /\(([^\).]+)\)/gi;
            var matches = [];
            var match;
            while ((match = myRe.exec(req.getResponseHeader("Content-Description"))) !== null) {
                matches.push(match[1]);
            }
            if (matches[0] != matches[1]) {
                new Modal("File Corruption", MessageCode["FileCorruptedFrontEnd"], {
                    text: "Download Anyway",
                    callback: function(e){
                        e.hide();
                        downloadFunc(resp, req);
                    }
                }).show();
                return;
            }

            downloadFunc(resp, req);
        };
        let progressFunction = function(evt) {
            var percentComplete = Math.round((evt.loaded / evt.total) * 100);
            document.getElementById(id).innerText = "Downloading... " + percentComplete + "%";
            if (percentComplete >= 100) {
                document.getElementById(id).disabled = false;
                document.getElementById(id).innerText = "Download Notes";
            }
        };
        this.innerText = "Downloading...";
        this.disabled = true;

        Resources.Files.GET(id.substring(0, id.indexOf("_download")), progressFunction, successFunction);
    },
    
    getNotesNewSort: function(){
        app.notes.pagesLoaded = 0;
        var nc = document.getElementById("notesContainer");
        while (nc.firstChild) nc.removeChild(nc.firstChild);
        app.notes.getNotes();
    }
};

app.startup.push(function notesStartup() {
    app.notes.studentId = getQueryParameterByName("studentId");
    app.notes.courseId = getQueryParameterByName("courseId");
    
    document.body.onscroll = debounce(app.notes.getNotesPaged, 250);
    document.getElementById("sortDrop").addEventListener('change', app.notes.getNotesNewSort);
});

app.afterStartup.push(function notesAfterStartup() {
    app.notes.getNotes();
});
