import { vi, describe, it, expect, beforeEach} from 'vitest';
import {mount} from "@vue/test-utils";
import MealCheckbox from '@/components/dashboard/MealCheckbox.vue';
import type {Day, Meal} from "../../../src/api/getDashboardData";
import {MealState} from "../../../src/enums/MealState";

vi.mock('@/api/postJoinMeal', () => ({
    useJoinMeal: vi.fn(async () => ({
        response: { value: { slotId: 1, participantId: 123 } },
        error: { value: false }
    }))
}));

describe('Test MealCheckbox', () => {
    let meal;
    let day;
    let wrapper;
    beforeEach(() => {
        meal = {
            isParticipating: true,
            hasOffers: true,
            mealState: MealState.TRADEABLE
        } as Meal;
        day = {
            activeSlot: 1,
            events: {},
            meals: {
                '1': { dishSlug: 'combined-dish' },
                '2': { dishSlug: 'pizza' }
            }
        } as Day;
        wrapper = mount(MealCheckbox, {
            props: {
                mealID: 1,
                weekID: 1,
                dayID: 1,
                meal: meal,
                day: day
            },
            global: {
                stubs: {
                    ConfirmOfferMealPopover: {
                        props: ['show'],
                        emits: ['close', 'confirm'],
                        template: `
                          <div v-if="show" data-test="confirm-popover">
                            <button data-test="close-btn" @click="$emit('close')">close</button>
                            <button data-test="confirm-btn" @click="$emit('confirm')">confirm</button>
                          </div>
                        `
                    }
                }
            }
        });

    });
    it('should show and close confirm offer meal popover', async () => {
        await wrapper.get('[data-cy="mealCheckbox"]').trigger('click');
        expect(wrapper.find('[data-test="confirm-popover"]').exists()).toBe(true);
        await wrapper.get('[data-test="close-btn"]').trigger('click');
        expect(wrapper.find('[data-test="confirm-popover"]').exists()).toBe(false);
    });

    it('should show and accept confirm offer meal popover', async () => {
        await wrapper.get('[data-cy="mealCheckbox"]').trigger('click');
        expect(wrapper.find('[data-test="confirm-popover"]').exists()).toBe(true);
        await wrapper.get('[data-test="confirm-btn"]').trigger('click');
        expect(wrapper.find('[data-test="confirm-popover"]').exists()).toBe(false);
    });

    it('should show and accept confirm offer meal popover with combibox', async () => {
        day = {
            activeSlot: 1,
            events: {},
            meals: {
                '1': { dishSlug: 'combined-dish' },
                '2': { dishSlug: 'pizza' }
            }
        } as Day;
        wrapper = mount(MealCheckbox, {
            props: {
                mealID: 1,
                weekID: 1,
                dayID: 1,
                meal: meal,
                day: day
            },
            global: {
                stubs: {
                    ConfirmOfferMealPopover: {
                        props: ['show'],
                        emits: ['close', 'confirm'],
                        template: `
                          <div v-if="show" data-test="confirm-popover">
                            <button data-test="close-btn" @click="$emit('close')">close</button>
                            <button data-test="confirm-btn" @click="$emit('confirm')">confirm</button>
                          </div>
                        `
                    }
                }
            }
        });

        await wrapper.get('[data-cy="mealCheckbox"]').trigger('click');
        expect(wrapper.find('[data-test="confirm-popover"]').exists()).toBe(true);
        await wrapper.get('[data-test="confirm-btn"]').trigger('click');
        expect(wrapper.find('[data-test="confirm-popover"]').exists()).toBe(false);
    });
});