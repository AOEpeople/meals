import { isMessage, IMessage } from '@/interfaces/IMessage';

describe('Test isMessage', () => {
    it('should return true if the response is of type IMessage', () => {
        const response: IMessage = {
            message: 'test'
        };
        expect(isMessage(response)).toBe(true);
    });

    it('should return false if the response contains members other than message', () => {
        const response = {
            message: 'test',
            other: 'other'
        };
        expect(isMessage(response)).toBe(false);
    });

    it('should return false if message is not of type string', () => {
        const response = {
            message: 1
        };
        expect(isMessage(response)).toBe(false);
    });

    it('should return false if message is not present', () => {
        const response = {
            other: 'other'
        };
        expect(isMessage(response)).toBe(false);
    });

    it('should return false if response is not of object type', () => {
        const response = 'test';
        expect(isMessage(response)).toBe(false);
    });

    it('should return false if response is null', () => {
        const response = null;
        expect(isMessage(response)).toBe(false);
    });

    it('should return false if response is undefined', () => {
        const response = undefined;
        expect(isMessage(response)).toBe(false);
    });

    it('should return false if response is empty', () => {
        const response = {};
        expect(isMessage(response)).toBe(false);
    });

    it('should return false if response is empty array', () => {
        const response = [];
        expect(isMessage(response)).toBe(false);
    });

    it('should return false if response is of a type other than IMessage', () => {
        interface ITest {
            test: string;
        }

        const response: ITest = {
            test: 'test'
        };

        expect(isMessage(response)).toBe(false);
    });
});
