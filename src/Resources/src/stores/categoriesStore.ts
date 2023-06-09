import { reactive, readonly } from "vue";
import getCategoriesData from "@/api/getCategoriesData";
import deleteCategory from "@/api/deleteCategory";
import postCreateCategory from "@/api/postCreateCategory";
import putCategoryUpdate from "@/api/putCategoryUpdate";

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

    async function deleteCategoryWithSlug(slug: string) {
        const { error, response } = await deleteCategory(slug);

        if (error.value || response.value?.status !== 'success') {
            CategoriesState.error = 'Error on deleting category';
            return;
        }

        await getCategories();
    }

    async function createCategory(newCategory: Category) {
        const { error, response } = await postCreateCategory(newCategory);

        if (error.value || response.value?.status !== 'success') {
            CategoriesState.error = 'Error on creating category';
            return;
        }

        await getCategories();
    }

    async function editCategory(index: number, titleDe: string, titleEn: string) {
        const { error, response } = await putCategoryUpdate(CategoriesState.categories[index].slug, titleDe, titleEn);

        if (!error.value && response.value) {
            updateCategoryState(index, response.value);
        } else {
            CategoriesState.error = 'Error on updating category';
        }
    }

    function updateCategoryState(index: number, category: Category) {
        if (CategoriesState.categories[index].id === category.id) {
            CategoriesState.categories[index].titleDe = category.titleDe;
            CategoriesState.categories[index].titleEn = category.titleEn;
            CategoriesState.categories[index].slug = category.slug;
        }
    }

    function resetState() {
        CategoriesState.categories = [];
        CategoriesState.error = '';
        CategoriesState.isLoading = false;
    }

    function getCategoryById(id: number) {
        return CategoriesState.categories.find(category => category.id === id);
    }

    function getCategoryTitleById(id: number, locale = 'en') {
        const category = getCategoryById(id);
        if(category) {
            return locale === 'en' ? category.titleEn : category.titleDe;
        }
        return '';
    }

    return {
        CategoriesState: readonly(CategoriesState),
        fetchCategories,
        deleteCategoryWithSlug,
        createCategory,
        editCategory,
        resetState,
        getCategoryTitleById,
        getCategoryById
    }
}