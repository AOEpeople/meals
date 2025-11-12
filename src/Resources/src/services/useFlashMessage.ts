import useEventsBus from '@/tools/eventBus';
import { FlashMessageType } from '@/enums/FlashMessage';
import { readonly, ref } from 'vue';

export interface FlashMessage {
    type: FlashMessageType;
    message: string;
    hasLifetime: boolean;
}

const FLASHMESSAGE_LIFETIME = 7000;

const { receive, emit } = useEventsBus();

const flashMessages = ref<FlashMessage[]>([]);
let shiftingActive = false;

/**
 * Listens for new flashmessage events and either pushes them directly into the state
 * or in case of an error gets the received errorcode from the errormessage.
 */
receive<FlashMessage>('flashmessage', (data) => {
    if (data.type === FlashMessageType.ERROR) {
        flashMessages.value.push({
            type: data.type,
            message: data.message.split(':')[0],
            hasLifetime: data.hasLifetime
        });
    } else {
        flashMessages.value.push(data);
    }
    if (data.hasLifetime) {
        shiftFlashMessages();
    }
});

/**
 * Shifts the flashMessages after a set delay and calls itself again.
 * Ends when flashMessages are empty.
 */
function shiftFlashMessages() {
    if (shiftingActive === false && flashMessages.value.length > 0) {
        shiftingActive = true;
        setTimeout(() => {
            flashMessages.value.shift();
            shiftingActive = false;
            if (flashMessages.value.length > 0) {
                shiftFlashMessages();
            }
        }, FLASHMESSAGE_LIFETIME);
    }
}

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

    function removeMessagesByMessageCode(messageCode: string) {
        flashMessages.value = flashMessages.value.filter((flashMessage: FlashMessage) => {
            return flashMessage.message !== messageCode;
        });
    }

    return {
        flashMessages: readonly(flashMessages),
        sendFlashMessage,
        clearMessages,
        removeMessagesByMessageCode
    };
}
