import { ref } from 'vue';
import nextThreeDays from '../fixtures/nextThreeDays.json';
import useApi from '@/api/api';
import { describe, expect } from '@jest/globals';
import { IDay, getNextThreeDays } from '@/api/getMealsNextThreeDays';

const testDays: IDay[] = [
    {
        date: new Date('2023-05-10T00:00:00.000Z'),
        de: [
            'Limbs oh la la la (Ofen gebacken) + Finger food mit einer schlammigen Süß-Sauer-Soße',
            'Tasty Worms DE',
            'Kombi-Gericht'
        ],
        en: [
            'Limbs oh la la la (oven backed) + Finger food with a slimy sweet and sour sauce',
            'Tasty Worms',
            'Combined Dish'
        ]
    },
    {
        date: new Date('2023-05-11T00:00:00.000Z'),
        de: ['Braaaaaiiinnnzzzzzz DE', 'Fish (so juicy sweat) DE'],
        en: ['Braaaaaiiinnnzzzzzz', 'Fish (so juicy sweat)']
    },
    {
        date: new Date('2023-05-12T00:00:00.000Z'),
        de: [
            'Braaaaaiiinnnzzzzzz DE',
            'Limbs oh la la la (Ofen gebacken) + Finger food mit einer schlammigen Süß-Sauer-Soße'
        ],
        en: ['Braaaaaiiinnnzzzzzz', 'Limbs oh la la la (oven backed) + Finger food with a slimy sweet and sour sauce']
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
