import useApi from "./api";
import { CreateDishVariationDTO } from "./postCreateDishVariation";
import { Dish } from "@/stores/dishesStore";

export default async function putDishVariationUpdate(slug: string, dishVariation: CreateDishVariationDTO) {
    const { error, request, response } = useApi<Dish>(
        'PUT',
        `api/dishes/variation/${slug}`,
        'application/json',
        JSON.stringify(dishVariation)
    );

    await request();

    return { error, response };
}