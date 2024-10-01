.PHONY: qa qa-lowest coverage run-all-examples

qa:
	composer update --prefer-stable
	vendor/bin/php-cs-fixer fix --diff --verbose
	vendor/bin/rector
	vendor/bin/phpstan
	vendor/bin/phpunit


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
