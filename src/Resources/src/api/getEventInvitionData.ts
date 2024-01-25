import { IMessage } from "@/interfaces/IMessage";
import useApi from "./api";

export interface EventInvitationData {
    date: string,
    lockDate: string,
    event: string
}

export default async function getEventInvitationData(invitationHash: string) {
    const { error, request, response } = useApi<EventInvitationData | IMessage>(
        'GET',
        `/api/event/invitation/${invitationHash}`
    );

    await request();

    return { error, response };
}