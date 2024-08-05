import dashboard from '../fixtures/dashboard.json';
import { useDashboardData } from '@/api/getDashboardData';
import { ref } from 'vue';
import useApi from '@/api/api';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(dashboard),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test useDashBoardData', () => {
    it('should return the correct data', async () => {
        const { dashboardData } = await useDashboardData();

        expect(useApi).toHaveBeenCalled();
        expect(dashboardData.value).toBeDefined();

        expect(dashboardData.value?.weeks['51'].days['251'].meals['15'].title.en).toBe('Innards');
        expect(dashboardData.value?.weeks['52'].startDate.date).toBe('2023-05-01 00:00:00.000000');
        expect(dashboardData.value?.weeks['52'].endDate.date).toBe('2023-05-05 23:59:59.000000');
    });

    it('should return the same object as is handed to it with the fixture', async () => {
        const { dashboardData } = await useDashboardData();

        const stringifiedJson = JSON.stringify(dashboard);
        const stringifiedApiResponse = JSON.stringify(dashboardData.value);

        expect(stringifiedApiResponse).toBe(stringifiedJson);
    });
});
