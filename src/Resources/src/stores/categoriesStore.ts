import { reactive, readonly } from "vue";
import getCategoriesData from "@/api/getCategoriesData";

export interface Category {
    id: number,
    titleDe: string,
    titleEn: string,
    slug: string
}

interface CategoriesState {
    categories: Category[],
    isLoading: boolean,
    error: string
}

const TIMEOUT_PERIOD = 10000;

const CategoriesState = reactive<CategoriesState>({
    categories: [],
    isLoading: false,
    error: ''
});

export function useCategories() {

    async function fetchCategories() {
        CategoriesState.isLoading = true;
        await getCategories();
        CategoriesState.isLoading = false;
    }

    async function getCategories() {
        const { categories, error } = await getCategoriesData();
        if (!error.value && categories.value) {
            CategoriesState.categories = categories.value;
            CategoriesState.error = '';
        } else {
            setTimeout(fetchCategories, TIMEOUT_PERIOD);
            CategoriesState.error = 'Error on getting the CategoryData';
        }
    }

    return {
        CategoriesState: readonly(CategoriesState),
        fetchCategories
    }
}