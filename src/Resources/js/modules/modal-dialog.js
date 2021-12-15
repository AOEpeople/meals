function ModalDialog($content, opts) {
    let self = this;
    this.$content = $content;
    this.options = Object.assign({
            title: '',
            okButton: 'OK',
            cancelButton: 'Cancel',
            callback: () => {},
        }, opts || {}
    );

    this.$dialog = build();

    function build () {
        let $dialogWrapper = $('<div class="modal-dialog-wrapper"></div>');

        if (0 < self.options.title.length) {
            $dialogWrapper.append('<h1 class="header">' + self.options.title + '</h1>');
        }

        $dialogWrapper.append($content, getButtonBar());

        return $dialogWrapper;
    }

    function getButtonBar () {
        const okButton = $('<button data-fancybox-close class="button ok">' + self.options.okButton + '</button>');
        const cancelButton = $('<button data-fancybox-close class="button cancel">' + self.options.cancelButton + '</button>');

        let buttonsContainer = $('<div class="actions"></div>');
        buttonsContainer.append(cancelButton, okButton);

        return buttonsContainer;
    }
}

ModalDialog.prototype.open = function () {
    $.fancybox.open(this.$dialog, {
        buttons: false,
        modal: true
    });
}

ModalDialog.prototype.getOkButton = function () {
    return this.$dialog.find('.button.ok');
}

module.exports = ModalDialog;
