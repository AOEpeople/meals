declare module '*.vue' {
    import { defineComponent } from 'vue';
    const component: ReturnType<typeof defineComponent>;
    export default component;
}

declare module '*.png';

declare module '*.svg' {
    const content: any;
    export default content;
}
