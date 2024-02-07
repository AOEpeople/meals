import { Ref } from 'vue';

/**
 * Checks if a given API response object is defined and error free.
 * Also accepts an optional callback that checks if the response adheres to a specific interface.
 * @param error         Ref with a boolean value that indicates errors
 * @param response      Ref with the response object from the API call
 * @param typeChecker   Callback function to check the type of the response object
 */
export function isResponseObjectOkay<T>(error: Ref<boolean>, response: Ref<T>, typeChecker?: (arg: T) => boolean) {
    return (
        error.value === false &&
        response.value !== null &&
        response.value !== undefined &&
        checkObject<T>(response, typeChecker)
    );
}

/**
 * Checks if a given API response array is defined and error free.
 * Also accepts an optional callback that checks if the response adheres to a specific interface.
 * @param error         Ref with a boolean value that indicates errors
 * @param response      Ref with the response array from the API call
 * @param typeChecker   Callback function to check the type of the response array
 */
export function isResponseArrayOkay<T>(error: Ref<boolean>, response: Ref<T[]>, typeChecker?: (arg: T) => boolean) {
    return (
        error.value === false &&
        response.value !== null &&
        response.value !== undefined &&
        checkArray<T>(response, typeChecker)
    );
}

/**
 * Checks if a given API response dictionary is defined and error free.
 * Also accepts an optional callback that checks if the response adheres to a specific interface.
 * @param error         Ref with a boolean value that indicates errors
 * @param response      Ref with the response dictionary from the API call
 * @param typeChecker   Callback function to check the type of the response dictionary
 */
export function isResponseDictOkay<T>(
    error: Ref<boolean>,
    response: Ref<Record<number | string, T>>,
    typeChecker?: (arg: T) => boolean
) {
    return (
        error.value === false &&
        response.value !== null &&
        response.value !== undefined &&
        checkDict<T>(response, typeChecker)
    );
}

function checkArray<T>(response: Ref<T[]>, typeChecker?: (arg: T) => boolean) {
    return (
        Array.isArray(response.value) &&
        (response.value.length > 0
            ? typeof typeChecker === 'function' && typeChecker !== null && typeChecker !== undefined
                ? typeChecker(response.value[0])
                : true
            : true)
    );
}

function checkObject<T>(response: Ref<T>, typeChecker?: (arg: T) => boolean) {
    return typeof typeChecker === 'function' && typeChecker !== null && typeChecker !== undefined
        ? typeChecker(response.value)
        : true;
}

function checkDict<T>(response: Ref<Record<number | string, T>>, typeChecker?: (arg: T) => boolean) {
    return typeof typeChecker === 'function' && typeChecker !== null && typeChecker !== undefined
        ? typeChecker(response.value[Object.keys(response.value)[0]])
        : true;
}
