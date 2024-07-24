import useApi from './api';
import { type Link } from './getGuestLink';

export default async function getEventGuestLink(dayId: string) {
    const { error, response: link, request } = useApi<Link>('GET', `/event/invitation/${dayId}`);

    await request();

    return { link, error };
}
