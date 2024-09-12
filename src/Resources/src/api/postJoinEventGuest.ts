import { type IMessage } from '@/interfaces/IMessage';
import useApi from './api';

export interface GuestEventData {
    firstName: string;
    lastName: string;
    company: string;
}
export interface EventParticipationResponse {
    eventId: number;
    participationId: number;
    participations: number;
    isParticipating: boolean;
}

export default async function postJoinEventGuest(invitationHash: string, guestData: GuestEventData) {
    const { error, request, response } = useApi<IMessage | EventParticipationResponse>(
        'POST',
        `/api/event/invitation/${invitationHash}`,
        'application/json',
        JSON.stringify(guestData)
    );

    await request();

    return { error, response };
}
