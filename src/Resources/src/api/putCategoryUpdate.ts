import { Category } from "@/stores/categoriesStore";
import useApi from "./api";

/**
 * Performs a PUT request to update a category
 * @param slug The identifier of the category to be changed
 * @param category The category as it should look after updating
 * @returns The updated category
 */
export default async function putCategoryUpdate(slug: string, titleDe: string, titleEn: string) {
    const data = {
        titleDe: titleDe,
        titleEn: titleEn
    };

    const { error, request, response } = useApi<Category>(
        'PUT',
        `api/categories/${slug}`,
        'application/json',
        JSON.stringify(data)
    );

    await request();

    return { error, response };
}