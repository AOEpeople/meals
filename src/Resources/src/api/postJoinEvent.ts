import useApi from '@/api/api';
import { IMessage } from '@/interfaces/IMessage';

export interface EventParticipationResponse {
    eventId: number;
    participationId: number;
    participations: number;
    isParticipating: boolean;
}

export default async function postJoinEvent(date: string) {
    const { error, request, response } = useApi<IMessage | EventParticipationResponse>(
        'POST',
        `api/events/participation/${date}`
    );

    await request();

    return { error, response };
}
