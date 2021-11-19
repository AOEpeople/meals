import Switchery from "switchery.js";

export default function SlotIndexView() {
    this.init();
};

SlotIndexView.prototype.init = function () {
    this.initSlotStateToggler();
    $('#slot-table .table-data.slot-actions a.delete').click(this.handleDeleteSlot.bind(this));
}

SlotIndexView.prototype.initSlotStateToggler = function () {
    const self = this;
    $('#slot-table .table-data.slot-actions input[type="checkbox"]').each(function (idx, checkbox) {
        new Switchery(checkbox, {
            size: 'small',
            onChange: function (state) {
                const id = $(checkbox).data('id');
                self.handleToggleSlotState(id, state);
            }
        });
    });
};

SlotIndexView.prototype.handleToggleSlotState = function (id, state) {
    const url = '/meal/slot/' + id + '/update-state';
    $.post(url, {'disabled': (false === state ? '1' : '0')})
        .fail(function () {
            $('.alert').show();
        });
};

SlotIndexView.prototype.handleDeleteSlot = function (event) {
    let self = this; // SlotIndexView

    const $delLink = $(event.target);
    const slotId = $delLink.data('id');
    const url = '/meal/slot/' + slotId + '/delete';

    let $flashContainer = $('#flash-msg');
    let msg = '';

    $.ajax({url: url, method: 'DELETE'})
        .done(function () {
            const $slotRow = $delLink.closest('.table-row');
            const slotTitle = $slotRow.find('.slot-title').text();

            $slotRow.remove();
            msg = $flashContainer.data('del-success-msg').replace('_', slotTitle);
            self.showFlashMsg(msg, 'success');
        })
        .fail(function () {
            msg = $flashContainer.data('err-msg');
            self.showFlashMsg(msg, 'error');
        });
}

SlotIndexView.prototype.showFlashMsg = function (msg, type) {
    const msgClass = ('error' === type) ? 'alert-danger' : 'alert-success';
    let $flashContainer = $('#flash-msg');

    $flashContainer.addClass(msgClass).text(msg).slideToggle("slow");
    setTimeout( function () {
        $flashContainer.slideToggle('slow', () => $flashContainer.text('').removeClass(msgClass));
    }, 3000);
}
