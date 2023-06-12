import { ISuccess } from "@/interfaces/ISuccess";
import useApi from "./api";

export default async function deleteDishVariation(slug: string) {
    const { error, request, response } = useApi<ISuccess>(
        'DELETE',
        `api/dishes/variation/${slug}`,
        'application/json'
    );

    await request();

    return { error, response };
}