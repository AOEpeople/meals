import useApi from './api';
import { type IMessage } from '@/interfaces/IMessage';
import { type EventParticipationResponse } from './postJoinEvent';

export async function deleteLeaveEvent(date: string) {
    const { response, request, error } = useApi<IMessage | EventParticipationResponse>(
        'DELETE',
        `api/events/participation/${date}`
    );

    await request();

    return { error, response };
}
