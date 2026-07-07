import useApi from '@/api/api';
import { type IMessage } from '@/interfaces/IMessage';

/**
 * Performs a POST request to add a cash payment to a user.
 * @param userid  The userid of the user to add the cash payment to.
 * @param amount    The amount of money the user payed in cash.
 */
export default async function postCashPayment(userid: number, amount: number) {
    const { error, request, response } = useApi<IMessage | number>(
        'POST',
        `api/payment/cash/${userid}?amount=${amount}`
    );

    await request();

    return { error, response };
}
