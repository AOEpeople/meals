import {Store} from '@/store/store';
import {useDashboardData, Day, Dashboard, Week} from '@/hooks/getDashboardData';

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

        this.configureMealUpdateHandlers()
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

    /**
     * Configure handlers to process meal push notifications.
     */
    private configureMealUpdateHandlers(): void {
        const eventSrc = new EventSource('https://meals.test:8081/.well-known/mercure?topic=participation-updates&topic=meal-offer-updates&topic=slot-allocation-updates', { withCredentials: true })

        // @ts-ignore
        eventSrc.addEventListener('participationUpdate', (event: MessageEvent) => {
            this.handleParticipationUpdate(JSON.parse(event.data))
        })
        // @ts-ignore
        eventSrc.addEventListener('mealOfferUpdate', (event: MessageEvent) => {
            this.handleMealOfferUpdate(JSON.parse(event.data))
        })
        // @ts-ignore
        eventSrc.addEventListener('slotAllocationUpdate', (event: MessageEvent) => {
            this.handleSlotAllocationUpdate(JSON.parse(event.data))
        })
    }

    private handleParticipationUpdate(data: any): void {

    }
    private handleMealOfferUpdate(data: any): void {

    }
    private handleSlotAllocationUpdate(data: any): void {

    }
}

export const dashboardStore: DashboardStore = new DashboardStore()