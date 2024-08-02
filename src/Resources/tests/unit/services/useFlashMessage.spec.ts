import { FlashMessageType } from '@/enums/FlashMessage';
import useFlashMessage from '@/services/useFlashMessage';
import { flushPromises } from '@vue/test-utils';
import { describe, beforeEach, it, expect } from 'vitest';

const { flashMessages, sendFlashMessage, clearMessages } = useFlashMessage();

describe('Test useFlashMessage', () => {
    beforeEach(() => {
        clearMessages();
    });

    it('should contain the flashMessage in the state after it was emitted', async () => {
        expect(flashMessages.value).toEqual([]);

        const testMessage = {
            type: FlashMessageType.INFO,
            message: 'test message 123'
        };

        sendFlashMessage(testMessage);

        await flushPromises();

        expect(flashMessages.value).toHaveLength(1);
        expect(flashMessages.value[0]).toEqual(testMessage);
    });

    it('should trim the the flashmessage on receiving an error', async () => {
        const testMessage = {
            type: FlashMessageType.ERROR,
            message: '111: abcgtn'
        };

        sendFlashMessage(testMessage);

        await flushPromises();

        expect(flashMessages.value[0]).toEqual({
            type: FlashMessageType.ERROR,
            message: '111'
        });
    });
});
