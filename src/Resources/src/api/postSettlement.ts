import useApi from '@/api/api';
import { type IMessage } from '@/interfaces/IMessage';

/**
 * Sends a POST request to create a new settlement request.
 * @param userid  The user id of the user to set the settlement for.
 */
export default async function postSettlement(userid: number) {
    const { error, request, response } = useApi<IMessage | null>(
        'POST',
        'api/costs/settlement',
        'application/json',
        JSON.stringify({ userid: userid })
    );

    await request();

    return { error, response };
}
