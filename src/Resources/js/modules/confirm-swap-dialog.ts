import 'jquery-ui/ui/widgets/dialog';
import {ParticipationRequest, ParticipationRequestHandler} from './participation-request-handler';
import {ActionResponse} from './participation-response-handler';
import {BaseDialog} from './base-dialog';

export class ConfirmSwapDialog extends BaseDialog {
    private readonly containerID: string = '#confirm-swapbox';
    private opts: ConfirmSwapDialogOptions;

    constructor(opts: ConfirmSwapDialogOptions) {
        super();
        this.opts = opts;
    }

    public open(): void {
        this.$dialog = $(this.containerID).dialog({
            modal: true,
            width: 500,
            maxWidth: 500,
            draggable: false,
            buttons: {
                'OK': this.handleOk.bind(this),
                'Cancel': this.handleCancel.bind(this)
            },
            create: this.handleCreate
        });
    }

    private handleOk(): void {
        ParticipationRequestHandler.sendRequest(this.opts.participationRequest, this.opts.$checkbox, this.opts.handlerMethod);
        this.$dialog.dialog('close');
    }
}

interface ConfirmSwapDialogOptions {
    participationRequest: ParticipationRequest,
    $checkbox: JQuery,
    handlerMethod: ($checkbox: JQuery, response: ActionResponse) => void
}
