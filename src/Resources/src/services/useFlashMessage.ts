import useEventsBus from '@/tools/eventBus';
import { FlashMessageType } from '@/enums/FlashMessage';
import { readonly, ref } from 'vue';

export interface FlashMessage {
    type: FlashMessageType,
    message: string
}

const { receive, emit } = useEventsBus();

const flashMessages = ref<FlashMessage[]>([]);

/**
 * Listens for new flashmessage events and either pushes them directly into the state
 * or in case of an error gets the received errorcode from the errormessage.
 */
receive<FlashMessage>('flashmessage', (data) => {
    if (data.type === FlashMessageType.ERROR) {
        flashMessages.value.push({
            type: data.type,
            message: data.message.split(':')[0]
        });
    } else {
        flashMessages.value.push(data);
    }
});

export default function useFlashMessage() {

    /**
     * Emits a FlashMessage to the EventsBus.
     * @param flashmessage  The message to emit
     */
    function sendFlashMessage(flashmessage: FlashMessage) {
        emit('flashmessage', flashmessage);
    }

    /**
     * Clears all messages from the FlashMessages.
     */
    function clearMessages() {
        flashMessages.value = [];
    }

    return {
        flashMessages: readonly(flashMessages),
        sendFlashMessage,
        clearMessages
    }
}