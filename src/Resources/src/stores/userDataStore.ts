import {Store} from "@/stores/store";
import {useUserData, UserData} from "@/api/getUserData";
import router from "@/router";
import {useI18n} from "vue-i18n";

class UserDataStore extends Store<UserData> {
    protected data(): UserData {
        return {
            roles: [],
            user: '',
            balance: 0
        };
    }

    async fillStore() {
        await this.writeUserData()
    }

    private async writeUserData() {
        const {userData, error} = await useUserData()
        if (error.value === true) {
            console.warn("couldn't receive User Data!")
            return;
        }
        if (userData.value !== undefined) {
            this.state.roles = userData.value.roles
            this.state.user = userData.value.user
            this.state.balance = userData.value.balance
        }
    }

    public roleAllowsRoute(routeName: string) : boolean
    {
        const route = router.getRoutes().find(r => r.name === routeName)
        if (route === undefined || route === null) {
            return false;
        }

        return this.state.roles.some(role => route.meta.allowedRoles.includes(role))
    }

    updateBalance(newAmount: number): void {
        this.state.balance = newAmount;
    }

    adjustBalance(adjustAmount: number): void {
        this.state.balance += adjustAmount;
    }

    balanceToLocalString(): string {
        const { locale } = useI18n();
        if (locale.value === 'en') {
            return this.state.balance.toFixed(2);
        } else {
            return this.state.balance.toFixed(2).replace(/\./g, ',');
        }
    }
}

export const userDataStore: UserDataStore = new UserDataStore()
