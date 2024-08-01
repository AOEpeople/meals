import { vi } from 'vitest';
import { computed } from 'vue';

vi.mock('vue-i18n', () => ({
    useI18n: () => ({
        t: (key: string) => key,
        locale: computed(() => 'en')
    })
}));

vi.mock('@marcoschulte/vue3-progress', () => ({
    useProgress: () => ({
        start: () => ({
            finish: () => void 0
        })
    })
}));

vi.mock('vue-router', () => ({
    useRouter: () => ({
        push: () => void 0
    })
}));

vi.mock('tools/mercureReceiver', () => ({
    mercureReceiver: {
        init: async () => new Promise((resolve) => resolve(undefined))
    }
}));
