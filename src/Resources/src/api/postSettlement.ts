import useApi from '@/api/api';
import { type IMessage } from '@/interfaces/IMessage';

/**
 * Sends a POST request to create a new settlement request.
 * @param username  The username of the user to set the settlement for.
 */
export default async function postSettlement(username: string) {
    const { error, request, response } = useApi<IMessage | null>(
        'POST',
        'api/costs/settlement',
        'application/json',
        JSON.stringify({ username: username })
    );

    await request();

    return { error, response };
}
