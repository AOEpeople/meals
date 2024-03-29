export DOCKER_BUILDKIT=1

.PHONY: build build-yarn create-migrations get-users load-testdata poweroff run-devbox run-lint-sass run-cs-fixer run-phpmd run-psalm run-tests ssh update-schema

help:
	@echo ""
	@echo "Available helper commands:"
	@echo ""
	@echo "	build              - Build an image from the Dockerfile"
	@echo "	build-yarn         - (Re-)build production ready frontend assets i.e. CSS, JS"
	@echo "	build-yarn-dev     - (Re-)build development ready frontend assets i.e. JS"
	@echo "	build-yarn-dev-css - (Re-)build development ready frontend assets i.e. CSS, JS"
	@echo "	build-yarn-watch   - (Re-)build and watch development ready frontend assets i.e. CSS, JS"
	@echo "	create-migration   - Create Doctrine migration from code"
	@echo "	get-users          - Get test users and their passwords"
	@echo "	load-testdata      - Load test data i.e. dishes, meals and users"
	@echo "	poweroff           - Stop all related containers and projects"
	@echo "	run-devbox         - Run devbox"
	@echo "	run-lint           - Run code linter"
	@echo "	run-prettier-check - Run prettier with the check option"
	@echo "	run-prettier       - Run prettier to format frontend files"
	@echo "	run-cs-fixer       - Run Coding Standards Fixer"
	@echo "	run-phpmd          - Run PHP Mess Detector"
	@echo "	run-psalm          - Run static code analysis"
	@echo "	run-tests-be       - Run backend-tests"
	@echo "	run-tests-fe       - Run frontend-tests"
	@echo " run-cypress        - Run cypress"
	@echo " run-cypress-headless - Run cypress headless"
	@echo "	ssh                - Open a bash session in the web container"
	@echo "	update-schema      - Update the Doctrine schema"
	@echo "	mailhog            - Open MailHog in the browser"
	@echo ""

build:
	docker build \
		--build-arg BUILDKIT_INLINE_CACHE=1 \
		--cache-from aoepeople/meals:edge \
		--tag aoepeople/meals:edge \
		.

build-yarn:
	ddev exec yarn --cwd=src/Resources build

build-yarn-dev:
	ddev exec yarn --cwd=src/Resources build-dev

build-yarn-dev-css:
	ddev exec yarn --cwd=src/Resources build-dev-css

build-yarn-watch:
	ddev exec yarn --cwd=src/Resources build-watch

run-lint:
	ddev exec yarn --cwd src/Resources lint

run-prettier-check:
	ddev exec yarn --cwd src/Resources prettier-check

run-prettier:
	ddev exec yarn --cwd src/Resources prettier

run-phpmd:
	ddev exec vendor/bin/phpmd src/Mealz text ./phpmd.xml --baseline-file ./phpmd.baseline.xml --exclude */Tests/*

run-psalm:
	ddev exec vendor/bin/psalm --use-baseline=./psalm.baseline.xml

run-cs-fixer:
	ddev exec vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist --diff --dry-run -v

update-schema:
	ddev exec php bin/console doctrine:schema:update --force

load-testdata:
	ddev exec php bin/console doctrine:fixtures:load -n

create-migration:
	ddev exec php bin/console doctrine:migrations:diff

run-tests-be:
	ddev run tests

run-tests-fe:
	ddev exec yarn --cwd=src/Resources test

run-cypress:
	yarn --cwd=./tests/e2e cypress open

run-cypress-headless:
	yarn --cwd=./tests/e2e cross-env-shell cypress run --headless --browser electron --env "baseUrl=https://meals.test/"

ssh:
	ddev ssh

run-devbox:
	ddev start && ddev install

poweroff:
	ddev poweroff

get-users:
	grep -n "'username'" src/Mealz/UserBundle/DataFixtures/ORM/LoadUsers.php

mailhog:
	ddev launch -m