import { DateTime } from "@/api/getDashboardData";
import { Dictionary } from "types/types";


export interface WeekDTO {
    id: number;
    notify: boolean;
    enable: boolean;
    days: DayDTO[];
}

export interface DayDTO {
    meals: Dictionary<MealDTO[]>;
    enabled: boolean;
    id: number;
    date: DateTime
}

export interface MealDTO {
    dishSlug: string | null;
    mealId: number | null;
}