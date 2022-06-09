type Week = {
    id: number,
    year: number,
    calenderWeek: number,
    days: Array<Day>,
};

type Transaction = {
    data: [{
        type: string,
        date: string,
        description: string,
        amount: number,
    }],
    difference: number,
};

type Day = {
    id: number | null,
    dateTime: Date,
    week: Week,
    meals: Array<Meal>,
    lockParticipationDateTime: Date,
};

type Meal = {
    id: number | null,
    dish: Dish,
    price: number,
    participationLimit: number,
    day: Day,
    dateTime: Date,
    participants: Array<Participant>,
};

type Dish = {
    id: number,
    slug: string,
    title_en: string,
    title_de: string,
    description_en: string | null,
    description_de: string | null,
    category: Category | null,
    price: number,
    enabled: boolean,
    currentLocale: string,
    variations: Array<DishVariation>,
};

type DishVariation = Dish;

type Participant = {
    id: number | null,
    meal: Meal,
    slot: Slot | null,
    combinedDishes: Array<Dish> | null,
    profile: Profile,
    comment: string | null,
    guestName: string | null,
    costAbsorbed: boolean,
    offeredAt: number,
    confirmed: boolean,
}

type Category = {
    id: number,
    slug: string,
    title_en: string,
    title_de: string,
    currentLocale: string,
    dishes: Array<Dish>,
}

type Slot = {
    id: number,
    title: string,
    limit: number,
    disabled: boolean,
    deleted: boolean,
    order: number,
    slug: string | null,
}

type Profile = {
    username: string,
    name: string,
    firstName: string,
    hidden: boolean,
    company: string | null,
    roles: Array<Role> | null,
    settlementHash: string | null,
}

const ROLE_KITCHEN_STAFF = 'ROLE_KITCHEN_STAFF';
const ROLE_USER = 'ROLE_USER';
const ROLE_GUEST = 'ROLE_GUEST';
const ROLE_FINANCE = 'ROLE_FINANCE';

type Role = {
    id: number,
    title: string,
    sid: string,
    profiles: Array<Profile> | null,
}
