import { IMessage } from "@/interfaces/IMessage";
import useApi from "./api";

export default async function postCreateWeek(year: number, calendarWeek: number) {
    const { error, request, response } = useApi<IMessage | null>(
        'POST',
        `api/weeks/${year}W${calendarWeek}`
    );

    await request();

    return { error, response };
}