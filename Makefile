.PHONY: run-phpmd

run-phpmd:
	ddev exec vendor/bin/phpmd src/Mealz text ./phpmd.xml --baseline-file ./phpmd.baseline.xml

run-tests:
	ddev run tests
