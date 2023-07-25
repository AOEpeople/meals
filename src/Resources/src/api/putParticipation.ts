import { IParticipationUpdate } from "@/stores/participationsStore";
import useApi from "./api";
import { IMessage } from "@/interfaces/IMessage";

export default async function putParticipation(mealId: number, profileId: string) {
    const { error, request, response } = useApi<IMessage | IParticipationUpdate>(
        'PUT',
        `api/participation/${profileId}/${mealId}`
    );

    await request();

    return { error, response };
}