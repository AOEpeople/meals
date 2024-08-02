import useApi from '@/api/api';
import type { IParticipationsState } from './getShowParticipations';

export default async function getPrintParticipations() {
    const { response, request, error } = useApi<IParticipationsState>('GET', '/api/print/participations');

    await request();

    return { error, response };
}
