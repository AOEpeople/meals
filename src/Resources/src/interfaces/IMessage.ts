export interface IMessage {
    message: string;
}

/**
 * Checks if a given response is of type IMessage
 */
export function isMessage(response: IMessage | unknown): response is IMessage {
    return (
        (response as IMessage)?.message !== undefined &&
        Object.keys(response as IMessage).length === 1 &&
        typeof (response as IMessage).message === 'string'
    );
}
