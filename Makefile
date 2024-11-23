.PHONY: qa qa-lowest coverage run-all-examples

qa:
	composer update --prefer-stable
	PHP_CS_FIXER_IGNORE_ENV=true vendor/bin/php-cs-fixer fix --diff --verbose
	vendor/bin/rector
	vendor/bin/phpstan
	vendor/bin/phpunit


qa-lowest:
	composer update --prefer-lowest
	PHP_CS_FIXER_IGNORE_ENV=true vendor/bin/php-cs-fixer fix --diff --verbose
	vendor/bin/phpstan
	vendor/bin/phpunit

coverage:
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html=coverage

run-all-examples:
	./example
