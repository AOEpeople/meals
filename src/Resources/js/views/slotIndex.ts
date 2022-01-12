import Switchery from "switchery.js";

export default class SlotIndexView {
    constructor() {
        this.init();
    }

    private init() {
        this.initSlotStateToggler();
        $('#slot-table .table-data.slot-actions a.delete').on('click', this.handleDeleteSlot.bind(this));
    }

    private initSlotStateToggler() {
        const self = this;
        $('#slot-table .table-data.slot-actions input[type="checkbox"]').each(function (idx, checkbox) {
            new Switchery(checkbox, {
                size: 'small',
                onChange: function (state: boolean) {
                    const id = $(checkbox).data('id');
                    self.handleToggleSlotState(id, state);
                }
            });
        });
    };

    private handleToggleSlotState(id: string, state: boolean) {
        const url = '/meal/slot/' + id + '/update-state';
        $.post(url, {'disabled': (false === state ? '1' : '0')})
            .fail(function () {
                $('.alert').show();
            });
    };

    private handleDeleteSlot(event: JQuery.TriggeredEvent) {
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

    private showFlashMsg(msg: string, type: string) {
        const msgClass = ('error' === type) ? 'alert-danger' : 'alert-success';
        let $flashContainer = $('#flash-msg');

        $flashContainer.addClass(msgClass).text(msg).slideToggle('slow');
        setTimeout( function () {
            $flashContainer.slideToggle('slow', () => $flashContainer.text('').removeClass(msgClass));
        }, 3000);
    }
};
