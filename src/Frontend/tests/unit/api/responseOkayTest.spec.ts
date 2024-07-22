import { isResponseObjectOkay, isResponseArrayOkay, isResponseDictOkay } from '@/api/isResponseOkay';
import { ref } from 'vue';
import { IMessage, isMessage } from '@/interfaces/IMessage';
import { Dictionary } from '@/types/types';
import { TimeSlot, isTimeSlot } from '@/stores/timeSlotStore';

describe('Test isResponseOkay', () => {
    it('should return true if the response is defined, not null and there are no errors', () => {
        const response = ref({
            test: 'test'
        });
        const error = ref(false);
        expect(isResponseObjectOkay(error, response)).toBeTruthy();
    });

    it('should return false if there is an error', () => {
        const response = ref({
            test: 'test'
        });
        const error = ref(true);
        expect(isResponseObjectOkay(error, response)).toBeFalsy();
    });

    it('should return false if respose is null', () => {
        const response = ref(null);
        const error = ref(false);
        expect(isResponseObjectOkay(error, response)).toBeFalsy();
    });

    it('should be false if response is undefined', () => {
        const response = ref(undefined);
        const error = ref(false);
        expect(isResponseObjectOkay(error, response)).toBeFalsy();
    });

    it('should be false if respose is undefined and error is true', () => {
        const response = ref(undefined);
        const error = ref(true);
        expect(isResponseObjectOkay(error, response)).toBeFalsy();
    });

    it('should accept a callback to check for a specific type', () => {
        const msg: IMessage = {
            message: 'TestString123'
        };

        const response = ref<IMessage>(msg);
        const error = ref(false);
        expect(isResponseObjectOkay<IMessage>(error, response, isMessage)).toBeTruthy();
    });

    it('should accept a callback and check for an array', () => {
        const msgs: IMessage[] = [{ message: 'testmsg1' }, { message: 'testmsg2' }];

        const response = ref<IMessage[]>(msgs);
        const error = ref(false);
        expect(isResponseArrayOkay<IMessage>(error, response, isMessage)).toBeTruthy();
    });

    it('should accept a callback and fail the check for an array', () => {
        const msgs = { message: 'testmsg1' };

        const response = ref(msgs);
        const error = ref(false);
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        expect(isResponseArrayOkay<IMessage>(error, response as any, isMessage)).toBeFalsy();
    });

    it('should accept a callback and check for a dictionary', () => {
        const msgs: Dictionary<IMessage> = {
            1: { message: 'testmsg1' },
            2: { message: 'testmsg2' }
        };

        const response = ref<Record<number, IMessage>>(msgs);
        const error = ref(false);
        expect(isResponseDictOkay<IMessage>(error, response, isMessage)).toBeTruthy();
    });

    it('should accept a callback and fail the check for a dictionary', () => {
        const msgs = { message: 'testmsg1' };

        const response = ref(msgs);
        const error = ref(false);
        // @ts-expect-error ts needs to be tricked to allow forbidden types
        expect(isResponseDictOkay<IMessage>(error, response, isMessage)).toBeFalsy();
    });

    it('should accept a timeslot object', () => {
        const slot: TimeSlot = {
            title: 'Test',
            limit: 9,
            order: 1,
            enabled: true,
            slug: 'test'
        };

        const response = ref<TimeSlot>(slot);
        const error = ref(false);
        expect(isResponseObjectOkay<TimeSlot>(error, response, isTimeSlot)).toBeTruthy();
    });
});
