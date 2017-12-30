/*Where buttons are 
{
    text: "Some title"
    callback: function(){},
}
*/
function Modal(title, content, confirmButton, cancelButton, cancelText) {
    this.title = title;
    this.content = content;

    this.confirmButton = confirmButton;
    this.cancelButton = cancelButton;
    if (this.cancelButton == null) {
        this.cancelButton = {
            text: cancelText || "Cancel",
            callback: function() {
                this.hide();
            }
        };
    } else {
        if (this.cancelButton.callback == null) {
            this.cancelButton.callback = function() {
                this.hide();
            }
        }
    }

    if (!document.getElementById("MODALWRAPPER")) {
        Modal.generateWrapper();
    }
}

Modal.prototype.show = function() {
    this.hide();

    var modalDiv = document.createElement("DIV");
    modalDiv.id = "MODAL";
    modalDiv.onclick = function(e) {
        e.stopPropagation();
    };

    var modalHeader = document.createElement("HEADER");
    modalHeader.innerHTML = this.title;
    modalDiv.appendChild(modalHeader);

    var modalBody = document.createElement("DIV");
    modalBody.innerHTML = this.content;
    modalDiv.appendChild(modalBody);

    var modalFooter = document.createElement("FOOTER");

    var that = this;

    if (this.confirmButton) {
        var confirmButton = document.createElement("BUTTON");
        confirmButton.className = "confirmButton";
        confirmButton.innerHTML = this.confirmButton.text;
        confirmButton.onclick = function(e) {
            that.confirmButton.callback.call(that, e);
        };
        modalFooter.appendChild(confirmButton);
    }

    if (this.cancelButton) {
        var cancelButton = document.createElement("BUTTON");
        cancelButton.className = "cancelButton";
        cancelButton.innerHTML = this.cancelButton.text;
        cancelButton.onclick = function(e) {
            that.cancelButton.callback.call(that);
            that.hide();
        };
        modalFooter.appendChild(cancelButton);

        document.getElementById("MODALWRAPPER").onclick = function() {
            that.cancelButton.callback.call(that);
            that.hide();
        };
    }

    modalDiv.appendChild(modalFooter);

    var wrapper = document.getElementById("MODALWRAPPER");
    if (!wrapper) {
        wrapper = Modal.generateWrapper();
    }
    wrapper.appendChild(modalDiv);
    wrapper.style.display = "block";
};

Modal.prototype.hide = function() {
    var currentModal = document.getElementById("MODAL");
    if (currentModal) currentModal.parentElement.removeChild(currentModal);

    document.getElementById("MODALWRAPPER").style.display = "none";
};

Modal.generateWrapper = function() {
    var wrapper = document.createElement("DIV");
    wrapper.id = "MODALWRAPPER";
    document.getElementsByTagName("BODY")[0].appendChild(wrapper);
    return wrapper;
};


function generateDeleteConfirmationModal(content, confirmCallback) {
    var confirmButton = {
        text: "Confirm",
        callback: confirmCallback
    };
    return new Modal("Are you sure?", content, confirmButton);
}
