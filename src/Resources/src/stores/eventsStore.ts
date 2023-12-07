import getEvents from "@/api/getEvents";
import { isResponseArrayOkay } from "@/api/isResponseOkay";
import { reactive, readonly } from "vue";

export interface Event {
    id: number,
    title: string,
    slug: string,
    public: boolean
}

interface EventsState {
    events: Event[],
    error: string,
    isLoading: boolean
}

function isEvent(event: Event): event is Event {
    return (
        event !== null &&
        event !== undefined &&
        typeof (event as Event).id === 'number' &&
        typeof (event as Event).title === 'string' &&
        typeof (event as Event).slug === 'string' &&
        typeof (event as Event).public === 'boolean'
    )
}

const TIMEOUT_PERIOD = 10000;

const EventsState = reactive<EventsState>({
    events: [],
    error: "",
    isLoading: false
});

export function useEvents() {

    async function fetchEvents() {
        EventsState.isLoading = true;

        const { error, events } = await getEvents();
        if (isResponseArrayOkay<Event>(error, events, isEvent) === true) {
            EventsState.events = events.value;
            EventsState.error = '';
        } else {
            EventsState.error = 'Error on fetching events';
            setTimeout(fetchEvents, TIMEOUT_PERIOD);
        }

        EventsState.isLoading = false;
    }

    return {
        EventsState: readonly(EventsState),
        fetchEvents
    }
}