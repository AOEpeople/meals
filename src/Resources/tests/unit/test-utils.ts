import { createApp } from 'vue';

export function mockComposableInApp<T>(composable: () => T) {
    let result: T | undefined;
    const app = createApp({
        setup() {
            result = composable();
            // suppress missing template warning
            // eslint-disable-next-line @typescript-eslint/no-empty-function
            return () => {};
        }
    });
    app.mount(document.createElement('div'));
    // return the result and the app instance
    // for testing provide / unmount
    return {
        result,
        app
    };
}
