import useApi from '@/api/api';
import { IMessage } from '@/interfaces/IMessage';

export default async function postCashPayment(username: string, amount: number) {
    const { error, request, response } = useApi<IMessage | number>(
        'POST',
        `api/payment/cash/${username}?amount=${amount}`
    );

    await request();

    return { error, response };
}