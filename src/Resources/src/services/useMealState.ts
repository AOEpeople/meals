import { type Meal } from '@/api/getDashboardData';
import { MealState } from '@/enums/MealState';

export default function useMealState() {
    function generateMealState(meal: Meal): MealState {
        if (isLocked(meal) === true) {
            return checkLockedMealOfferStatus(meal);
        } else {
            return checkOpenMealStatus(meal);
        }
    }

    function isLocked(meal: Meal) {
        return meal.isLocked === true && meal.isOpen === true;
    }

    function isMealOpen(meal: Meal): boolean {
        return meal.isLocked === false && meal.isOpen === true && meal.reachedLimit === false;
    }

    function checkLockedMealOfferStatus(meal: Meal): MealState {
        if (meal.isOffering === true) {
            return MealState.OFFERING;
        } else if (meal.isParticipating !== null) {
            return MealState.OFFERABLE;
        } else if (meal.hasOffers) {
            return MealState.TRADEABLE;
        } else {
            return MealState.DISABLED;
        }
    }

    function checkOpenMealStatus(meal: Meal): MealState {
        if (isMealOpen(meal) === true) {
            return MealState.OPEN;
        } else if (meal.isParticipating !== null && meal.isLocked === false && meal.isOpen === true) {
            return MealState.OFFERABLE;
        } else {
            return MealState.DISABLED;
        }
    }

    return {
        generateMealState
    };
}
