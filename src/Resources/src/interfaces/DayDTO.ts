import { DateTime } from '@/api/getDashboardData';
import { Dictionary } from 'types/types';

export interface WeekDTO {
    id: number;
    notify: boolean;
    enabled: boolean;
    days: DayDTO[];
}

export interface DayDTO {
    meals: Dictionary<MealDTO[]>;
    enabled: boolean;
    id: number;
    event: number | null;
    date: DateTime;
    lockDate: DateTime;
}

export interface MealDTO {
    dishSlug: string | null;
    mealId: number | null;
    participationLimit: number;
}
