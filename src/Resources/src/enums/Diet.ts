export enum Diet {
    VEGAN = 'vegan',
    VEGETARIAN = 'vegetarian',
    MEAT = 'meat'
}

export function getDietTranslationMap() {
    const dietTranslationMap = new Map<string, string>();
    dietTranslationMap.set('vegetarisch', 'vegetarian');
    dietTranslationMap.set('vegan', 'vegan');
    dietTranslationMap.set('fleisch', 'meat');
    return dietTranslationMap;
}