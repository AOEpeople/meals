import { computed } from "vue";

jest.mock('vue-i18n', () => ({
    useI18n: () => ({
        t: (key: string) => key,
        locale: computed(() => 'en')
    })
}));

jest.mock('@marcoschulte/vue3-progress', () => ({
    useProgress: () => ({
        start: () => ({
            finish: () => void 0
        }),
    }),
}))