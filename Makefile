qa:
	composer update --prefer-stable
	vendor/bin/php-cs-fixer fix --diff --verbose
	vendor/bin/phpstan
	vendor/bin/phpunit
	vendor/bin/rector

qa-lowest:
	composer update --prefer-lowest
	vendor/bin/php-cs-fixer fix --diff --verbose
	vendor/bin/phpstan
	vendor/bin/phpunit

coverage:
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html=coverage

run-all-examples:
	for file in ./examples/*.php; do \
		echo "Running $$file..."; \
		php $$file; \
		echo ""; \
	done
