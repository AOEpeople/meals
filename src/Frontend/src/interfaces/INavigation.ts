import { FunctionalComponent, Component } from 'vue';

export interface INavigation {
    name: string;
    to: string;
    icon: FunctionalComponent | Component;
    access: boolean;
}
