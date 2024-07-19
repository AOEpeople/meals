import { describe, expect, test } from '@jest/globals';
import useEventBus from 'tools/eventBus';

const { emit, receive } = useEventBus();

describe('test EventBus', () => {
    test('if connection works', (done) => {
        receive<string>('connectionTest', (value) => {
            expect(value).toBe('connected');
            done();
        });

        emit('connectionTest', 'connected');
    });
});
