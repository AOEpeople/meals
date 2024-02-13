import { IMessage } from '@/interfaces/IMessage';
import useApi from './api';

export interface GuestEventData {
    firstName: string;
    lastName: string;
    company: string;
}

export default async function postJoinEventGuest(invitationHash: string, guestData: GuestEventData) {
    const { error, request, response } = useApi<IMessage | null>(
        'POST',
        `/api/event/invitation/${invitationHash}`,
        'application/json',
        JSON.stringify(guestData)
    );

    await request();

    return { error, response };
}
