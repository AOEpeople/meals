export DOCKER_BUILDKIT=1

.PHONY: build build-yarn create-migrations get-users load-testdata poweroff run-devbox run-lint-sass run-cs-fixer run-phpmd run-psalm run-tests ssh update-schema

help:
	@echo ""
	@echo "Available helper commands:"
	@echo ""
	@echo "	build              - Build an image from the Dockerfile"
	@echo "	build-yarn         - (Re-)build production ready frontend assets i.e. CSS, JS"
	@echo "	build-yarn-dev     - (Re-)build development ready frontend assets i.e. CSS, JS"
    @echo "	build-yarn-watch   - (Re-)build development ready frontend assets i.e. CSS, JS and watch"
	@echo "	create-migration   - Create Doctrine migration from code"
	@echo "	get-users          - Get test users and their passwords"
	@echo "	load-testdata      - Load test data i.e. dishes, meals and users"
	@echo "	poweroff           - Stop all related containers and projects"
	@echo "	run-devbox         - Run devbox"
	@echo "	run-lint-sass      - Run code linter for sass"
	@echo "	run-cs-fixer       - Run Coding Standards Fixer"
	@echo "	run-phpmd          - Run PHP Mess Detector"
	@echo "	run-psalm          - Run static code analysis"
	@echo "	run-tests          - Run tests"
	@echo "	ssh                - Open a bash session in the web container"
	@echo "	update-schema      - Update the Doctrine schema"
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

build-yarn-watch:
	ddev exec yarn --cwd=src/Resources build-dev --watch

run-lint-sass:
	ddev exec yarn --cwd src/Resources lint:sass

run-phpmd:
	ddev exec vendor/bin/phpmd src/Mealz text ./phpmd.xml --baseline-file ./phpmd.baseline.xml --exclude */Tests/*

run-psalm:
	ddev exec vendor/bin/psalm --use-baseline=./psalm.baseline.xml

run-cs-fixer:
	ddev exec vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist

update-schema:
	ddev exec php bin/console doctrine:schema:update --force

load-testdata:
	ddev exec php bin/console doctrine:fixtures:load -n

create-migration:
	ddev exec php bin/console doctrine:migrations:diff

run-tests:
	ddev run tests

ssh:
	ddev ssh

run-devbox:
	ddev start && ddev install

poweroff:
	ddev poweroff

get-users:
	grep -n "'username'" src/Mealz/UserBundle/DataFixtures/ORM/LoadUsers.php
