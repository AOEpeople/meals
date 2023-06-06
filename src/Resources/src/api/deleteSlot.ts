import { ISuccess } from "@/interfaces/ISuccess";
import useApi from "./api";

/**
 * Performs a DELETE request to delete a slot with a given identifier
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