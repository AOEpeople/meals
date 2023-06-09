import { CreateDishDTO } from "@/api/postCreateDish";
import { Dish } from "@/stores/dishesStore";
import useApi from "./api";

export default async function putDishUpdate(slug: string, dish: CreateDishDTO) {
    const { error, request, response } = useApi<Dish>(
        'PUT',
        `api/dishes/${slug}`,
        'application/json',
        JSON.stringify(dish)
    );

    await request();

    return { error, response };
}