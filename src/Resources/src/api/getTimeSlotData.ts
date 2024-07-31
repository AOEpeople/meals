import useApi from '@/api/api';
import { ref } from 'vue';

interface ITimeSlotResponse {
    id: number;
    title: string;
    limit: number;
    order: number;
    enabled: boolean;
    slug: string;
}

type TimeSlot = {
    title: string;
    limit: number;
    order: number;
    enabled: boolean;
    slug: string;
};

export type TimeSlots = {
    [id: number]: TimeSlot;
};

function convertResponseToTimeSlots(response: ITimeSlotResponse[]): TimeSlots {
    const timeSlots: TimeSlots = {};
    for (const timeSlotResponse of response) {
        timeSlots[timeSlotResponse.id] = {
            title: timeSlotResponse.title,
            limit: timeSlotResponse.limit,
            order: timeSlotResponse.order,
            enabled: timeSlotResponse.enabled,
            slug: timeSlotResponse.slug
        };
    }
    return timeSlots;
}

export async function useTimeSlotData() {
    const { response: timeslotsResponse, request, error } = useApi<ITimeSlotResponse[]>('GET', 'api/slots');

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    const timeslots = ref<TimeSlots>(convertResponseToTimeSlots(timeslotsResponse.value as ITimeSlotResponse[]));

    return { timeslots, error };
}
