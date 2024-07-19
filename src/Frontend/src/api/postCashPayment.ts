import useApi from '@/api/api';
import { IMessage } from '@/interfaces/IMessage';

/**
 * Performs a POST request to add a cash payment to a user.
 * @param username  The username of the user to add the cash payment to.
 * @param amount    The amount of money the user payed in cash.
 */
export default async function postCashPayment(username: string, amount: number) {
    const { error, request, response } = useApi<IMessage | number>(
        'POST',
        `api/payment/cash/${username}?amount=${amount}`
    );

    await request();

    return { error, response };
}
