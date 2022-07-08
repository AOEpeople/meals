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

    public updateActiveSlotForDayById(id: number, newActiveSlot: number): void {
        let day = this.getDayById(id)
        day!.activeSlot = newActiveSlot
    }

    public getDayById(id: number): Day | null {
        let result = null

        this.state.weeks.forEach((week: Week) => {
            for (let day of week.days) {
                if (day.id === id) {
                    result = day
                    return day
                }
            }
            return null
        })

        return result
    }
}

export const dashboardStore: DashboardStore = new DashboardStore()