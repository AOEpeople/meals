import { computed } from "vue";

jest.mock('vue-i18n', () => ({
    useI18n: () => ({
        t: (key: string) => key,
        locale: computed(() => 'en')
    })
}));