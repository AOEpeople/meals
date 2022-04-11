interface SlotAllocationUpdate {
    date: Date;
    slotAllocation: {
        // key: slot, value: allocation count
        [key: string]: number;
    };
}

export class SlotAllocationUpdateHandler {
    static handleUpdate(data: SlotAllocationUpdate): void {
        let $slotSelector = $(`#day-${data.date}-slots`);
        if (1 !== $slotSelector.length) {
            return;
        }

        for (const [slot, count] of Object.entries(data.slotAllocation)) {
            let $slotOption = $slotSelector.find(`option[value="${slot}"]`);
            if (1 !== $slotOption.length) {
                continue;
            }

            const slotLimit = $slotOption.data('limit');
            if (slotLimit > 0) {
                const slotTitle = $slotOption.data('title');
                const slotText = `${slotTitle} (${count}/${slotLimit})`;
                $slotOption.text(slotText);
                // disable slot-option if no. of booked slots reach the slot limit
                $slotOption.prop('disabled', slotLimit <= count);
            }

            if ('' !== $slotSelector.val()) {
                $slotSelector.find('option[value=""]').hide();
            }
        }
    }
}
