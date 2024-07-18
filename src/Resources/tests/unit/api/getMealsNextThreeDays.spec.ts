import { ref } from 'vue';
import nextThreeDays from '../fixtures/nextThreeDays.json';
import useApi from '@/api/api';
import { describe, expect } from '@jest/globals';
import { IDay, IDish, getNextThreeDays } from '@/api/getMealsNextThreeDays';
import { Diet } from '@/enums/Diet';

const iDishesEn: IDish[] = [
    {
        title: 'Limbs oh la la la (oven backed) + Finger food with a slimy sweet and sour sauce',
        diet: Diet.MEAT
    },
    {
        title: 'Tasty Worms',
        diet: Diet.MEAT
    },
    {
        title: 'Combined Dish',
        diet: Diet.MEAT
    }
];

const iDishesDe: IDish[] = [
    {
        title: 'Limbs oh la la la (Ofen gebacken) + Finger food mit einer schlammigen Süß-Sauer-Soße',
        diet: Diet.MEAT
    },
    {
        title: 'Tasty Worms DE',
        diet: Diet.MEAT
    },
    {
        title: 'Kombi-Gericht',
        diet: Diet.MEAT
    }
];

const iDishesTwoEn: IDish[] = [
    {
        title: 'Braaaaaiiinnnzzzzzz DE',
        diet: Diet.MEAT
    },
    {
        title: 'Fish (so juicy sweat) DE',
        diet: Diet.MEAT
    }
];

const iDishesTwoDe: IDish[] = [
    {
        title: 'Braaaaaiiinnnzzzzzz',
        diet: Diet.MEAT
    },
    {
        title: 'Fish (so juicy sweat)',
        diet: Diet.MEAT
    }
];

const testDays: IDay[] = [
    {
        date: new Date('2023-05-10T00:00:00.000Z'),
        de: iDishesDe,
        en: iDishesEn
    },
    {
        date: new Date('2023-05-11T00:00:00.000Z'),
        de: iDishesTwoDe,
        en: iDishesTwoEn
    },
    {
        date: new Date('2023-05-12T00:00:00.000Z'),
        de: [iDishesTwoDe[0], iDishesDe[0]],
        en: [iDishesTwoEn[0], iDishesEn[0]]
    }
];

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(nextThreeDays.dataOne),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test getMealsNextThreeDays', () => {
    const { resetState } = getNextThreeDays();

    beforeEach(() => {
        mockedReturnValue.error.value = false;
        mockedReturnValue.response.value = nextThreeDays.dataOne;
        resetState();
    });

    it('should have no error in the state before and after fetching', async () => {
        const { nextThreeDaysState, fetchNextThreeDays } = getNextThreeDays();

        expect(nextThreeDaysState.error).toBeFalsy();
        await fetchNextThreeDays();
        expect(nextThreeDaysState.error).toBeFalsy();
    });

    it('should have no error in the state before and an error after fetching', async () => {
        const { nextThreeDaysState, fetchNextThreeDays } = getNextThreeDays();

        expect(nextThreeDaysState.error).toBeFalsy();
        mockedReturnValue.error.value = true;
        await fetchNextThreeDays();
        expect(nextThreeDaysState.error).toBeTruthy();
    });

    it('should have three days in the state after fetching', async () => {
        const { nextThreeDaysState, fetchNextThreeDays } = getNextThreeDays();

        expect(nextThreeDaysState.days).toEqual([]);
        await fetchNextThreeDays();

        expect(nextThreeDaysState.days).not.toEqual([]);
        expect(nextThreeDaysState.days).toHaveLength(3);
    });

    it('should have the transformed fixture in the state after fetching', async () => {
        const { nextThreeDaysState, fetchNextThreeDays } = getNextThreeDays();

        await fetchNextThreeDays();

        expect(nextThreeDaysState.days).toEqual(testDays);
    });
});
