import getEvents from "@/api/getEvents";
import { isResponseArrayOkay } from "@/api/isResponseOkay";
import { computed, reactive, readonly, ref, watch } from "vue";
import postCreateEvent from '@/api/postCreateEvent';
import { IMessage, isMessage } from "@/interfaces/IMessage";
import useFlashMessage from "@/services/useFlashMessage";
import { FlashMessageType } from "@/enums/FlashMessage";
import putEventUpdate from "@/api/putEventUpdate";
import deleteEvent from "@/api/deleteEvent";

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

const { sendFlashMessage } = useFlashMessage();

watch(
    () => EventsState.error,
    () => {
        if (EventsState.error !== '') {
            sendFlashMessage({
                type: FlashMessageType.ERROR,
                message: EventsState.error
            });
        }
    }
)

export function useEvents() {

    const filterStr = ref('');

    const filteredEvents = computed(() => {
        return EventsState.events.filter((event) => event.title.toLowerCase().includes(filterStr.value.toLowerCase()))
    });

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

    async function createEvent(title: string, isPublic: boolean) {
        const { error, response } = await postCreateEvent(title, isPublic);

        if (error.value === true || isMessage(response.value) === true) {
            EventsState.error = response.value?.message;
            return;
        }

        sendFlashMessage({
            type: FlashMessageType.INFO,
            message: 'events.created'
        });
        await fetchEvents();
    }

    async function updateEvent(slug: string, title: string, isPublic: boolean) {
        const { error, response } = await putEventUpdate(slug, title, isPublic);

        if (error.value === true && isMessage(response.value as IMessage) === true) {
            EventsState.error = (response.value as IMessage)?.message;
        } else if (error.value === false && isEvent(response.value as Event)) {
            const event = getEventBySlug(slug);
            event.public = (response.value as Event).public;
            event.title = (response.value as Event).title;
            event.slug = (response.value as Event).slug;
        } else {
            EventsState.error = 'An unknown error occured while updating an event!';
        }
    }

    async function deleteEventWithSlug(slug: string) {
        const { error, response } = await deleteEvent(slug);

        if (error.value === true && isMessage(response.value) === true) {
            EventsState.error = response.value?.message;
        } else {
            EventsState.events.splice(EventsState.events.findIndex((event) => event.slug === slug))
        }
    }

    function setFilter(newFilter: string) {
        filterStr.value = newFilter;
    }

    function getEventBySlug(slug: string) {
        return EventsState.events.find((event) => event.slug === slug);
    }

    return {
        EventsState: readonly(EventsState),
        filteredEvents,
        fetchEvents,
        createEvent,
        updateEvent,
        deleteEventWithSlug,
        setFilter
    }
}