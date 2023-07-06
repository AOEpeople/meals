import useApi from "@/api/api";
import { Dictionary } from "types/types";

export default async function getDishCount() {
    const { error, request, response } = useApi<Dictionary<number>>(
        'GET',
        `api/meals/count`
    );

    await request();

    return { error, response };
}