export default function ModalDialog($container, opts) {
    this.$container = $container;
    this.options = Object.assign({
            title: '',
            okButton: 'OK',
            cancelButton: 'Cancel',
            callback: () => {}
        }, opts || {}
    );

    if (0 < this.options.title.length) {
        this.$container.prepend('<h3 class="headline">' + this.options.title + '</h3>');
    }

    let okButton = $('<button data-fancybox-close title="OK" class="button ok">' + this.options.okButton + '</button>');
    let cancelButton = $('<button data-fancybox-close title="Cancel" class="button cancel">' + this.options.cancelButton + '</button>');

    let buttonsContainer = $('<div class="actions"></div>');
    buttonsContainer.append(cancelButton, okButton);

    this.$container.append(buttonsContainer);
}

ModalDialog.prototype.open = function () {
    $.fancybox.open(this.$container, this.options);
}
