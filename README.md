![Meals app screenshot](https://raw.githubusercontent.com/AOEpeople/meals/main/src/Resources/images/meals_screenshot.png)

# 🍽 [AOEpeople/meals](https://github.com/AOEpeople/meals)

### Meals is an open source web application to manage lunches in a company canteen.

---
[![CI](https://github.com/AOEpeople/meals/actions/workflows/main.yml/badge.svg)](https://github.com/AOEpeople/meals/actions/workflows/main.yml)
[![docker hub pulls](https://img.shields.io/docker/pulls/aoepeople/meals.svg)](https://hub.docker.com/repository/docker/aoepeople/meals)
---

## Features (User)

### Meal / event enrollment
Sign in with your login credentials and select your preferred meals / events on landing page.

### Invite guest for a meal / event
As a logged-in user, you will see small share icon on each day in a week.
You can send your guest the link, and he will be able to enroll for a particular day giving his First/Last name and Company information.

### Transaction history
Click on your balance. Now you get your Balance from the last day of the last month and an overview of all transaction in the current month.

### PayPal debt payment
In your transaction history you also can pay your debts with PayPal.

## Features (Admin)

### General
Admin has access to all user features as well.

### Menu (List of weeks)
**Route:** /weeks
**Available at:** Choose "Menu" in admin navigation bar.
**Actions:** Create new week and edit existing ones.

**Description:**
List of current and upcoming weeks. Already created / edited weeks are green.
Weeks which haven't been created yet, are displayed with a grey background color.

### Menu (Week detail view)
**Route:** /menu/{week-id}
**Available at:** Choose "Menu" in admin navigation bar and click on one of the listed weeks.
**Actions:** Disable whole week or some days.

**Description:**
Here you can select the desired dishes for the selected week.
Additionally, you can disable some days or the whole week in case of (public) holiday.

### Menu (Participations detail view)
**Route:** /participations/{week-id}/edit
**Available at:** Choose "participations" in the Menu
**Actions:** Manage the participations of users

**Description:**
Lists all currently participating users for the menu of the week and their respective meals.
You can change their filter them, change their participations and add currently not participating users.

### Dishes
**Route:** /dish
**Available at:** Choose "Dishes" in admin navigation bar.
**Actions** Add variation, Create, edit and delete dishes

### Dish variations
**Route:** /dish
**Available at:** Choose "Dishes" in admin navigation bar.
**Actions** Add variation, edit and delete dishes

**Description:**
Lists all existing variations for particular dishes. You can edit and delete them.
If you click on "ADD VARIATION" you can add new variation to some Dish.

### (Dish) Categories
**Route:** /category
**Available at:** Choose "Categories" in admin navigation bar.
**Actions:** Create, edit and delete categories

**Description:**
Lists all existing (dish) categories. You can edit and delete them.
If you click on "CREATE CATEGORY" you can create a new one.

### Timeslots
**Route:** /time-slots
**Available at:** Choose "Timeslot" in admin navigation bar.
**Actions:** Activate, create, edit and delete timeslots.

### Events
**Route:** /events
**Available at:** Choose "Events" in the admin navigation bar.
**Actions:** Search, create, edit and delete events

### Costs
**Route:** /costs
**Available at:** Choose "Costs" in admin navigation bar.
**Actions:** Book transaction for user, "CASH REGISTER" (Accounting book)

**Description:**
Lists all users and their outstanding debts. Debts are structured in 6 different columns:
Total, current month (all debts in this month till current day), one column for each of the last three month,
all debts before the last three month summed up in one column.
Additionally, you can add a transaction (positive or negative) to a users profile, by clicking on the plus sign.
Also, you can request an account settlement if an employee leaves. You can find the 3 dots button right beside the add transaction icon. You can only settle accounts with a positive amount of money.
The log for account settlements is in the **app/logs** Folder. If the account settlement is successful all future meal bookings that are associated with this account, will be canceled.

### Accounting book
**Route:** /cash-register
**Available at:** Choose "Costs" in admin navigation bar. Click on button "CASH REGISTER".

**Description:**
Lists all transactions booked for users in the last month.

## Features (Finance)

### General
Finance has access to all user features and the finance tab.

**Route:** /finance
**Available at:** Choose "Finance" in finance navigation bar.
**Actions** Select Date to list all transaction and export as pdf.

---

## Devbox Installation
We're using [ddev](https://ddev.readthedocs.io/) for local development. `ddev` is a CLI tool which uses Docker to simplify local development. Please make sure that `ddev`, `mkcert` and `docker` are installed. Before starting the Devbox run:
```
mkcert -install
```

To simplify things, we have put common commands into a **Makefile**. To see all available options, run the following command:
```
make
```

Run the following to start the Devbox:
```
make run-devbox
```

Point your web browser to https://meals.test  :tada:

:memo:  Don't forget to add `127.0.0.1 meals.test` to your local hosts file if not done automatically via ddev.

To run End-to-End testing via cypress run the following command:
```
make run-cypress
```
You need to start the devbox before running any tests with cypress.

## Component testing

To run specific component tests with Vitest in isolation:
```
ddev exec npm run --prefix src/Resources test:unit -- "<test-filename> <testname>"
```
`<test-filename>` for a file with the name `xyz.spec.ts` would be `xyz`
`<testname>` is optional, if no testname is specified the whole file is run

## Linting

There are several lintig and formatting checks for the front- and backend. These are also executed in the GitHub-Actions workflows. Every check can be run manually and locally with the following make commands.

#### ESLint
Frontend asset linting
```
make run-lint
```

#### Prettier
Frontend formatting check
```
make run-prettier-check
```
Frontend automated formatting
```
make run-prettier
```

#### Vue-Tsc
Check TypeScript types
```
make run-typecheck
```

#### Php-CS-Fixer
Backend linting
```
make run-cs-fixer
```

#### Phpmd
Backend linting
```
make run-phpmd
```

#### Psalm
Backend linting
```
make run-psalm
```

## Troubleshooting

### SQLSTATE[42S22]: Column not found: 1054 Unknown column

    ddev exec php bin/console doctrine:schema:update --force --env=dev

---

## Developer information

### Vite

Vite has two different modes of operation. One where a production ready build is generated and a dev mode. The dev mode is best used to quickly iterate and rebuild over changes while working.


### User roles

The following roles are in use:

  * ROLE_USER: basically everyone who is allowed to join the meals
  * ROLE_KITCHEN_STAFF: allowed to create and edit dishes and meals
  * ROLE_GUEST: for users who are invited for a meal, e.g. customers etc.
  * ROLE_ADMIN: for users who are admins
  * ROLE_FINANCE: for users who are from finance

### Login
There are two possibilities for authentication. Either OAuth or a classic login via the database. The modes can be toggled with the `APP_AUTH_MODE` environment variable in `.env`.
For User authentication using oauth with a custom identity provider you must define the following env vars with correct values in `.env.local`:

```shell
IDP_SERVER=https://login.some-domain.com/
IDP_CLIENT_ID=client-id
IDP_CLIENT_SECRET=client-secret
```

### Dev Warning

In the Vite Dev mode browsers will typically send out a Warning("Source-Map-Fehler: No sources are declared in this source map."). This can be mitigated by using the build mode.

### API Error Messages
*MealAdminController 1xx*
  * 101: Invalid JSON was received
  * 102: No menu can be created in this week because a menu already exists
  * 103: The week for this daterange is not empty, because it already exists
  * 104: There was an unknown error on generating an empty week
  * 105: A day whose meals should be edited doesn't exist
  * 106: There were more than two meals requested for a specific day
  * 107: The requested meal contains an unknown dish
  * 108: The meal cannot be changed because it already has participations
*DishController / DishVariationController 2xx*
  * 201: There are parameters missing to create a dish
  * 202: One or more titles haven't been sent
  * 203: There was an error while counting the number of times dishes were taken
  * 204: The servingSize cannot be adjusted, because there are booked combi-meals
*CategoryController 3xx*
  * 301: The choosen titles for the category either are missing or do already exist
*ParticipantController 4xx*
  * 401: To add a user to a combined dish, the combined dish needs to have exactly two dishes
  * 402: On joining a Meal an error occured
  * 403: User is not allowed to join meal
*CostSheetController 5xx*
  * 501: The profile that is requested to be hidden, is already hidden
  * 502: The settlement request has already been sent
  * 503: The settlement request failed
  * 504: The profile for this hash cannot be found
  * 505: The settlement request was already processed or the request is invalid
*CashController 6xx*
  * 601: The amount of cash that will be added, has to be more than zero
*EventController 7xx*
  * 701: Event creation parameters are missing
*EventParticipationController 8xx*
  * 801: User has no permission to enter or leave
  * 802: User could not join the event
  * 803: User could not leave the event
  * 804: User could not join, because the event already happened
*MealGuestController 9xx*
  * 901: Could not find the Invitation for the given hash
  * 902: Parameters were not provided (eg. firstname and lastname)
  * 903: An unknown error occured on joining the event as a guest
*GuestController 10xx*
  * 1001: At least one of the Parameters firstName or lastName are missing
  * 1002: The profile exists but is hidden