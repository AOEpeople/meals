import useApi from './api';
import type { EventParticipation } from './getDashboardData';
import { type Link } from './getGuestLink';

export default async function getEventGuestLink(eventParticipation?: EventParticipation) {
    console.log('eventParticipation im getEventGuestLink:\n '+ JSON.stringify(eventParticipation));
    // hier kommt undefined beim eventParticipation?.day.date rein
    console.log('DayId:\n '+ JSON.stringify(eventParticipation?.day.dayId));
    const { error, response: link, request } = useApi<Link>('GET', `/event/invitation/${eventParticipation?.day.dayId}/${eventParticipation?.participationId}`);

    await request();

    return { link, error };
}
