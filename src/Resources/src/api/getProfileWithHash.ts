import { IMessage } from "@/interfaces/IMessage";
import useApi from "./api";
import { IProfile } from "@/stores/profilesStore";
/**
 * Fetches a profile that was requested for settlement by their settlement hash
 */
export default async function getTransactionHistory(hash: string) {
    const { error, response: profile, request } = useApi<IProfile | IMessage>(
        'GET',
        `api/costs/profile/${hash}`
    );

    await request();

    return { error, profile };
}