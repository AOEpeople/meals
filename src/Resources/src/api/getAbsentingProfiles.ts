import useApi from '@/api/api';
import { type IProfile } from '@/stores/profilesStore';

export default async function getAbsentingProfiles(weekId: number) {
    const { error, request, response } = useApi<IProfile[]>('GET', `api/participations/${weekId}/abstaining`);

    await request();

    return { error, response };
}
