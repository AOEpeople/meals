import {Store} from '@/store/store';
import {useDashboardData, Day, Dashboard, Week} from '@/hooks/getDashboardData';
import {useJoinMeal} from '@/hooks/postJoinMeal';

class DashboardStore extends Store<Dashboard> {

    protected data(): Dashboard {
        return {
            weeks: new Array<Week>(),
        }
    }

    async fillStore() {
        let { dashboardData } = await useDashboardData();
        if(dashboardData.value){
            this.state = dashboardData.value;
        } else {
            console.log('could not receive Transactions')
        }
    }

    async joinMeal(mealId: number, dishSlugs: Array<String>, dayId: number) {
        let data = {
            mealID: mealId,
            dishSlugs: dishSlugs,
            slotID: this.getDayById(dayId)?.activeSlot
        }

        let { response } = await useJoinMeal(JSON.stringify(data));

        console.log(response)
    }

    public updateActiveSlotForDayById(id: number, newActiveSlot: number): void {
        let day = this.getDayById(id);
        day!.activeSlot = newActiveSlot;
    }

    private getDayById(id: number): Day | null {
        this.state.weeks.forEach((week: Week) => week.days.forEach((day: Day) => {
            if(day.id === id) {
               return day;
            }
        }))

        return null;
    }
}

export const dashboardStore: DashboardStore = new DashboardStore()