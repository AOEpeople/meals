import useApi from "@/api/api";
import { WeekDTO } from "@/interfaces/DayDTO";
import { ISuccess } from "@/interfaces/ISuccess";

export default async function putWeekUpdate(week: WeekDTO) {
    const { error, request, response } = useApi<ISuccess>(
        'PUT',
        `api/menu/${week.id}`,
        'application/json',
        JSON.stringify(week)
    );

    await request();

    return { error, response };
}