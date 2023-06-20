import { ISuccess } from "@/interfaces/ISuccess";
import useApi from "./api";

export default async function postCreateWeek(year: number, calendarWeek: number) {
    const { error, request, response } = useApi<ISuccess>(
        'POST',
        `api/weeks/${year}W${calendarWeek}`
    );

    await request();

    return { error, response };
}