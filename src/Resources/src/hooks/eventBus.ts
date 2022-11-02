import { ref, watch } from 'vue'
const bus = ref(new Map())

export default function useEventsBus() {

    function emit(event: string, ...args: any): void {
        bus.value.set(event, args)
    }

    function receive(event: string, callback: Function): void {
        watch(() => bus.value.get(event), val => {
            const [ payload ] = val ?? []
            callback(payload)
        })
    }

    return { emit, receive }
}