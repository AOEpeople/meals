import {Subscriber} from "./subscriber";

export class MercureSubscriber implements Subscriber {
    private readonly hubURL: string;

    constructor(hubURL: string) {
        this.hubURL = hubURL;
    }

    subscribe(topics: string[], callback: Function): void {
        let url = new URL(this.hubURL);
        topics.forEach(topic => url.searchParams.append('topic', topic));

        let eventSource = new EventSource(url);
        eventSource.onmessage = (event) => callback(JSON.parse(event.data));
    }
}
