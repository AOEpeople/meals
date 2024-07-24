import useApi from './api';
import { type IMessage } from '@/interfaces/IMessage';

export default async function getEventParticipants(date: string) {
    const { error, response, request } = useApi<string[] | IMessage>('GET', `api/participations/event/${date}`);

    await request();

    return { error, response };
}
