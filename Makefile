PHP_VERSION ?= 8.4
DEPENDENCIES_LOWEST ?= 0
COMPOSER_IMAGE ?= composer:2
COMPOSER_CACHE_DIR ?= $(HOME)/.cache/composer
PHP_IMAGE = php:$(PHP_VERSION)-cli
CA_CERT_MOUNT = $(if $(CA_CERT_FILE),--volume "$(CA_CERT_FILE):/ca-cert.pem:ro" --env COMPOSER_CAFILE=/ca-cert.pem,)
PROXY_ENV = --env HTTP_PROXY --env HTTPS_PROXY --env ALL_PROXY --env NO_PROXY --env http_proxy --env https_proxy --env all_proxy --env no_proxy

.PHONY: help clean check-php-version install lint audit validate test

help:
	@printf '%s\n' 'Targets: clean check-php-version install lint audit validate test'
	@printf '%s\n' 'Set PHP_VERSION to select the Docker PHP version.'

clean:
	rm -rf vendor composer.lock .phpunit.cache .phpunit.result.cache

check-php-version:
	@docker run --rm "$(PHP_IMAGE)" php -r 'printf("PHP %s\n", PHP_VERSION);'

install:
	@mkdir -p "$(COMPOSER_CACHE_DIR)"
	@set +e; docker run --rm --entrypoint sh \
		--user "$(shell id -u):$(shell id -g)" \
		--volume "$(CURDIR):/app" \
		--volume "$(COMPOSER_CACHE_DIR):/composer-cache" \
		--workdir /app \
		$(PROXY_ENV) $(CA_CERT_MOUNT) \
		--env PHP_VERSION="$(PHP_VERSION)" \
		--env DEPENDENCIES_LOWEST="$(DEPENDENCIES_LOWEST)" \
		--env COMPOSER_CACHE_DIR=/composer-cache \
		"$(COMPOSER_IMAGE)" -c 'result=0; composer config platform.php "$$PHP_VERSION" || result=$$?; if [ "$$result" -eq 0 ]; then if [ "$$DEPENDENCIES_LOWEST" = 1 ]; then composer update --prefer-dist --no-interaction --no-progress --prefer-lowest; else composer update --prefer-dist --no-interaction --no-progress; fi; result=$$?; fi; composer config --unset platform.php >/dev/null 2>&1 || true; composer config --unset platform >/dev/null 2>&1 || true; php -r '\''$$c=json_decode(file_get_contents("composer.json"), true); if (isset($$c["config"]) && $$c["config"] === []) { unset($$c["config"]); file_put_contents("composer.json", json_encode($$c, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL); }'\''; rm -f composer.lock; exit "$$result"'; result=$$?; rm -f composer.lock; exit $$result

lint: check-php-version
	@docker run --rm --volume "$(CURDIR):/app" --workdir /app "$(PHP_IMAGE)" \
		sh -c 'find src tests -type f -name "*.php" -exec php -l {} +'

audit:
	@docker run --rm --entrypoint sh --user "$(shell id -u):$(shell id -g)" \
		--volume "$(CURDIR):/app" \
		--workdir /app $(PROXY_ENV) $(CA_CERT_MOUNT) \
		"$(COMPOSER_IMAGE)" -c 'composer audit'

validate:
	@docker run --rm --entrypoint sh --user "$(shell id -u):$(shell id -g)" \
		--volume "$(CURDIR):/app" \
		--workdir /app $(PROXY_ENV) $(CA_CERT_MOUNT) \
		"$(COMPOSER_IMAGE)" -c 'composer validate --strict --no-check-lock'

test: check-php-version
	@docker run --rm --volume "$(CURDIR):/app" --workdir /app "$(PHP_IMAGE)" \
		vendor/bin/phpunit
