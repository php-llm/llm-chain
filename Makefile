qa:
	vendor/bin/php-cs-fixer fix
	vendor/bin/phpstan
	vendor/bin/phpunit
