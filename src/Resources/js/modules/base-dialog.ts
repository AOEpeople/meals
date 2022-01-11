import 'jquery-ui/ui/widgets/dialog';

export abstract class BaseDialog {
    protected $dialog: JQuery;

    public abstract open(): void;

    protected handleCancel(): void {
        this.$dialog.dialog('close');
    }

    protected handleCreate(): void {
        let $widget = $(this).dialog('widget');
        $widget.removeClass('ui-corner-all');
        $widget.find('.ui-dialog-titlebar-close').remove();
        $widget.find('.ui-button').removeClass('ui-corner-all');
    }
}