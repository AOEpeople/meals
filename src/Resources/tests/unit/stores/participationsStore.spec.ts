import { useParticipations } from '@/stores/participationsStore';
import { ref } from 'vue';
import Participations from '../fixtures/menuParticipations.json';
import Update from '../fixtures/participationUpdateResponse.json';
import { type IProfile } from '@/stores/profilesStore';
import { describe, beforeEach, it, expect, vi } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (/api\/participations\/\d+$/.test(url) === true && method === 'GET') {
        return {
            response: ref(Participations),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (/api\/participation\/.*\/\d+$/.test(url) === true && method === 'PUT') {
        return {
            response: ref(Update.put),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (/api\/participation\/.*\/\d+$/.test(url) === true && method === 'DELETE') {
        return {
            response: ref(Update.delete),
            request: asyncFunc,
            error: ref(false)
        };
    }
};

vi.mock('@/api/api', () => ({
    default: vi.fn((method: string, url: string) => getMockedResponses(method, url))
}));

describe('Test participationsStore', () => {
    const {
        menuParticipationsState,
        fetchParticipations,
        addParticipantToMeal,
        removeParticipantFromMeal,
        resetStates,
        addEmptyParticipationToState,
        getProfileId,
        getParticipants,
        countBookedMeal,
        hasParticipantBookedMeal,
        hasParticipantBookedCombiDish
    } = useParticipations(1);

    beforeEach(() => {
        resetStates();
    });

    it('should not contain data before fetching', () => {
        expect(menuParticipationsState.days).toEqual({});
        expect(menuParticipationsState.isLoading).toBeFalsy();
        expect(menuParticipationsState.error).toEqual('');
    });

    it('should contain data after fetching', async () => {
        await fetchParticipations();
        expect(menuParticipationsState.days).toEqual(Participations);
        expect(menuParticipationsState.isLoading).toBeFalsy();
        expect(menuParticipationsState.error).toEqual('');
    });

    it('should add a participant to a meal', async () => {
        await fetchParticipations();
        await addParticipantToMeal(1518, 'Meals, Alice', '571');

        expect(menuParticipationsState.error).toEqual('');
        expect(menuParticipationsState.days['571']['Meals, Alice'].booked).toEqual([
            {
                mealId: 1516,
                dishId: 48,
                combinedDishes: []
            },
            {
                mealId: 1518,
                dishId: 52,
                combinedDishes: []
            }
        ]);
    });

    it('should remove a participant from a meal', async () => {
        await fetchParticipations();
        await removeParticipantFromMeal(1516, 'Meals, Alice', '571');

        expect(menuParticipationsState.error).toEqual('');
        expect(menuParticipationsState.days['571']['Meals, Alice'].booked).toEqual([
            {
                mealId: 1518,
                dishId: 52,
                combinedDishes: []
            }
        ]);
    });

    it('should add an empty participation to the state', async () => {
        await fetchParticipations();
        const profile: IProfile = {
            user: 'jane.meals',
            fullName: 'Meals, Jane',
            roles: []
        };
        addEmptyParticipationToState(profile);

        expect(menuParticipationsState.error).toEqual('');
        expect(menuParticipationsState.days['571']['Meals, Jane'].booked).toEqual({});
        expect(menuParticipationsState.days['571']['Meals, Jane'].profile).toEqual(profile.user);
    });

    it('should return a list of unique participants strings', async () => {
        await fetchParticipations();
        const participants = getParticipants();

        let count;
        for (const participant of participants) {
            count = 0;
            for (const search of participants) {
                if (participant === search) {
                    count++;
                }
            }
            expect(count).toEqual(1);
        }
    });

    it('should return the profile id', async () => {
        await fetchParticipations();
        const id = getProfileId('Meals, Alice');

        expect(id).toEqual('alice.meals');
    });

    it('should return the number of booked meals', async () => {
        await fetchParticipations();
        const count = countBookedMeal('572', 47);

        expect(count).toEqual(4);
    });

    it('should return true if a participant has booked a meal', async () => {
        await fetchParticipations();

        expect(hasParticipantBookedMeal('573', 'Meals, Alice', 1521)).toBeTruthy();
        expect(hasParticipantBookedMeal('573', 'Meals, Alice', 1522)).toBeFalsy();
    });

    it('should return true if a participant has booked a combi dish', async () => {
        await fetchParticipations();

        expect(hasParticipantBookedCombiDish('575', 'Meals, Finance', 48)).toBeTruthy();
        expect(hasParticipantBookedCombiDish('575', 'Meals, Finance', 45)).toBeTruthy();
        expect(hasParticipantBookedCombiDish('575', 'Meals, Alice', 45)).toBeFalsy();
        expect(hasParticipantBookedCombiDish('575', 'Meals, Finance', 55)).toBeFalsy();
    });
});
