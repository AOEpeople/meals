import useApi from "./api";
import { ISuccess } from "@/api/postCreateSlot";

/**
 * Performs a POST request to delete a slot with a given ID
 * @param slug The identifier of the slot
 */
export default async function deleteSlot(slug: string) {
    const { error, request, response } = useApi<ISuccess>(
        'DELETE',
        `api/slots/${slug}`,
        'application/json'
    );

    await request();

    return { error, response };
}