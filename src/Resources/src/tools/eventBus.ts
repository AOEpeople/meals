import { ref, watch } from 'vue';

const bus = ref(new Map());

export default function useEventsBus() {
    function emit(event: string, ...args: unknown[]): void {
        bus.value.set(event, args);
    }

    function receive<T>(event: string, callback: (data: T) => void): void {
        watch(
            () => bus.value.get(event),
            (val) => {
                const [payload] = val ?? [];
                callback(payload);
            }
        );
    }

    return { emit, receive };
}
