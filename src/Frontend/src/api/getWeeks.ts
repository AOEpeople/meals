import useApi from '@/api/api';
import { type Week } from '@/stores/weeksStore';

export default async function getWeeksData() {
    const { response: weeks, request, error } = useApi<Week[]>('GET', 'api/weeks');

    await request();

    return { weeks, error };
}
