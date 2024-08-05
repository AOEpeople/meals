import { type IMessage } from '@/interfaces/IMessage';
import useApi from './api';
import { type DateTime } from './getDashboardData';

export interface EventInvitationData {
    date: DateTime;
    lockDate: DateTime;
    event: string;
}

export default async function getEventInvitationData(invitationHash: string) {
    const { error, request, response } = useApi<EventInvitationData | IMessage>(
        'GET',
        `/api/event/invitation/${invitationHash}`
    );

    await request();

    return { error, response };
}
