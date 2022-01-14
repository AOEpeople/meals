export enum TooltipLabel {
    OFFERED_MEAL = "offeredMeal",
    AVAILABLE_MEAL = "availableMeal"
}

export interface Tooltip {
    offeredMeal: string
    availableMeal: string
}

export interface LocalizedLabels {
    tooltip: Tooltip
}

export interface Labels {
    en: LocalizedLabels,
    de: LocalizedLabels
}