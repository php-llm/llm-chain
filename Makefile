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
	php ./examples/chat-claude-anthropic.php
	php ./examples/chat-gpt-azure.php
	php ./examples/chat-gpt-openai.php
	php ./examples/image-describer.php
	php ./examples/reasoning-openai.php
	php ./examples/structured-output-math.php
	php ./examples/toolbox-clock.php
	php ./examples/toolbox-serpapi.php
	php ./examples/toolbox-weather.php
	php ./examples/toolbox-wikipedia.php
	php ./examples/toolbox-youtube.php
