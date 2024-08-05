import useEventBus from '@/tools/eventBus';
import { describe, test, expect } from 'vitest';

const { emit, receive } = useEventBus();

describe('test EventBus', () => {
    test('if connection works', () => new Promise<void>(done => {
        receive<string>('connectionTest', (value) => {
            expect(value).toBe('connected');
            done();
        });

        emit('connectionTest', 'connected');
    }));
});
