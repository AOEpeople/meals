import { Dictionary } from "types/types";
import useApi from "./api";
import { IMenuParticipation } from "@/stores/participationsStore";

/**
 * Fetches a list of all participations for a passed in week
 * @param weekId ID of the week
 */
export default async function getParticipations(weekId: number) {
    const { error, response: participations, request } = useApi<Dictionary<Dictionary<IMenuParticipation>>>(
        'GET',
        `api/participations/${weekId}`
    );

    await request();

    return { error, participations };
}