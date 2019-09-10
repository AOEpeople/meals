# Mealz for Zombies

## Features (User)

### Meal enrollment
Sign in with LDAP credentials and select your preferred meals on landing page.

### Invite guest for a meal
You need to be signed in with LDAP credentials and you will see small share icon on each day in a week.
You can send your guest the link and he will be able to enroll for particular day giving his First/Last name and Company information.

### Transaction history
You need to be signed in with LDAP credentials and click on your balance. Now you get your Balance from the last day of the last month and a overview of all transaction in the current month.

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
Additionally you can disable some days or the whole week in case of (public) holiday.

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
Additionally you can add a transaction (positive or negative) to a users profile, by clicking on the plus sign.

### Accounting book
**Route:** /accounting/book
**Available at:** Choose "Costs" in admin navigation bar. Click on button "CASH REGISTER".

**Description:**
Lists all transactions booked for users in the last month.

## Installation

    composer install

    APACHEUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data' | grep -v root | head -1 | cut -d\  -f1`
    sudo setfacl -R -m u:"$APACHEUSER":rwX -m u:`whoami`:rwX app/cache app/logs web/media
    sudo setfacl -dR -m u:"$APACHEUSER":rwX -m u:`whoami`:rwX app/cache app/logs web/media

    php app/console doctrine:schema:update --force

### Apache configuration

    <VirtualHost *:80>
            ServerName mealz.local
            DocumentRoot /var/www/mealz/web
            DirectoryIndex app_dev.php

            # autostart xdebug on this host
            # php_value xdebug.remote_enable 1

            <Directory "/var/www/mealz/web">
                    AllowOverride All
                    Allow from All

                    <IfModule mod_rewrite.c>
                            RewriteEngine On

                            RewriteCond %{REQUEST_FILENAME} !-f
                            RewriteRule ^(.*)$ app_dev.php [QSA,L]
                    </IfModule>

            </Directory>
    </VirtualHost>

### Frontend build

```
cd /var/www/mealz/devbox/current/app/Resources
npm config set strict-ssl false
sudo npm install
sudo ./node_modules/bower/bin/bower install
sudo ./node_modules/gulp/bin/gulp.js build
```

To develop and automatically build gulp try
```
sudo ./node_modules/.bin/gulp watch
```

### You're done

Point your webbrowser to http://meals.local
Don't forget to add to your local hosts file:
127.0.0.1 www.meals.local meals.local

### SSH Access
ssh docker@meals.local Password: docker

## Troubleshooting

### SQLSTATE[42S22]: Column not found: 1054 Unknown column

    php app/console doctrine:schema:update --force --env=dev

## Developer information

### User roles

The following roles are in use:

  * ROLE_USER: basically everyone who is allowed to join the meals
  * ROLE_KITCHEN_STAFF: allowed to create and edit dishes and meals
  * ROLE_GUEST: for users who is invited for a meal, customers etc.
  * ROLE_ADMIN: for users who is admin


### Test data

To load up some test data, run

    php app/console doctrine:fixtures:load --env=dev

It generates dishes, meals and users.

You can use "john", "jane, "alice" and "bob" to login. Their password is just their username.
The User "kochomi" is allowed to modify dishes and edit meals.

### Running tests

Some tests require a working database. The database dedicated for running tests can be configured by setting
the database name in `app/config/parameters.yml` as `database_name_testing`. Credentials should be the same
as for the dev environment.

Before running phpunit make sure the database schema is up-to-date:

    sudo -i
    mysql
    > CREATE USER 'mealz_test'@'localhost' IDENTIFIED BY 'mealz_test';
    > CREATE DATABASE mealz_test;
    > GRANT ALL PRIVILEGES ON mealz_test.* TO 'mealz_test'@'localhost';

    php app/console doctrine:schema:update --env=test --force
    bin/phpunit -c app/config/commons/development/phpunit.xml

Note: When you disable xdebug the performance will be better
