import { Ref } from "vue";

export function isResponseOkay<T>(error: Ref<boolean>, response: Ref<T>) {
    return (
        error.value === false &&
        response.value !== null &&
        response.value !== undefined
    );
}