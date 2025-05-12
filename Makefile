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
	vendor/bin/phpstan --memory-limit=-1

tests:
	vendor/bin/phpunit

coverage:
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html=coverage

run-examples:
	./example

huggingface-models:
	php examples/huggingface/_model-listing.php

ci: ci-stable

ci-stable: deps-stable rector cs phpstan tests

ci-lowest: deps-low rector cs phpstan tests

fix-transformers:
	wget -P /tmp https://github.com/rindow/rindow-matlib/releases/download/1.1.1/rindow-matlib_1.1.1-24.04_amd64.deb
	dpkg-deb -x /tmp/rindow-matlib_1.1.1-24.04_amd64.deb /tmp/librindowmatlib_extracted
	cp /tmp/librindowmatlib_extracted/usr/lib/rindowmatlib-thread/librindowmatlib.so vendor/codewithkyrian/transformers/libs/librindowmatlib.so
