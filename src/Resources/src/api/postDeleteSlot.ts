import useApi from "./api";
import { ISuccess } from "@/api/postCreateSlot";

/**
 * Performs a POST request to delete a slot with a given ID
 * @param id The ID of the slot
 */
export default async function postDeleteSlot(id: number) {
    const { error, request, response } = useApi<ISuccess>(
        'POST',
        'api/delete-slot',
        'application/json',
        JSON.stringify({ id: id })
    );

    await request();

    return { error, response };
}