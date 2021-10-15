export DOCKER_BUILDKIT=1

.PHONY: build run-phpmd run-tests

build:
	docker build \
		--build-arg BUILDKIT_INLINE_CACHE=1 \
		--cache-from aoepeople/meals:edge \
		--tag aoepeople/meals:edge \
		.

run-phpmd:
	ddev exec vendor/bin/phpmd src/Mealz text ./phpmd.xml --baseline-file ./phpmd.baseline.xml

run-tests:
	ddev run tests
