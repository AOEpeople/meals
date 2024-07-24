import { type IParticipationUpdate } from '@/stores/participationsStore';
import useApi from './api';
import { type IMessage } from '@/interfaces/IMessage';

export default async function deleteParticipation(mealId: number, profileId: string) {
    const { error, request, response } = useApi<IMessage | IParticipationUpdate>(
        'DELETE',
        `api/participation/${profileId}/${mealId}`
    );

    await request();

    return { error, response };
}
