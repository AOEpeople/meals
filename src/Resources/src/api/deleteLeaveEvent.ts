import useApi from './api';
import { type IMessage } from '@/interfaces/IMessage';
import { type EventParticipationResponse } from './postJoinEvent';

export async function deleteLeaveEvent(date: string, eventId: number) {
    const { response, request, error } = useApi<IMessage | EventParticipationResponse>(
        'DELETE',
        `api/events/participation/${date}/${eventId}`
    );

    await request();

    return { error, response };
}
