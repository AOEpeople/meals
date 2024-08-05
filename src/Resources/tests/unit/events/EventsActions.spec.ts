import EventsActions from '@/components/events/EventsActions.vue';
import { Event } from '@/stores/eventsStore';
import { mount } from '@vue/test-utils';
import ActionButton from '@/components/misc/ActionButton.vue';
import { Action } from '@/enums/Actions';
import { describe, it, expect } from 'vitest';

const testEvent: Event = {
    id: 17,
    title: 'TestEvent',
    slug: 'testevent',
    public: false
};

describe('Test EventsActions', () => {
    it('should contain a EDIT ActionButton and a DELETE ActionButton', () => {
        const actions = [Action.EDIT, Action.DELETE];

        const wrapper = mount(EventsActions, {
            props: {
                event: testEvent
            }
        });

        const actionButtons = wrapper.findAllComponents(ActionButton);
        expect(actionButtons).toHaveLength(2);

        actionButtons.forEach((actionButton) => {
            expect(actions).toContain(actionButton.props('action'));
        });
    });

    it('should call deleteEventWithSlug when clicking the delete ActionButton', async () => {
        const wrapper = mount(EventsActions, {
            props: {
                event: testEvent
            }
        });

        const actionButtons = wrapper.findAllComponents(ActionButton);

        for (const actionButton of actionButtons) {
            if (actionButton.props('action') === 'DELETE') {
                await actionButton.trigger('click');
            }
        }
    });
});
