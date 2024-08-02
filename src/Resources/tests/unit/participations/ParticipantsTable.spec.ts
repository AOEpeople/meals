import ParticipantsTable from '@/components/participations/ParticipationsTable.vue';
import ParticipantsTableHead from '@/components/participations/ParticipantsTableHead.vue';
import ParticipantsTableBody from '@/components/participations/ParticipantsTableBody.vue';
import dashboard from '../fixtures/dashboard.json';
import participations from '../fixtures/participations.json';
import { shallowMount } from '@vue/test-utils';
import { computed, nextTick, ref } from 'vue';
import { vi, describe, beforeEach, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const getMockedResponses = (url: string) => {
    switch (url) {
        case 'api/dashboard':
            return {
                response: ref(dashboard),
                request: asyncFunc,
                error: ref(false)
            };
        case '/api/print/participations':
            return {
                response: ref(participations),
                request: asyncFunc,
                error: ref(false)
            };
        default:
            return {};
    }
};

vi.mock('@/api/api', () => ({
    default: vi.fn((method: string, url: string) => getMockedResponses(method, url))
}));

const mockedWindowWidth = ref(900);
// eslint-disable-next-line @typescript-eslint/no-unused-vars
let mockSetTableHeadHeight = vi.fn((height: number, elementId: string) => void 0);
vi.mock('@/services/useComponentHeights', () => ({
    useComponentHeights: () => ({
        maxTableHeight: computed(() => 300),
        setTableHeadHight: mockSetTableHeadHeight,
        windowWidth: mockedWindowWidth
    })
}));

describe('Test ParticipantsTable', () => {
    beforeEach(() => {
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        mockSetTableHeadHeight = vi.fn((height: number, elementId: string) => void 0);
    });

    it('should call setTableHeight onMounted', () => {
        shallowMount(ParticipantsTable);

        expect(mockSetTableHeadHeight).toHaveBeenCalledTimes(1);
    });

    it('should call setTableHeight again after updating', async () => {
        shallowMount(ParticipantsTable);

        mockedWindowWidth.value = 1000;
        await nextTick();

        expect(mockSetTableHeadHeight).toHaveBeenCalledTimes(2);
    });

    it('should produce the correct styles string for the height', () => {
        const wrapper = shallowMount(ParticipantsTable);
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore ts does not find conditionally exported values for testing i.e. tableHeight
        expect(wrapper.vm.tableHeight).toEqual('300px');
    });

    it('should consist of a table with thead-component and tbody-component', () => {
        const wrapper = shallowMount(ParticipantsTable);

        expect(wrapper.get('table')).toBeDefined();
        expect(wrapper.getComponent(ParticipantsTableHead)).toBeDefined();
        expect(wrapper.getComponent(ParticipantsTableBody)).toBeDefined();
    });
});
