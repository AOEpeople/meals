import { vi } from 'vitest';
import { computed } from 'vue';

// --- Global Fetch Mock ---
global.fetch = vi.fn(() =>
  Promise.resolve({
    ok: true,
    status: 200,
    json: () => Promise.resolve({}),
    text: () => Promise.resolve(''),
  })
) as unknown as typeof fetch;

// --- Mock window.location ---
Object.defineProperty(window, 'location', {
  value: {
    ...window.location,
    reload: vi.fn(),
    assign: vi.fn(),
    replace: vi.fn(),
  },
  writable: true,
});

// --- Blockiere XMLHttpRequest ---
global.XMLHttpRequest = vi.fn(() => ({
  open: vi.fn(),
  send: vi.fn(),
  setRequestHeader: vi.fn(),
})) as any;

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
    }),
    createRouter: () => ({
        beforeEach: () => void 0
    }),
    createWebHistory: () => void 0,
}));

vi.mock('@/tools/mercureReceiver', () => ({
    mercureReceiver: {
        init: async () => new Promise((resolve) => resolve(undefined))
    }
}));

vi.mock('@/tools/checkActiveSession', () => ({
  default: vi.fn(() => Promise.resolve(true))
}));