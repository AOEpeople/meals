import { Category } from '@/stores/categoriesStore';
import useApi from './api';
import { IMessage } from '@/interfaces/IMessage';

/**
 * Performs a POST to create a new category
 * @param category The category to create
 */
export default async function postCreateCategory(category: Category) {
    const { error, request, response } = useApi<IMessage | null>(
        'POST',
        'api/categories',
        'application/json',
        JSON.stringify(category)
    );

    await request();

    return { error, response };
}
