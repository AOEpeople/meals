import { type Ref, reactive, readonly, watch } from 'vue';
import getCategoriesData from '@/api/getCategoriesData';
import deleteCategory from '@/api/deleteCategory';
import postCreateCategory from '@/api/postCreateCategory';
import putCategoryUpdate from '@/api/putCategoryUpdate';
import { isMessage, type IMessage } from '@/interfaces/IMessage';
import { isResponseObjectOkay, isResponseArrayOkay } from '@/api/isResponseOkay';
import useFlashMessage from '@/services/useFlashMessage';
import { FlashMessageType } from '@/enums/FlashMessage';

export interface Category {
    id: number;
    titleDe: string;
    titleEn: string;
    slug: string;
}

interface CategoriesState {
    categories: Category[];
    isLoading: boolean;
    error: string;
}

function isCategory(category: Category): category is Category {
    return (
        category !== null &&
        category !== undefined &&
        typeof (category as Category).id === 'number' &&
        typeof (category as Category).titleDe === 'string' &&
        typeof (category as Category).titleEn === 'string' &&
        typeof (category as Category).slug === 'string' &&
        Object.keys(category).length === 4
    );
}

const TIMEOUT_PERIOD = 10000;

const CategoriesState = reactive<CategoriesState>({
    categories: [],
    isLoading: false,
    error: ''
});

const { sendFlashMessage } = useFlashMessage();

watch(
    () => CategoriesState.error,
    () => {
        if (CategoriesState.error !== '') {
            sendFlashMessage({
                type: FlashMessageType.ERROR,
                message: CategoriesState.error
            });
        }
    }
);

export function useCategories() {
    /**
     * Calls getCategories and sets isLoading to true while fetching
     */
    async function fetchCategories() {
        CategoriesState.isLoading = true;
        await getCategories();
        CategoriesState.isLoading = false;
    }

    /**
     * Calls getCategoriesData to fetch the categories and sets the categories state.
     * Retries to fetch after a timeout if there are errors.
     */
    async function getCategories() {
        const { categories, error } = await getCategoriesData();

        if (isResponseArrayOkay<Category>(error, categories, isCategory) === true) {
            CategoriesState.categories = (categories.value as Category[]);
            CategoriesState.error = '';
        } else {
            setTimeout(fetchCategories, TIMEOUT_PERIOD);
            CategoriesState.error = 'Error on getting the CategoryData';
        }
    }

    /**
     * Calls deleteCategory to delete a category and fetches the categories again
     * @param slug The slug of the category to delete
     */
    async function deleteCategoryWithSlug(slug: string) {
        const { error, response } = await deleteCategory(slug);

        if (error.value === true || isMessage(response.value) === true) {
            CategoriesState.error = (response.value as IMessage).message;
            return;
        }

        await getCategories();
        sendFlashMessage({
            type: FlashMessageType.INFO,
            message: 'category.deleted'
        });
    }

    /**
     * Calls postCreateCategory to create a new category and fetches the categories again
     * @param newCategory The category to create
     */
    async function createCategory(newCategory: Category) {
        const { error, response } = await postCreateCategory(newCategory);

        if (error.value === true || isMessage(response.value) === true) {
            CategoriesState.error = (response.value as IMessage).message;
            return;
        }

        await getCategories();
        sendFlashMessage({
            type: FlashMessageType.INFO,
            message: 'category.created'
        });
    }

    /**
     * Updates the categoryState after calling putCategoryUpdate
     * @param index The index of the category to update
     * @param titleDe The new german title
     * @param titleEn The new english title
     */
    async function editCategory(index: number, titleDe: string, titleEn: string) {
        const { error, response } = await putCategoryUpdate(CategoriesState.categories[index].slug, titleDe, titleEn);

        if (
            isMessage(response.value) === false &&
            isResponseObjectOkay<Category>(error, response as Ref<Category>) === true
        ) {
            updateCategoryState(index, response.value as Category);
            sendFlashMessage({
                type: FlashMessageType.INFO,
                message: 'category.updated'
            });
        } else if (isMessage(response.value)) {
            CategoriesState.error = response.value.message;
        } else {
            sendFlashMessage({
                type: FlashMessageType.UNKNOWN,
                message: 'Unknown Error!'
            });
        }
    }

    /**
     * Updates the category at the given index with the given category
     * @param index
     * @param category
     */
    function updateCategoryState(index: number, category: Category) {
        if (CategoriesState.categories[index].id === category.id) {
            CategoriesState.categories[index].titleDe = category.titleDe;
            CategoriesState.categories[index].titleEn = category.titleEn;
            CategoriesState.categories[index].slug = category.slug;
        }
    }

    /**
     * Resets the categoryState.
     * Used for testing.
     */
    function resetState() {
        CategoriesState.categories = [];
        CategoriesState.error = '';
        CategoriesState.isLoading = false;
    }

    /**
     * Returns the category with the given id
     */
    function getCategoryById(id: number) {
        return CategoriesState.categories.find((category) => category.id === id);
    }

    function getCategoryTitleById(id: number, locale = 'en') {
        const category = getCategoryById(id);
        if (category !== undefined && category !== null) {
            return locale === 'en' ? category.titleEn : category.titleDe;
        }
        return '';
    }

    /**
     * Returns all categoryId's where the title contains the given string
     * @param title The title to search for
     */
    function getCategoryIdsByTitle(title: string) {
        const categories = CategoriesState.categories.filter((category) => categoryContainsString(category, title));
        return categories.map((category) => category.id);
    }

    /**
     * Searches wether the category contains the given string in the title
     */
    function categoryContainsString(category: Category, searchStr: string) {
        return (
            category.titleDe.toLowerCase().includes(searchStr.toLowerCase()) ||
            category.titleEn.toLowerCase().includes(searchStr.toLowerCase())
        );
    }

    return {
        CategoriesState: readonly(CategoriesState),
        fetchCategories,
        deleteCategoryWithSlug,
        createCategory,
        editCategory,
        resetState,
        getCategoryTitleById,
        getCategoryById,
        getCategoryIdsByTitle
    };
}
