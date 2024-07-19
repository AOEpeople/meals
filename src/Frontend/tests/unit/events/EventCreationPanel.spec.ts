import EventCreationPanel from '@/components/events/EventCreationPanel.vue';
import { mount } from '@vue/test-utils';

const mockCreateEvent = jest.fn();
const mockUpdateEvent = jest.fn();

jest.mock('@/stores/eventsStore', () => ({
    useEvents: () => ({
        createEvent: mockCreateEvent,
        updateEvent: mockUpdateEvent
    })
}));

describe('Test EventCreationPanel', () => {
    afterEach(() => jest.clearAllMocks());

    it('should not call createEvent if title is empty', async () => {
        const wrapper = mount(EventCreationPanel);

        await wrapper.trigger('submit.prevent');

        expect(mockCreateEvent).not.toHaveBeenCalled();
        expect(mockUpdateEvent).not.toHaveBeenCalled();
    });

    it('should call createEvent on submit when edit prop is not set', async () => {
        const wrapper = mount(EventCreationPanel);

        const inputEle = wrapper.get('#event\\.popover\\.title');
        expect((inputEle.element as HTMLInputElement).value).toBe('');

        await inputEle.setValue('TestEvent1234');
        expect((inputEle.element as HTMLInputElement).value).toBe('TestEvent1234');

        await wrapper.trigger('submit.prevent');

        expect(mockCreateEvent).toHaveBeenCalled();
    });

    it('should not call updateEvent on submit if edit prop is set to true but input is empty', async () => {
        const wrapper = mount(EventCreationPanel, {
            props: {
                edit: true
            }
        });

        await wrapper.trigger('submit.prevent');

        expect(mockCreateEvent).not.toHaveBeenCalled();
        expect(mockUpdateEvent).not.toHaveBeenCalled();
    });

    it('should call updateEvent on submit if edit prop is set to true and input is not empty', async () => {
        const wrapper = mount(EventCreationPanel, {
            props: {
                edit: true
            }
        });

        const inputEle = wrapper.get('#event\\.popover\\.title');
        expect((inputEle.element as HTMLInputElement).value).toBe('');

        await inputEle.setValue('TestEvent1234');
        expect((inputEle.element as HTMLInputElement).value).toBe('TestEvent1234');

        await wrapper.trigger('submit.prevent');

        expect(mockCreateEvent).not.toHaveBeenCalled();
        expect(mockUpdateEvent).not.toHaveBeenCalled();
    });
});
