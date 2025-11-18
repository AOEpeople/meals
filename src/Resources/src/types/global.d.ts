export {};

declare global {
    interface Window {
        appData?: {
            payment_notification_debt_limit?: number;
            meals_locked_debt_limit?: number;
        };
    }
}
