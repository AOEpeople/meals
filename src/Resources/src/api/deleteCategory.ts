import { type IMessage } from '@/interfaces/IMessage';
import useApi from './api';

/**
 * Performs a DELETE request to delete a category with a given identifier
 * @param slug The identifier of the category
 */
export default async function deleteCategory(slug: string) {
    const { error, request, response } = useApi<IMessage | null>('DELETE', `api/categories/${slug}`);

    await request();

    return { error, response };
}
