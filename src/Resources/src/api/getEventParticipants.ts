import useApi from './api';
import { type IMessage } from '@/interfaces/IMessage';

export default async function getEventParticipants(date: string, participationId: number) {
    const { error, response, request } = useApi<string[] | IMessage>(
        'GET',
        `api/events/participation/${date}/${participationId}`
    );

    await request();
    return { error, response };
}
