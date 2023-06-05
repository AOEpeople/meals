import { useCategories } from "@/stores/categoriesStore";
import success from "../fixtures/Success.json";
import Categories from "../fixtures/getCategories.json";
import { ref } from "vue";
import useApi from "@/api/api";
import { describe, it } from "@jest/globals";

const asyncFunc: () => Promise<void> = async () => {
    new Promise(resolve => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (url.includes('api/categories') && method === 'GET') {
        return {
            response: ref(Categories),
            request: asyncFunc,
            error: false
        }
    } else if (url.includes('api/categories') && (method === 'POST' || method === 'DELETE')) {
        return {
            response: ref(success),
            request: asyncFunc,
            error: ref(false)
        }
    } else if (url.includes('api/categories') && method === 'PUT') {
        return {
            response: ref(Categories[1]),
            request: asyncFunc,
            error: ref(false)
        }
    }
}

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(method, url));

describe('Test categoriesStore', () => {

    const { resetState, CategoriesState, fetchCategories } = useCategories();

    beforeEach(() => {
        resetState();
    });

    it('should not contain data before fetching', () => {
        expect(CategoriesState.categories).toEqual([]);
        expect(CategoriesState.error).toBe('');
        expect(CategoriesState.isLoading).toBeFalsy();
    });

    it('should contain a list of categories after fetching', async () => {
        await fetchCategories();

        expect(CategoriesState.categories).toEqual(Categories);
        expect(CategoriesState.error).toBe('');
        expect(CategoriesState.isLoading).toBeFalsy();
    });
})