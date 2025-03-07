.PHONY: deps-stable deps-low cs rector phpstan tests coverage run-examples ci ci-stable ci-lowest

deps-stable:
	composer update --prefer-stable --ignore-platform-req=ext-mongodb

deps-low:
	composer update --prefer-lowest --ignore-platform-req=ext-mongodb

cs:
	PHP_CS_FIXER_IGNORE_ENV=true vendor/bin/php-cs-fixer fix --diff --verbose

rector:
	vendor/bin/rector

phpstan:
	vendor/bin/phpstan

tests:
	vendor/bin/phpunit

coverage:
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html=coverage

run-examples:
	./example

ci: ci-stable

ci-stable: deps-stable rector cs phpstan tests

ci-lowest: deps-low rector cs phpstan tests
