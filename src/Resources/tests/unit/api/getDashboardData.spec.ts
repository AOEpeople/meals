import { describe, expect, it } from "@jest/globals";
import dashboard from "../fixtures/dashboard.json";
import { useDashboardData, getDashboardData } from "@/api/getDashboardData";
import useApi from "@/api/api";
import { ref } from "vue";

const asyncFunc: () => Promise<void> = async () => {
    new Promise(resolve => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(dashboard),
    request: asyncFunc,
    error: ref(false)
}

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe("Test useDashBoardData", () => {
    it('should return the correct data', async () => {
        const { dashboardData } = await useDashboardData();

        expect(useApi).toHaveBeenCalled();
        expect(dashboardData.value).toBeDefined();

        expect(dashboardData.value?.weeks['51'].days['251'].meals['15'].title.en).toBe("Innards");
        expect(dashboardData.value?.weeks['52'].startDate.date).toBe("2023-05-01 00:00:00.000000");
        expect(dashboardData.value?.weeks['52'].endDate.date).toBe("2023-05-05 23:59:59.000000");
    });

    it('should return the same object as is handed to it with the fixture', async () => {
        const { dashboardData } = await useDashboardData();

        const stringifiedJson = JSON.stringify(dashboard);
        const stringifiedApiResponse = JSON.stringify(dashboardData.value);

        expect(stringifiedApiResponse).toBe(stringifiedJson);
    });
});

describe('Test getDashboardData', () => {
    it('should have the object from the fixture in the dashBoardState after fetching', async () => {
        const { dashBoardState, getDashboard } = getDashboardData();

        expect(dashBoardState.weeks).toEqual({});
        await getDashboard();
        expect(dashBoardState.weeks).not.toEqual({});

        const stringifiedJson = JSON.stringify(dashboard.weeks);
        const stringifiedDashboardState = JSON.stringify(dashBoardState.weeks);

        expect(stringifiedDashboardState).toBe(stringifiedJson);
    });

    it('should still have an false errorState after fetching', async () => {
        const { getDashboard, errorState } = getDashboardData();

        await getDashboard();
        expect(errorState.value).toBeFalsy();
    });

    it('should get the next three days for a given date', async () => {

        const correctDates = ["2023-05-01 12:00:00.000000", "2023-05-02 12:00:00.000000", "2023-05-03 12:00:00.000000"]

        const { getDashboard, getNextThreeDays } = getDashboardData();

        await getDashboard();

        const days = getNextThreeDays(new Date("2023-04-28 23:59:59.000000"));
        const dayDates = days.map((day) => day.date.date);
        for(const dateString of dayDates) {
            expect(correctDates.includes(dateString)).toBeTruthy();
        }
    })
});