import { type Category } from '@/stores/categoriesStore';
import useApi from './api';

/**
 * Performs a GET request to get a list of Categories
 */
export default async function getCategoriesData() {
    const { response: categories, request, error } = useApi<Category[]>('GET', 'api/categories');

    await request();

    return { categories, error };
}
