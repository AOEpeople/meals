import useApi from '@/api/api';
import { type IMessage } from '@/interfaces/IMessage';

/**
 * Confirmation of a settlement request.
 * @param hash  The hash of the settlement request to confirm.
 */
export default async function postConfirmSettlement(hash: string) {
    const { error, request, response } = useApi<IMessage | null>('POST', `api/costs/settlement/confirm/${hash}`);

    await request();

    return { error, response };
}
