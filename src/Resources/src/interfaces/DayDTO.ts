import { type DateTime } from '@/api/getDashboardData';
import { type Dictionary } from '@/types/types';

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
    events: Dictionary<EventDTO[]>;
    date: DateTime;
    lockDate: DateTime;
}

export interface MealDTO {
    dishSlug: string | null;
    mealId: number | null;
    participationLimit: number;
}

export interface EventDTO {
    eventSlug: string | null;
    eventId: number | null;
    eventTitle: string | null;
    isPublic: boolean | null;
}
