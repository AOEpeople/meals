import 'jquery';
import Switchery from "switchery.js";

let SlotIndexView = function () {
    this.checkboxSelector = '#slot-table .table-data.slot-actions input[type="checkbox"]';
    this.switcheryElem = [];
    this.initSlotStateToggler();
};

SlotIndexView.prototype.initSlotStateToggler = function () {
    const self = this;
    $(this.checkboxSelector).each(function (idx, checkbox) {
        new Switchery(checkbox, {
            size: 'small',
            onChange: function (state) {
                const id = $(checkbox).data('id');
                self.slotStateChangeHandler(id, state);
            }
        });
    });
};

SlotIndexView.prototype.slotStateChangeHandler = function (id, state) {
    const url = '/meal/slot/' + id + '/update-state';
    $.post(url, {'disabled': (false === state ? '1' : '0')})
        .fail(function () {
            $('.alert').show();
        });
};

export { SlotIndexView as default };
