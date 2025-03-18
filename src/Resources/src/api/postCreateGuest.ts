import type { IProfile } from '@/stores/profilesStore';
import useApi from './api';
import { type IMessage } from '@/interfaces/IMessage';

export interface Guest {
    firstName: string;
    lastName: string;
    company: string;
}

export default async function postCreateGuest(guest: Guest) {
    const { error, request, response } = useApi<IMessage | IProfile>(
        'POST',
        'api/guest',
        'application/json',
        JSON.stringify(guest)
    );

    await request();

    return { error, response };
}
