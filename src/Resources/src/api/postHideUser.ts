import { IMessage } from "@/interfaces/IMessage";
import useApi from "./api";

export default async function postHideUser(username: string) {
    const { error, request, response } = useApi<IMessage | null>(
        'POST',
        `api/costs/hideuser/${username}`
    );

    await request();

    return { error, response };
}