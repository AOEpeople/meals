![Meals app screenshot](https://raw.githubusercontent.com/AOEpeople/meals/master/src/Resources/images/meals_screenshot.png)

# 🍽 [AOEpeople/meals](https://github.com/AOEpeople/meals)

### Meals is an open source web application to manage lunches in a company canteen.

---
[![CI](https://github.com/AOEpeople/meals/actions/workflows/main.yml/badge.svg)](https://github.com/AOEpeople/meals/actions/workflows/main.yml)
[![docker hub pulls](https://img.shields.io/docker/pulls/aoepeople/meals.svg)](https://hub.docker.com/repository/docker/aoepeople/meals)
---

## Features (User)

### Meal enrollment
Sign in with your login credentials and select your preferred meals on landing page.

### Invite guest for a meal
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
**Route:** /menu
**Available at:** Choose "Menu" in admin navigation bar.
**Actions:** Create new week and edit existing ones.

**Description:**
List of current and upcoming weeks. Already created / edited weeks are green.
Weeks which haven't been created yet, are displayed with a grey background color.

### Menu (Week detail view)
**Route:** /menu/{YYYY}W{KW}/new oder /menu/{week-id}/edit
**Available at:** Choose "Menu" in admin navigation bar and click on one of the listed weeks.
**Actions:** Disable whole week or some days.

**Description:**
Here you can select the desired dishes for the selected week.
Additionally, you can disable some days or the whole week in case of (public) holiday.

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

### Costs
**Route:** /print/costsheet
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
**Route:** /accounting/book
**Available at:** Choose "Costs" in admin navigation bar. Click on button "CASH REGISTER".

**Description:**
Lists all transactions booked for users in the last month.

## Features (Finance)

### General
Finance has access to all user features and the finance tab.

### Dish variations
**Route:** /accounting/book/finance/list
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

## Troubleshooting

### SQLSTATE[42S22]: Column not found: 1054 Unknown column

    ddev exec php bin/console doctrine:schema:update --force --env=dev

---

## Developer information

### User roles

The following roles are in use:

  * ROLE_USER: basically everyone who is allowed to join the meals
  * ROLE_KITCHEN_STAFF: allowed to create and edit dishes and meals
  * ROLE_GUEST: for users who are invited for a meal, e.g. customers etc.
  * ROLE_ADMIN: for users who are admins
  * ROLE_FINANCE: for users who are from finance

### Login
User authentication takes place using oauth with custom identity provider. To use it you must define the following env vars with correct values in `.env.local`:

```shell
IDP_SERVER=https://login.some-domain.com/
IDP_CLIENT_ID=client-id
IDP_CLIENT_SECRET=client-secret
```