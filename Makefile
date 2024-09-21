qa:
	vendor/bin/php-cs-fixer fix
	vendor/bin/phpstan
	vendor/bin/phpunit

qa-lowest:
	composer update --prefer-lowest
	vendor/bin/php-cs-fixer fix
	vendor/bin/phpstan
	vendor/bin/phpunit
	git restore composer.lock
