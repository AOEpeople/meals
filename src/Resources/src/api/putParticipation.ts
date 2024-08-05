import type { IParticipationUpdate } from '@/stores/participationsStore';
import useApi from './api';
import type { IMessage } from '@/interfaces/IMessage';

export default async function putParticipation(mealId: number, profileId: string, combiDishes?: string[]) {
    let data;

    if (combiDishes !== undefined && combiDishes !== null && combiDishes.length === 2) {
        data = { combiDishes: combiDishes };
    } else {
        data = {};
    }

    const { error, request, response } = useApi<IMessage | IParticipationUpdate>(
        'PUT',
        `api/participation/${profileId}/${mealId}`,
        'application/json',
        JSON.stringify(data)
    );

    await request();

    return { error, response };
}
