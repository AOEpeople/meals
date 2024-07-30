import postCreateWeek from '@/api/postCreateWeek';
import { ref } from 'vue';
import { MealDTO, DayDTO, WeekDTO } from '@/interfaces/DayDTO';
import { vi, describe, it, expect } from 'vitest';

const testMeal: MealDTO = {
    dishSlug: 'test',
    mealId: 0,
    participationLimit: 0
};

const testDay: DayDTO = {
    meals: { 0: [testMeal] },
    enabled: false,
    id: 0,
    events: null,
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

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test postCreateWeek', () => {
    it('should create a new week', async () => {
        const { error, response } = await postCreateWeek(2023, 20, testWeek);

        expect(error.value).toBeFalsy();
        expect(response.value).toBe(testWeek.id);
    });
});
