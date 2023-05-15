import ParticipantsTable from "@/components/participations/ParticipationsTable.vue";
import ParticipantsTableHead from "@/components/participations/ParticipantsTableHead.vue";
import ParticipantsTableBody from "@/components/participations/ParticipantsTableBody.vue";
import { describe, it, beforeEach } from "@jest/globals";
import dashboard from "../fixtures/dashboard.json";
import participations from "../fixtures/participations.json";
import { shallowMount } from "@vue/test-utils";
import { computed, nextTick, ref } from "vue";
import useApi from "@/api/api";

const asyncFunc: () => Promise<void> = async () => {
    new Promise(resolve => resolve(undefined));
};

const getMockedResponses = (url: string) => {
    switch(url) {
        case "api/dashboard":
            return {
                response: ref(dashboard),
                request: asyncFunc,
                error: ref(false)
            };
        case "/api/print/participations":
            return {
                response: ref(participations),
                request: asyncFunc,
                error: ref(false)
            }
        default:
            return {}
    }
}

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(url));

jest.mock("vue-i18n", () => ({
    useI18n: () => ({
        t: (key: string) => key,
        locale: computed(() => 'en')
    })
}));


const mockedWindowWidth = ref(900);
let mockSetTableHeadHeight = jest.fn((height: number, elementId: string) => void 0);
jest.mock('@/services/useComponentHeights', () => ({
    useComponentHeights: () => ({
        maxTableHeight: computed(() => 300),
        setTableHeadHight: mockSetTableHeadHeight,
        windowWidth: mockedWindowWidth
    })
}));

describe('Test ParticipantsTable', () => {

    beforeEach(() => {
        mockSetTableHeadHeight = jest.fn((height: number, elementId: string) => void 0);
    })

    it('should call setTableHeight onMounted', () => {
        const wrapper = shallowMount(ParticipantsTable);

        expect(mockSetTableHeadHeight).toHaveBeenCalledTimes(1);
    });

    it('should call setTableHeight again after updating', async () => {
        const wrapper = shallowMount(ParticipantsTable);

        mockedWindowWidth.value = 1000;
        await nextTick();

        expect(mockSetTableHeadHeight).toHaveBeenCalledTimes(2);
    });

    it('should produce the correct styles string for the height', () => {
        const wrapper = shallowMount(ParticipantsTable);
        expect(wrapper.vm.tableHeight).toEqual('300px');
    });

    it('should consist of a table with thead-component and tbody-component', () => {
        const wrapper = shallowMount(ParticipantsTable);

        expect(wrapper.get('table')).toBeDefined();
        expect(wrapper.getComponent(ParticipantsTableHead)).toBeDefined();
        expect(wrapper.getComponent(ParticipantsTableBody)).toBeDefined();
    });
});