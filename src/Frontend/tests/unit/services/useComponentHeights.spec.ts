import { describe, it } from '@jest/globals';
import { mockComposableInApp } from '../test-utils';
import { useComponentHeights } from '@/services/useComponentHeights';

describe('Test the composable useComponenetHeights', () => {
    beforeEach(() => {
        global.innerWidth = 1200;
        global.innerHeight = 1200;
    });

    it('should report the correct windowWidth', () => {
        const { result } = mockComposableInApp(() => useComponentHeights());
        expect(result).toBeDefined();

        expect(result?.windowWidth.value).toBe(1200);
        result?.addWindowHeightListener();

        global.innerWidth = 800;
        global.dispatchEvent(new Event('resize'));

        expect(result?.windowWidth.value).toBe(800);
    });

    it('should compute the correct maxTableHeight', () => {
        const { result } = mockComposableInApp(() => useComponentHeights());

        const listOfHeights = [100, 125, 60, 14];

        result?.setMealListHight(listOfHeights[0], '');
        result?.setMealOverviewHeight(listOfHeights[1], '');
        result?.setNavBarHeight(listOfHeights[2], '');
        result?.setTableHeadHight(listOfHeights[3], '');

        expect(result?.maxTableHeight.value).toBe(1200 - listOfHeights.reduce((a, b) => a + b));
    });
});
