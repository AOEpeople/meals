import { Store } from '@/stores/store';
import { Env, useEnvs } from '@/api/getEnvironmentals';

class EnvironmentStore extends Store<Env> {
    protected data(): Env {
        return {
            paypalId: '',
            mercureUrl: ''
        };
    }

    async fillStore() {
        const { env, error } = await useEnvs();
        if (error.value === true) {
            console.warn("couldn't receive EnvironmentalVars!");
            return;
        }
        if (env !== undefined) {
            this.state.paypalId = env.paypalId;
            this.state.mercureUrl = env.mercureUrl;
        }
    }
}

export const environmentStore: EnvironmentStore = new EnvironmentStore();
