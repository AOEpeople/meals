import { dashboardStore } from '@/stores/dashboardStore';
import dashboardData from '../fixtures/getDashboard.json';
import { ref } from 'vue';
import { vi, describe, beforeEach, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (url.includes('api/dashboard') && method === 'GET') {
        return {
            response: ref(dashboardData),
            request: asyncFunc,
            error: ref(false)
        };
    }
};

vi.mock('@/api/api', () => ({
    default: vi.fn((method: string, url: string) => getMockedResponses(method, url))
}));

describe('Test dashboardStore', () => {
    beforeEach(() => {
        dashboardStore.resetState();
        dashboardStore.fillStore();
    });

    it('should fill the store with data', async () => {
        expect(dashboardStore.getWeeks()).toEqual(dashboardData.weeks);
    });

    it('should return the correct week', () => {
        const weekIds = Object.keys(dashboardData.weeks);
        for (let i = 0; i < Object.keys(dashboardData.weeks).length; i++) {
            expect(dashboardStore.getWeek(weekIds[i])).toEqual(dashboardData.weeks[weekIds[i]]);
        }
    });

    it('should return the correct day', () => {
        const weekIds = Object.keys(dashboardData.weeks);
        for (let i = 0; i < Object.keys(dashboardData.weeks).length; i++) {
            const dayIds = Object.keys(dashboardData.weeks[weekIds[i]].days);
            for (let j = 0; j < Object.keys(dashboardData.weeks[weekIds[i]].days).length; j++) {
                expect(dashboardStore.getDay(weekIds[i], dayIds[j])).toEqual(
                    dashboardData.weeks[weekIds[i]].days[dayIds[j]]
                );
            }
        }
    });

    it('should return all the days of the week', () => {
        const weekIds = Object.keys(dashboardData.weeks);
        for (let i = 0; i < Object.keys(dashboardData.weeks).length; i++) {
            expect(dashboardStore.getDays(weekIds[i])).toEqual(dashboardData.weeks[weekIds[i]].days);
        }
    });

    it('should return the correct slot', () => {
        const weekId = '7219';
        const dayId = '36092';
        const slotId = '1024';
        const slot = dashboardData.weeks[weekId].days[dayId].slots[slotId];
        expect(dashboardStore.getSlot(weekId, dayId, slotId)).toEqual(slot);
    });

    it('should return the correct meal', () => {
        const weekId = '7219';
        const dayId = '36092';
        const mealId = '102502';
        const meal = dashboardData.weeks[weekId].days[dayId].meals[mealId];
        expect(dashboardStore.getMeal(weekId, dayId, mealId)).toEqual(meal);
    });

    it('should return the correct meals', () => {
        const weekId = '7219';
        const dayId = '36092';
        const meals = dashboardData.weeks[weekId].days[dayId].meals;
        expect(dashboardStore.getMeals(weekId, dayId)).toEqual(meals);
    });

    it('should return the correct variation', () => {
        const weekId = '7219';
        const dayId = '36093';
        const parentId = '3079';
        const mealId = '102505';
        const variation = dashboardData.weeks[weekId].days[dayId].meals[parentId].variations[mealId];
        expect(dashboardStore.getVariation(weekId, dayId, parentId, mealId)).toEqual(variation);
    });

    it('should update the participation count of an event', () => {
        const weekId = 7218;
        const dayId = 36088;
        const eventId = 537;
        expect(dashboardStore.getDay(weekId, dayId).event.participations).toEqual(0);
        expect(dashboardStore.getDay(weekId, dayId).event.eventId).toEqual(eventId);
        const participations = 17;
        dashboardStore.updateEventParticipation(weekId, dayId, eventId, participations);
        expect(dashboardStore.getDay(weekId, dayId).event.participations).toEqual(participations);
    });
});
