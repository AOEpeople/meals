import { Category } from "@/stores/categoriesStore";
import useApi from "./api";
import { ISuccess } from "@/interfaces/ISuccess";

/**
 * Performs a POST to create a new category
 * @param category The category to create
 */
export default async function(category: Category) {
    const { error, request, response } = useApi<ISuccess>(
        'POST',
        'api/categories',
        'application/json',
        JSON.stringify(category)
    );

    await request();

    return { error, response };
}