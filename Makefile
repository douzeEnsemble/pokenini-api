# Executables (local)
DOCKER = docker
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP) bin/console
PHPUNIT  = $(PHP) vendor/bin/phpunit
DOCKERCOMPOSE_LINTER_CMD = docker run -t --rm -v ${PWD}:/app zavoloklom/dclint:3.1.0-alpine
DOTENV_LINTER_CMD = docker run -t --rm -v ${PWD}:/app -w /app dotenvlinter/dotenv-linter:4.0.0
HADOLINT_CMD = docker run -t --rm -v ${PWD}:/app hadolint/hadolint:v2.14.0-alpine hadolint
EDITORCONFIG_LINTER_CMD = docker run --rm --volume=${PWD}:/check mstruebing/editorconfig-checker:v3.6.0

# Misc
.DEFAULT_GOAL = help

## —— 🎵 🐳 The Symfony-docker Makefile 🐳 🎵 ——————————————————————————————————
.PHONY: help
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Directories and files 📁 ————————————————————————————————————————————————————————————————
.env: ## Create .env files (not phony to check the file)
	touch .env
.env.dev.local: ## Create .env.dev.local files (not phony to check the file)
	cp .env.dev .env.dev.local

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
.PHONY: build
build: ## Builds the Docker images
	$(DOCKER_COMP) build

.PHONY: rebuild
rebuild: ## Re-builds the Docker images (build with no cache)
	${DOCKER_COMP} build --no-cache --pull

.PHONY: start
start: ## Start the project
start: install up vendor cc data

.PHONY: up
up: ## Up Docker container
up: up-process up-after

up-process:
	$(DOCKER_COMP) up --wait

up-after:

.PHONY: install
install: ## Install requirements
install: .env .env.dev.local build

.PHONY: stop
stop: ## Stop the project
	$(DOCKER_COMP) down --remove-orphans

.PHONY: destruct
destruct: ## Destruct the project
destruct: stop
	$(DOCKER_COMP) down --remove-orphans --volumes database moco.sheets.int moco.sheets.test newman php web --rmi all

.PHONY: logs
logs: ## Containers logs
	@$(DOCKER_COMP) logs -f -n 0

.PHONY: sh
sh: ## Connect to the PHP container
	@$(PHP_CONT) sh

.PHONY: bash
bash: ## Alias of sh
bash: sh

.PHONY: restart-mocks
restart-mocks: ## Restart Moco mocks
	$(DOCKER_COMP) restart moco.sheets.int
	$(DOCKER_COMP) restart moco.sheets.tests

## —— Data 💾 ————————————————————————————————————————————————————————————————
.PHONY: data
data: ## Initialize data
data: init-db data-app

.PHONY: init-db
init-db: ## Initialize database data
	$(SYMFONY) doctrine:database:drop --force --if-exists --env=dev
	$(SYMFONY) doctrine:database:create --env=dev
	$(SYMFONY) doctrine:migration:migrate --no-interaction --env=dev
	$(SYMFONY) doctrine:database:drop --force --if-exists --env=test
	$(SYMFONY) doctrine:database:create --env=test
	$(SYMFONY) doctrine:migration:migrate --no-interaction --env=test
	$(SYMFONY) doctrine:database:drop --force --if-exists --env=int
	$(SYMFONY) doctrine:database:create --env=int
	$(SYMFONY) doctrine:migration:migrate --no-interaction --env=int

.PHONY: data-app
data-app: ## Initialize app data
	$(SYMFONY) app:update:labels \
		&& $(SYMFONY) app:update:games_collections_and_dex \
		&& $(SYMFONY) app:update:pokemons \
		&& $(SYMFONY) app:update:regional_dex_numbers \
		&& $(SYMFONY) app:update:games_availabilities \
		&& $(SYMFONY) app:update:games_shinies_availabilities \
		&& $(SYMFONY) app:update:collections_availabilities \
		&& $(SYMFONY) app:calculate:game_bundles_availabilities \
		&& $(SYMFONY) app:calculate:game_bundles_shinies_availabilities \
		&& $(SYMFONY) app:calculate:dex_availabilities \
		&& $(SYMFONY) app:calculate:pokemon_availabilities

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
.PHONY: composer
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

.PHONY: vendor
vendor: ## Install vendors according to the current composer.lock file
	@$(COMPOSER) install --prefer-dist --no-progress --no-interaction
	@$(COMPOSER) clear-cache

.PHONY: updates
updates: ## Updates all composer
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=./
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/deptrac
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/infection
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/jsonlint
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/php-cs-fixer
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/phpmd
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/phpstan
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/psalm

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
.PHONY: sf
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

.PHONY: cc
cc: ## Clear the cache
cc:
	@$(SYMFONY) cache:clear --env=dev
	@$(SYMFONY) cache:clear --env=test

## —— Tests 🧪 ———————————————————————————————————————————————————————————————
.PHONY: tests
tests: ## Execute all tests
tests:
	$(PHPUNIT) tests/src --display-all

.PHONY: t
t: ## Alias of tests
t: tests

.PHONY: tests-unit
tests-unit: ## Execute unit tests
	$(PHPUNIT) tests/src/Unit

.PHONY: tests-integration
tests-integration: ## Execute integration tests
	$(PHPUNIT) tests/src/Integration

.PHONY: tu
tu: ## Alias of tests-unit
tu: tests-unit

.PHONY: ti
ti: ## Alias of tests-integration
ti: tests-integration

.PHONY: tests-api-mocked
tests-api-mocked: ## Execute tests on the group api-mocked-testing only
	$(PHPUNIT) tests/src/Integration --group=api-mocked-testing

## —— Quality 👌 ———————————————————————————————————————————————————————————————
.PHONY: quality
quality: ## Execute all quality analyses
quality: infra-quality code-quality

.PHONY: infra-quality
infra-quality: ## Execute all infra quality analyses
infra-quality: docker-compose-linter dockerfile-linter dotenv-linter

.PHONY: iq
iq: ## Alias of infra-quality
iq: infra-quality

.PHONY: docker-compose-linter
docker-compose-linter: ## Run Docker Compose linter
	$(DOCKERCOMPOSE_LINTER_CMD) -r .

.PHONY: docker-compose-fixer
docker-compose-fixer: ## Run Docker Compose fixer
	$(DOCKERCOMPOSE_LINTER_CMD)  -r . --fix

.PHONY: dockerfile-linter
dockerfile-linter: ## Run Dockerfile linter
	@find .docker -name 'Dockerfile' | while read -r dockerfile; do \
		$(HADOLINT_CMD) "/app/$$dockerfile"; \
	done

.PHONY: dotenv-linter
dotenv-linter: ## Run DotEnv linter
	$(DOTENV_LINTER_CMD) check . -r

.PHONY: dotenv-fixer
dotenv-fixer: ## Run DotEnv fixer
	$(DOTENV_LINTER_CMD) fix . -r --no-backup

.PHONY: code-quality
code-quality: ## Execute all code quality analyses
code-quality: editorconfig-linter jsonlint validate-autoloader phpcsfixer phpmd psalm phpstan deptrac

.PHONY: cq
cq: ## Alias of code-quality
cq: code-quality

.PHONY: editorconfig-linter
editorconfig-linter: ## Execute editorconfig linter
editorconfig-linter:
	$(EDITORCONFIG_LINTER_CMD)

.PHONY: jsonlint
jsonlint: ## Execute jsonlint
jsonlint: tools/jsonlint/vendor/bin/jsonlint
	find tests/resources -type f -name "*.json" -not -name "spreadsheets.json" \
		-exec grep -RhoP '"[A-Za-z0-9]+"(?=\s*:)' {} + \
		| grep -v '"majorDimension"' \
		| grep -vE '"[a-z0-9_]+"' \
		| sort -u \
		| tee /dev/stderr \
		| grep . && exit 1 || exit 0
	find tests/resources -type f -name "*.json" -not -name "spreadsheets.json" \
		-exec $(PHP) tools/jsonlint/vendor/bin/jsonlint {} \;

.PHONY: validate-autoloader
validate-autoloader: ## Execute cmheck on autoloader issues
validate-autoloader:
	@$(COMPOSER) dump-autoload -o --strict-psr --strict-ambiguous --dry-run

.PHONY: phpcsfixer
phpcsfixer: ## Execute PHP CS Fixer "Check"
phpcsfixer: tools/php-cs-fixer/vendor/bin/php-cs-fixer
	@$(PHP) tools/php-cs-fixer/vendor/bin/php-cs-fixer check --diff

.PHONY: phpcsfixer-fix
phpcsfixer-fix: ## Execute PHP CS Fixer "Fix"
phpcsfixer-fix: tools/php-cs-fixer/vendor/bin/php-cs-fixer
	@$(PHP) tools/php-cs-fixer/vendor/bin/php-cs-fixer fix

.PHONY: phpmd
phpmd: ## Execute phpmd
phpmd: tools/phpmd/vendor/bin/phpmd
	@$(PHP) tools/phpmd/vendor/bin/phpmd src,tests text phpmd.ruleset.xml

.PHONY: psalm
psalm: ## Execute psalm
psalm: tools/psalm/vendor/bin/psalm
	@$(PHP_CONT) rm -Rf var/cache/psalm
	@$(PHP) tools/psalm/vendor/bin/psalm -c psalm.xml --no-diff --show-info=false --no-cache --find-unused-psalm-suppress --no-suggestions
	@$(PHP) tools/psalm/vendor/bin/psalm -c psalm-src-only.xml --no-diff --show-info=false --no-cache --find-unused-psalm-suppress --no-suggestions

.PHONY: phpstan
phpstan: ## Execute phpstan analyse
phpstan: tools/phpstan/vendor/bin/phpstan
	@$(PHP) tools/phpstan/vendor/bin/phpstan clear-result-cache
	@$(PHP) tools/phpstan/vendor/bin/phpstan analyse --memory-limit=-1

.PHONY: deptrac
deptrac: ## Execute deptrac analyse
deptrac: tools/deptrac/vendor/bin/deptrac
	@$(PHP) tools/deptrac/vendor/bin/deptrac analyse --report-uncovered --fail-on-uncovered --cache-file=/app/var/cache/deptrac/.deptrac.cache

## —— Integration 🗂️ ———————————————————————————————————————————————————————————————
.PHONY: integration
integration: ## Execute all integration tests
integration: newman

.PHONY: newman
newman: ## Execute newman
newman: newman-prepare newman-execute

.PHONY: newman-prepare
newman-prepare:
	@$(SYMFONY) --env=int app:update:labels
	@$(SYMFONY) --env=int app:update:games_collections_and_dex
	@$(SYMFONY) --env=int app:update:pokemons
	@$(SYMFONY) --env=int app:update:regional_dex_numbers
	@$(SYMFONY) --env=int app:update:games_availabilities
	@$(SYMFONY) --env=int app:update:games_shinies_availabilities
	@$(SYMFONY) --env=int app:update:collections_availabilities
	@$(SYMFONY) --env=int app:calculate:game_bundles_availabilities
	@$(SYMFONY) --env=int app:calculate:game_bundles_shinies_availabilities
	@$(SYMFONY) --env=int app:calculate:dex_availabilities
	@$(SYMFONY) --env=int app:calculate:pokemon_availabilities

.PHONY: newman-execute
newman-execute:
	$(DOCKER_COMP) up newman --no-recreate --menu=false

## —— Measures 📏 ———————————————————————————————————————————————————————————————
.PHONY: measures
measures: ## Execute all measures tools
measures: coverage infection

.PHONY: m
m: ## Alias of measures
m: measures

.PHONY: clear-build
clear-build: ## Clear build directory
	rm -Rf build/coverage*

build/coverage/coverage-xml: ## Generate coverage report
	$(DOCKER_COMP) exec \
		-e XDEBUG_MODE=coverage -T php \
		php vendor/bin/phpunit \
			--exclude-group="browser-testing" \
			--coverage-clover=build/coverage/coverage.xml \
			--coverage-xml=build/coverage/coverage-xml \
			--log-junit=build/coverage/junit.xml

.PHONY: coverage
coverage: ## Execute PHPUnit Coverage to check the score
coverage: clear-build build/coverage/coverage-xml
	@$(PHP_CONT) php tools/coverage/coverage.php build/coverage/coverage.xml 100 true \
	|| (echo "❌ Coverage check failed, generating HTML report..." && $(MAKE) coverage-html && exit 1)

.PHONY: coverage-html
coverage-html: ## Execute PHPUnit Coverage in HTML
	$(DOCKER_COMP) exec \
		-e XDEBUG_MODE=coverage -T php \
		php vendor/bin/phpunit \
			--exclude-group="browser-testing" \
			--coverage-html=build/coverage/coverage-html

.PHONY: clear-infection-cache
clear-infection-cache:
	@$(PHP_CONT) rm -Rf var/cache/infection

.PHONY: infection
infection: ## Execute all Infection testing
infection: build/coverage/coverage-xml tools/infection/vendor/bin/infection clear-infection-cache
	@$(PHP) tools/infection/vendor/bin/infection --threads=4 --no-progress \
		--skip-initial-tests --coverage=build/coverage \
		--min-msi=100 --min-covered-msi=100 \
		--filter=src

## —— Security 🛡️ ———————————————————————————————————————————————————————————————
.PHONY: security
security: ## Execute all security commands
security: composer-audit composer-audit-tools security-check

.PHONY: s
s: ## Alias of security
s: security

.PHONY: composer-audit
composer-audit: ## Execute Composer Audit
composer-audit: c=audit
composer-audit: composer

.PHONY: composer-audit-tools
composer-audit-tools: ## Execute Composer Audit on quality tools
composer-audit-tools:
	@for tool in tools/deptrac tools/infection tools/jsonlint tools/php-cs-fixer tools/phpmd tools/phpstan tools/psalm; do \
		echo "Auditing $$tool..."; \
		$(COMPOSER) audit --working-dir=$$tool; \
	done

.PHONY: security-check
security-check: ## Execute Symfony Security Checker
security-check:
	@$(PHP_CONT) symfony security:check

## —— Cleaning 🧽 ———————————————————————————————————————————————————————————————
.PHONY: clean-unused-files
clean-unused-files: ## Clean unused mocks files
clean-unused-files:
	tools/clean-unused-files/clean_unused_files.sh tests/resources/moco/Back/responses

.PHONY: clean-moco-routes
clean-moco-routes: ## Clean unused moco routes
clean-moco-routes:
	tools/clean-moco-routes/clean_moco_routes.sh tests/resources/moco/Back/moco.json

## —— Tools 🔧 ———————————————————————————————————————————————————————————————
tools/php-cs-fixer/vendor/bin/php-cs-fixer: ## Install php-cs-fixer
	@$(COMPOSER) install --working-dir=tools/php-cs-fixer --optimize-autoloader --no-dev

tools/phpmd/vendor/bin/phpmd: ## Install phpmd
	@$(COMPOSER) install --working-dir=tools/phpmd --optimize-autoloader --no-dev

tools/psalm/vendor/bin/psalm: ## Install psalm
	@$(COMPOSER) install --working-dir=tools/psalm --optimize-autoloader --no-dev

tools/phpstan/vendor/bin/phpstan: ## Install phpstan
	@$(COMPOSER) install --working-dir=tools/phpstan --optimize-autoloader --no-dev

tools/deptrac/vendor/bin/deptrac: ## Install deptrac
	@$(COMPOSER) install --working-dir=tools/deptrac --optimize-autoloader --no-dev

tools/infection/vendor/bin/infection: ## Install infection
	@$(COMPOSER) install --working-dir=tools/infection --optimize-autoloader --no-dev

tools/jsonlint/vendor/bin/jsonlint: ## Install jsonlint
	@$(COMPOSER) install --working-dir=tools/jsonlint --optimize-autoloader --no-dev

## —— Image 🐳 ———————————————————————————————————————————————————————————————
img-build: ## Build Docker image
	docker build --target php_prod -f ./.docker/php/Dockerfile -t ghcr.io/douzeensemble/pokenini-api:latest .
img-push: ## Push Docker image
	docker push ghcr.io/douzeensemble/pokenini-api:latest
