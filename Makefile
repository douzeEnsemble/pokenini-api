# Executables (local)
DOCKER = docker
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP) bin/console
DOCKERCOMPOSE_LINTER_CMD = docker run -t --rm -v ${PWD}:/app zavoloklom/dclint:3.1.0-alpine
DOTENV_LINTER_CMD = docker run -t --rm -v ${PWD}:/app -w /app dotenvlinter/dotenv-linter:3.3.0

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
start: install up vendor cc

.PHONY: up
up: ## Up Docker container
up: up-process up-after

up-process:
	$(DOCKER_COMP) up --wait

up-after:

.PHONY: install
install: ## Install requirements
install: .env .env.dev.local

.PHONY: stop
stop: ## Stop the project
	$(DOCKER_COMP) down --remove-orphans

.PHONY: destruct
destruct: ## Destruct the project
	$(DOCKER_COMP) down --remove-orphans --volumes database moco.sheets.int moco.sheets.test newman php web --rmi all

.PHONY: logs
logs: ## Containers logs
	@$(DOCKER_COMP) logs -f -n 0

.PHONY: bash
bash: ## Connect to the PHP container
	@$(PHP_CONT) bash

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
	$(SYMFONY) app:update:labels
	$(SYMFONY) app:update:games_collections_and_dex
	$(SYMFONY) app:update:pokemons
	$(SYMFONY) app:update:regional_dex_numbers
	$(SYMFONY) app:update:games_availabilities
	$(SYMFONY) app:update:games_shinies_availabilities
	$(SYMFONY) app:update:collections_availabilities
	$(SYMFONY) app:calculate:game_bundles_availabilities
	$(SYMFONY) app:calculate:game_bundles_shinies_availabilities
	$(SYMFONY) app:calculate:dex_availabilities
	$(SYMFONY) app:calculate:pokemon_availabilities

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
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader 
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/php-cs-fixer 
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/phpmd 
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/psalm 
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/phpstan 
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/infection
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/jsonlint 
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/deptrac 
	@$(COMPOSER) update --bump-after-update --with-all-dependencies --optimize-autoloader --working-dir=tools/phpinsights 

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
.PHONY: sf
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

.PHONY: cc
cc: ## Clear the cache
	@$(SYMFONY) cache:clear --env=dev
	@$(SYMFONY) cache:clear --env=test

## —— Tests 🧪 ———————————————————————————————————————————————————————————————
.PHONY: tests
tests: ## Execute all tests
tests: 
	@$(PHP) bin/console doctrine:schema:update --force --env=test
	$(PHP) vendor/bin/phpunit tests/src

.PHONY: tests-defect
tests-defect: ## Execute tests and stop when one defect
tests-defect:
	$(PHP) vendor/bin/phpunit tests/src --stop-on-defect

.PHONY: t
t: ## Alias of tests
t: tests

.PHONY: tests-unit
tests-unit: ## Execute unit tests
	@$(PHP_CONT) vendor/bin/phpunit tests/src/Unit

.PHONY: tests-functional
tests-functional: ## Execute functional tests
	@$(PHP_CONT) vendor/bin/phpunit tests/src/Functional

.PHONY: tu
tu: ## Alias of tests-unit
tu: tests-unit

.PHONY: tf
tf: ## Alias of tests-functional
tf: tests-functional

.PHONY: ti
ti: ## Alias of tests-functional
ti: tests-functional

.PHONY: tests-api-mocked
tests-api-mocked: ## Execute tests on the group api-mocked-testing only
	@$(PHP_CONT) vendor/bin/phpunit tests/src/Functional --group=api-mocked-testing --stop-on-defect --no-progress --no-logging

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
		docker run -t --rm -v ${PWD}:/app hadolint/hadolint:2.12.0-alpine hadolint "/app/$$dockerfile"; \
	done

.PHONY: dotenv-linter
dotenv-linter: ## Run DotEnv linter
	$(DOTENV_LINTER_CMD) -r

.PHONY: dotenv-linter
dotenv-fixer: ## Run DotEnv fixer
	$(DOTENV_LINTER_CMD) fix -r --no-backup

.PHONY: code-quality
code-quality: ## Execute all code quality analyses
code-quality: validate-autoloader phpcsfixer phpmd psalm phpstan deptrac

.PHONY: cq
cq: ## Alias of code-quality
cq: code-quality

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
	@$(PHP) tools/psalm/vendor/bin/psalm --show-info=false --no-cache --find-unused-psalm-suppress --no-suggestions --taint-analysis

.PHONY: psalm-fix
psalm-fix: ## Execute psalm auto fixing
psalm-fix: tools/psalm/vendor/bin/psalm
	@$(PHP) tools/psalm/vendor/bin/psalm --alter --issues=UnnecessaryVarAnnotation,UnusedVariable,PossiblyUnusedMethod,MissingParamType

.PHONY: phpstan
phpstan: ## Execute phpstan analyse
phpstan: tools/phpstan/vendor/bin/phpstan
	@$(PHP) tools/phpstan/vendor/bin/phpstan clear-result-cache
	@$(PHP) tools/phpstan/vendor/bin/phpstan analyse --memory-limit=-1

.PHONY: deptrac
deptrac: ## Execute deptrac analyse
deptrac: tools/deptrac/vendor/bin/deptrac
	@$(PHP) tools/deptrac/vendor/bin/deptrac analyse --report-uncovered --fail-on-uncovered --cache-file=/app/var/cache/deptrac/.deptrac.cache

.PHONY: phpinsights
phpinsights: ## Execute phpinsights
phpinsights: tools/phpinsights/vendor/bin/phpinsights
	@$(PHP) tools/phpinsights/vendor/bin/phpinsights

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
security: composer-audit security-checker

.PHONY: s
s: ## Alias of security
s: security

.PHONY: composer-audit
composer-audit: ## Execute Composer Audit
composer-audit: c=audit
composer-audit: composer

tools/php-security-checker:
	mkdir tools/php-security-checker

tools/php-security-checker/local-php-security-checker: ## Download the file if needed
tools/php-security-checker/local-php-security-checker: tools/php-security-checker
	wget https://github.com/fabpot/local-php-security-checker/releases/download/v2.1.3/local-php-security-checker_linux_amd64 -O tools/php-security-checker/local-php-security-checker
	chmod a+x tools/php-security-checker/local-php-security-checker

.PHONY: security-checker
security-checker: ## Execute Security Checker
security-checker: tools/php-security-checker/local-php-security-checker
	tools/php-security-checker/local-php-security-checker

.PHONY: owasp-check
owasp-check: ## Execute OWASP Dependency Check
owasp-check: 
	@tools/owasp-check/dependency-check.sh ${NVD_API_KEY}

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

tools/phpinsights/vendor/bin/phpinsights: ## Install phpinsights
	@$(COMPOSER) install --working-dir=tools/phpinsights --optimize-autoloader --no-dev

## —— Image 🐳 ———————————————————————————————————————————————————————————————
img-build: ## Build Docker image
	docker build --target php_prod -f ./.docker/php/Dockerfile -t ghcr.io/douzeensemble/pokenini-api:latest .
img-push: ## Push Docker image
	docker push ghcr.io/douzeensemble/pokenini-api:latest
