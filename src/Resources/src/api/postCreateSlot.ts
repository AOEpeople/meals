import useApi from "./api";
import { TimeSlot } from "@/stores/timeSlotStore";

export interface ISuccess {
    status: string
}
/**
 * Performs a POST to create a new timeslot
 * @param timeSlot The timeslot to be created
 */
export default async function postCreateSlot(timeSlot: TimeSlot) {
    const { error, request, response } = useApi<ISuccess>(
        'POST',
        'api/slots',
        'application/json',
        JSON.stringify(timeSlot)
    );

    await request();

    return { error, response };
}