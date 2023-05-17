import useApi from "./api";
import { TimeSlot } from "@/stores/timeSlotStore";

interface ISuccess {
    status: string
}

export default async function createSlot(timeSlot: TimeSlot) {
    const { error, request, response } = useApi<ISuccess>(
        "POST",
        "api/create-slot",
        "application/json",
        JSON.stringify(timeSlot)
    );

    await request();

    return { error, response };
}