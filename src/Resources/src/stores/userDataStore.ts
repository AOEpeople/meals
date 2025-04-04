import { Store } from '@/stores/store';
import { useUserData, type UserData } from '@/api/getUserData';
import router from '@/router';

class UserDataStore extends Store<UserData> {
    protected data(): UserData {
        return {
            roles: [],
            user: '',
            fullName: '',
            balance: 0.0
        };
    }

    async fillStore() {
        await this.writeUserData();
    }

    private async writeUserData() {
        const { userData, error } = await useUserData();
        if (error.value === true) {
            console.warn("couldn't receive User Data!");
            return;
        }
        if (userData.value !== undefined && this.isUserdata(userData.value)) {
            this.state.roles = userData.value.roles;
            this.state.user = userData.value.user;
            this.state.fullName = userData.value.fullName;
            this.state.balance = userData.value.balance;
        }
    }

    private isUserdata(userData: UserData): userData is UserData {
        return userData !== null && userData !== undefined && typeof userData.balance === 'number';
    }

    public roleAllowsRoute(routeName: string): boolean {
        const route = router.getRoutes().find((r) => r.name === routeName);
        if (route === undefined || route === null || this.state.roles === undefined || this.state.roles === null) {
            return false;
        }

        return this.state.roles.some((role) => route.meta.allowedRoles.includes(role));
    }

    updateBalance(newAmount: number): void {
        this.state.balance = newAmount;
    }

    adjustBalance(adjustAmount: number): void {
        if (typeof adjustAmount === 'string') {
            adjustAmount = parseFloat(adjustAmount);
        }
        this.state.balance += adjustAmount;
    }

    balanceToLocalString(locale: string): string {
        if (locale === 'en') {
            return this.state.balance.toFixed(2);
        } else {
            return this.state.balance.toFixed(2).replace(/\./g, ',');
        }
    }
}

export const userDataStore: UserDataStore = new UserDataStore();
