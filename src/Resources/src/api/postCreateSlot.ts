import useApi from "./api";
import { TimeSlot } from "@/stores/timeSlotStore";

export interface ISuccess {
    status: string
}
/**
 * Performs a POST to create a new timeslot
 * @param timeSlot The timeslot to be created
 */
export default async function createSlot(timeSlot: TimeSlot) {
    const { error, request, response } = useApi<ISuccess>(
        'POST',
        'api/slot/create',
        'application/json',
        JSON.stringify(timeSlot)
    );

    await request();

    return { error, response };
}