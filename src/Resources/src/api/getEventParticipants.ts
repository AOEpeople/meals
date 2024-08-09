import useApi from './api';
import { type IMessage } from '@/interfaces/IMessage';

export default async function getEventParticipants(date: string, eventId: number) {
    const { error, response, request } = useApi<string[] | IMessage>(
        'GET',
        `api/participations/event/${date}/${eventId}`
    );

    await request();

    return { error, response };
}
