import { userDataStore } from '@/stores/userDataStore';

export default async function postPaypalOrder(amount: string, orderId: string) {
    const data = JSON.stringify([
        { name: 'ecash[profile]', value: userDataStore.getState().user },
        { name: 'ecash[orderid]', value: orderId },
        { name: 'ecash[amount]', value: amount }
    ]);

    const response = await fetch('/payment/ecash/form/submit', {
        method: 'POST',
        headers: {
            'content-type': 'application/json'
        },
        body: data
    });

    return response;
}
