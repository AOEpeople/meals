import { type IMessage } from '@/interfaces/IMessage';
import useApi from './api';

/**
 * Sends a POST request to hide a user from the costs page.
 * @param username  The username of the user to hide.
 */
export default async function postHideUser(username: string) {
    const { error, request, response } = useApi<IMessage | null>('POST', `api/costs/hideuser/${username}`);

    await request();

    return { error, response };
}
