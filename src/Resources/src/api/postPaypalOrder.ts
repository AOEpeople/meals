import useApi from "@/api/api";
import { userDataStore } from "@/stores/userDataStore";

export default async function postPaypalOrder(amount: string, orderId: string) {
    const data = JSON.stringify([
        { 'name': 'ecash[profile]', 'value': userDataStore.getState().user },
        { 'name': 'ecash[orderid]', 'value': orderId },
        { 'name': 'ecash[amount]', 'value': amount }
    ]);

    const { error, request, response } = useApi<null>(
        'POST',
        '/payment/ecash/form/submit',
        'application/json',
        data
    );

    await request();

    console.log('Posted ecash payment!');

    return { error, response };
}