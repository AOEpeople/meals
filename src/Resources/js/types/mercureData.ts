type ParticipationCountData = {
    mealId:     number,
    count:      number,
    isLocked:   boolean
};

type OfferData = {
    mealId:         number,
    isAvailable:    boolean,
    date:           Date,
    dishSlug:       string
};

type SlotData = {
    date:       Date,
    slotSlug:   string,
    slotCount:  number
};