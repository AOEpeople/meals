import { computed } from 'vue';

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
        })
    })
}));

jest.mock('vue-router', () => ({
    useRouter: () => ({
        push: () => void 0
    })
}));

jest.mock('tools/mercureReceiver', () => ({
    mercureReceiver: {
        init: async () => new Promise((resolve) => resolve(undefined))
    }
}));
