import { type IMessage } from '@/interfaces/IMessage';
import useApi from './api';

/**
 * Sends a POST request to hide a user from the costs page.
 * @param userid  The user id of the user to hide.
 */
export default async function postHideUser(userid: number) {
    const { error, request, response } = useApi<IMessage | null>(
        'POST',
        'api/costs/hideuser',
        'application/json',
        JSON.stringify({ userid: userid })
    );

    await request();

    return { error, response };
}
