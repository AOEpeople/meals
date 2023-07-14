import { Dictionary } from "types/types";
import useApi from "./api";
import { IBookedData } from "./getShowParticipations";

/**
 * Fetches a list of all participations for a passed in week
 * @param weekId ID of the week
 */
export default async function getParticipations(weekId: number) {
    const { error, response: participations, request } = useApi<Dictionary<Dictionary<IBookedData>>>(
        'GET',
        `api/participations/${weekId}`
    );

    await request();

    return { error, participations };
}