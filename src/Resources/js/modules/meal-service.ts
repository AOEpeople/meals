export class MealService {
    static getDate($checkbox: JQuery): string {
        const date = $checkbox.closest('[data-date]').data('date');
        if (undefined === date) {
            return null;
        }
        return date;
    }

    static getDishSlug($checkbox: JQuery): string {
        const slug = $checkbox.closest('[data-slug]').data('slug');
        if (undefined === slug) {
            return null;
        }
        return slug;
    }

    static getParticipantId($checkbox: JQuery): number {
        let strID = $checkbox.closest('[data-participant-id]').attr('data-participant-id');
        if (undefined === strID) {
            return null;
        }

        const id = Number(strID);
        return isNaN(id) ? null : id;
    }

    static setParticipantId($checkbox: JQuery, participantId: number): void {
        $checkbox
            .closest('[data-participant-id]')
            .attr('data-participant-id', participantId ?? '');
    }
}
