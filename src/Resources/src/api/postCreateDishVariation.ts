import { ISuccess } from "@/interfaces/ISuccess";
import useApi from "./api";

export interface CreateDishVariationDTO {
    titleDe?: string,
    titleEn?: string
}

export default async function postCreateDishVariation(dishVariation: CreateDishVariationDTO, parentSlug: string) {
    const { error, response, request } = useApi<ISuccess>(
        'POST',
        `api/dishes/${parentSlug}/variation`,
        'application/json',
        JSON.stringify(dishVariation)
    );

    await request();

    return { error, response };
}