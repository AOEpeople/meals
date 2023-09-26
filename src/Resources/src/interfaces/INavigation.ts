import { FunctionalComponent } from 'vue';

export interface INavigation {
    name: string,
    to: string,
    icon: FunctionalComponent,
    access: boolean
}