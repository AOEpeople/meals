export interface Subscriber {
    subscribe(topics: string[], callback: Function): void;
}
