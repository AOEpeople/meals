import postCreateWeek from '@/api/postCreateWeek';
import { ref } from 'vue';
import useApi from '@/api/api';
import { MealDTO, DayDTO, WeekDTO } from '@/interfaces/DayDTO';

const testMeal: MealDTO = {
    dishSlug: 'test',
    mealId: 0,
    participationLimit: 0
};

const testDay: DayDTO = {
    meals: { 0: [testMeal] },
    enabled: false,
    id: 0,
    event: null,
    date: {
        date: '',
        timezone_type: 0,
        timezone: ''
    },
    lockDate: {
        date: '',
        timezone_type: 0,
        timezone: ''
    }
};

const testWeek: WeekDTO = {
    id: 17,
    notify: false,
    enabled: false,
    days: [testDay]
};

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(testWeek.id),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test postCreateWeek', () => {
    it('should create a new week', async () => {
        const { error, response } = await postCreateWeek(2023, 20, testWeek);

        expect(error.value).toBeFalsy();
        expect(response.value).toBe(testWeek.id);
    });
});
