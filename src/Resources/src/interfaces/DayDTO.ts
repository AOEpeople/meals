export interface WeekDTO {
    id: number;
    notify: boolean;
    enable: boolean;
    days: DayDTO[];
}

export interface DayDTO {
    meals: MealDTO[];
    enabled: boolean;
    id: number;
}

export interface MealDTO {
    dishSlug: string | null;
    mealId: number | null;
}