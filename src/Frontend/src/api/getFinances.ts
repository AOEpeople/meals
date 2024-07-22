import useApi from './api';
import { type Finances } from '@/stores/financesStore';
import moment from 'moment/moment';

export default async function getFinances(dateRange?: Date[]) {
    let requestUrl = 'api/accounting/book/finance/list';

    if (dateRange !== undefined) {
        const minDate = moment(dateRange[0]).format('YYYY-MM-DD');
        const maxDate = moment(dateRange[1]).format('YYYY-MM-DD');

        requestUrl += `/${minDate}&${maxDate}`;
    }

    const { error, response: finances, request } = useApi<Finances[]>('GET', requestUrl);

    await request();

    return { error, finances };
}
