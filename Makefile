qa:
	vendor/bin/php-cs-fixer fix --diff --verbose
	vendor/bin/phpstan
	vendor/bin/phpunit
	vendor/bin/rector

qa-lowest:
	composer update --prefer-lowest
	vendor/bin/php-cs-fixer fix --diff --verbose
	vendor/bin/phpstan
	vendor/bin/phpunit
	git restore composer.lock

coverage:
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html=coverage
