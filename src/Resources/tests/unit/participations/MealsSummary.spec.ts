import { Day } from '@/api/getDashboardData';
import MealsSummary from '@/components/participations/MealsSummary.vue';
import { describe, it } from '@jest/globals';
import { mount } from '@vue/test-utils';

const dayOne: Day = {
    date: {
        date: "2023-05-01 12:00:00.000000",
        timezone_type: 3,
        timezone: 'Europe/Berlin'
    },
    isLocked: false,
    activeSlot: 1,
    meals: {
        111: {
           title: {
            en: 'Test111',
            de: 'Test111'
           },
           description: null,
           dishSlug: 'test111',
           price: 3.6,
           limit: 0,
           reachedLimit: false,
           isOpen: true,
           isLocked: false,
           isNew: false,
           parentId: null,
           participations: 12,
           isParticipating: null,
           hasOffers: true,
           isOffering: false,
           mealState: 'disabled',
           variations: null
        },
        112: {
            title: {
                en: 'Test112',
                de: 'Test112'
               },
               description: null,
               dishSlug: 'test112',
               price: 3.6,
               limit: 0,
               reachedLimit: false,
               isOpen: true,
               isLocked: false,
               isNew: false,
               parentId: null,
               participations: 12,
               isParticipating: null,
               hasOffers: true,
               isOffering: false,
               mealState: 'disabled',
               variations: null
        },
        113: {
            title: {
                en: 'Test113',
                de: 'Test113'
               },
               description: null,
               dishSlug: 'test113',
               price: 3.6,
               limit: 0,
               reachedLimit: false,
               isOpen: true,
               isLocked: false,
               isNew: false,
               parentId: null,
               participations: 12,
               isParticipating: null,
               hasOffers: true,
               isOffering: false,
               mealState: 'disabled',
               variations: null
        }
    },
    slots: {},
    slotsEnabled: true
}

const dayTwo: Day = {
    date: {
        date: "2023-05-03 12:00:00.000000",
        timezone_type: 3,
        timezone: 'Europe/Berlin'
    },
    isLocked: false,
    activeSlot: 1,
    meals: {
        111: {
            title: {
             en: 'Test111',
             de: 'Test111'
            },
            description: null,
            dishSlug: 'test111',
            price: 3.6,
            limit: 0,
            reachedLimit: false,
            isOpen: true,
            isLocked: false,
            isNew: false,
            parentId: null,
            participations: 12,
            isParticipating: null,
            hasOffers: true,
            isOffering: false,
            mealState: 'disabled',
            variations: null
         },
         112: {
             title: {
                 en: 'Test112',
                 de: 'Test112'
                },
                description: null,
                dishSlug: 'test112',
                price: 3.6,
                limit: 0,
                reachedLimit: false,
                isOpen: true,
                isLocked: false,
                isNew: false,
                parentId: null,
                participations: 12,
                isParticipating: null,
                hasOffers: true,
                isOffering: false,
                mealState: 'disabled',
                variations: null
         }
    },
    slots: {},
    slotsEnabled: false
}

const dayThree: Day = {
    date: {
        date: "2023-05-05 12:00:00.000000",
        timezone_type: 3,
        timezone: 'Europe/Berlin'
    },
    isLocked: false,
    activeSlot: 1,
    meals: {
        111: {
            title: {
             en: 'Test111',
             de: 'Test111'
            },
            description: null,
            dishSlug: 'test111',
            price: 3.6,
            limit: 0,
            reachedLimit: false,
            isOpen: true,
            isLocked: false,
            isNew: false,
            parentId: null,
            participations: 12,
            isParticipating: null,
            hasOffers: true,
            isOffering: false,
            mealState: 'disabled',
            variations: null
        }
    },
    slots: {},
    slotsEnabled: false
}

const dayFour: Day = {
    date: {
        date: "2023-05-02 12:00:00.000000",
        timezone_type: 3,
        timezone: 'Europe/Berlin'
    },
    isLocked: false,
    activeSlot: 1,
    meals: {},
    slots: {},
    slotsEnabled: false
}

jest.mock("vue-i18n", () => ({
    useI18n: () => ({
        t: (key: string) => key,
        locale: 'en'
    })
}));

describe('Test MealsSummary', () => {
    it('should display three meals and no empty rows', () => {
        const wrapper = mount(MealsSummary, {
            props: {
                day: dayOne
            }
        });
        const testMeals = ['Test111', 'Test112', 'Test113'];

        expect(wrapper.findAll('td')).toHaveLength(3);
        expect(wrapper.findAll('th')).toHaveLength(1);

        expect(wrapper.find('th').text()).toBe('Monday');
        for(const td of wrapper.findAll('td')) {
            expect(testMeals.includes(td.text())).toBe(true);
        }
    });

    it('should display two meals and an empty row', () => {
        const wrapper = mount(MealsSummary, {
            props: {
                day: dayTwo
            }
        });
        const testMeals = ['Test111', 'Test112', ''];

        expect(wrapper.findAll('td')).toHaveLength(3);
        expect(wrapper.findAll('th')).toHaveLength(1);

        expect(wrapper.find('th').text()).toBe('Wednesday');
        for(const td of wrapper.findAll('td')) {
            expect(testMeals.includes(td.text())).toBe(true);
        }
    });

    it('should display one meal and two empty rows', () => {
        const wrapper = mount(MealsSummary, {
            props: {
                day: dayThree
            }
        });
        const testMeals = ['Test111', ''];

        expect(wrapper.findAll('td')).toHaveLength(3);
        expect(wrapper.findAll('th')).toHaveLength(1);

        expect(wrapper.find('th').text()).toBe('Friday');
        for(const td of wrapper.findAll('td')) {
            expect(testMeals.includes(td.text())).toBe(true);
        }
    });

    it('should display no meal and three empty rows', () => {
        const wrapper = mount(MealsSummary, {
            props: {
                day: dayFour
            }
        });

        expect(wrapper.findAll('td')).toHaveLength(3);
        expect(wrapper.findAll('th')).toHaveLength(1);

        expect(wrapper.find('th').text()).toBe('Tuesday');
        for(const td of wrapper.findAll('td')) {
            expect(td.text()).toBe('');
        }
    });
});