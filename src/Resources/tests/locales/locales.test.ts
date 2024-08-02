import en from '../../src/locales/en.json';
import de from '../../src/locales/de.json';
import { describe, test, expect } from 'vitest';

type NestedStrings = NestedStringArray;
type NestedStringArray = Array<NestedStrings | string>;

describe('test locales', () => {
    test('all languages should contain the same keys', () => {
        const localesDe: NestedStrings = getNestedKeys(de);
        const localesEn: NestedStrings = getNestedKeys(en);

        expect(localesEn).toStrictEqual(localesDe);
    });

    test('all languages should not contain an empty field', () => {
        expect(hasEmptyFields(de)).toBeFalsy();
        expect(hasEmptyFields(en)).toBeFalsy();
    });
});

function getNestedKeys(locales: object): NestedStrings {
    const result: NestedStrings = [];

    for (const [key, value] of Object.entries(locales)) {
        result.push(key);
        if (typeof value === 'object') {
            result.push(getNestedKeys(value));
        }
    }

    return result;
}

function hasEmptyFields(locales: object): boolean {
    for (const [, value] of Object.entries(locales)) {
        if (value === '') return true;

        if (typeof value === 'object') {
            if (hasEmptyFields(value)) return true;
        }
    }
    return false;
}
