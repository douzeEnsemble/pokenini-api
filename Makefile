# Executables (local)
DOCKER = docker
DOCKER_COMP = docker compose

# Docker containers
ifeq (${CI}, true)
DOCKER_COMP_EXEC = $(DOCKER_COMP) exec -T
else
DOCKER_COMP_EXEC = $(DOCKER_COMP) exec
endif

PHP_CONT = $(DOCKER_COMP_EXEC) php
DATABASE_CONT = $(DOCKER_COMP_EXEC) database

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP_CONT) bin/console

# Misc
.DEFAULT_GOAL = help
.PHONY        = help build rebuild up install start stop sh data init_db data_app composer vendor sf cc tests phpunit testsunit testsfunctional quality phpcs phpcbf phpmd psalm phpstan integration newman measures coverage htmlcoverage infection

## —— 🎵 🐳 The Symfony-docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Directories and files 🐳 ————————————————————————————————————————————————————————————————
KEY_FILE := ./docker/apache/ssl/cert-key.pem
CERT_FILE := ./docker/apache/ssl/cert.pem

certs: ## Create ssl files
certs:
	mkdir -p ./docker/apache/ssl
	@if [ ! -e $(KEY_FILE) ] || [ ! -e $(CERT_FILE) ]; then \
		mkcert \
			-key-file $(KEY_FILE) \
			-cert-file $(CERT_FILE) \
			localhost 127.0.0.1 ::1 ; \
	fi

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	$(DOCKER_COMP) build
rebuild: ## Re-builds the Docker images (build with no cache)
	${DOCKER_COMP} build --no-cache

start: install up vendor cc data ## ## Start the project

up: ## Up Docker container
	$(DOCKER_COMP) up --wait

install: ## Install requirements
install: certs

stop: ## Stop the project
	$(DOCKER_COMP) down --remove-orphans

sh: ## Connect to the PHP FPM container
	@$(PHP_CONT) bash

## —— Data 💾 ————————————————————————————————————————————————————————————————
data: ## Initialize data
data: init_db data_app

init_db: ## Initialize database data
	$(SYMFONY) doctrine:database:drop --force --if-exists --env=dev
	$(SYMFONY) doctrine:database:create --env=dev
	$(SYMFONY) doctrine:migration:migrate --no-interaction --env=dev
	$(SYMFONY) doctrine:database:drop --force --if-exists --env=test
	$(SYMFONY) doctrine:database:create --env=test
	$(SYMFONY) doctrine:migration:migrate --no-interaction --env=test

data_app: ## Initialize app data
	$(SYMFONY) app:update:labels
	$(SYMFONY) app:update:games_and_dex
	$(SYMFONY) app:update:pokemons
	$(SYMFONY) app:update:regional_dex_numbers
	$(SYMFONY) app:update:games_availabilities
	$(SYMFONY) app:update:games_shinies_availabilities
	$(SYMFONY) app:calculate:game_bundles_availabilities
	$(SYMFONY) app:calculate:game_bundles_shinies_availabilities
	date
	$(SYMFONY) app:calculate:dex_availabilities
	date

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
	@$(COMPOSER) install --prefer-dist --no-progress --no-interaction
	@$(COMPOSER) clear-cache

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: ## Clear the cache
	@$(SYMFONY) cache:clear --env=dev
	@$(SYMFONY) cache:clear --env=test

## —— Tests 🧪 ———————————————————————————————————————————————————————————————
tests: ## Execute all tests
tests: phpunit

phpunit: ## Execute unit test
	@$(PHP) bin/console doctrine:schema:update --complete --force --env=test
	$(PHP) bin/phpunit

testsunit: ## Execute unit tests
	@$(PHP_CONT) bin/phpunit tests/Unit

testsfunctional: ## Execute functional tests
	@$(PHP_CONT) bin/phpunit tests/Functional

## —— Quality 👌 ———————————————————————————————————————————————————————————————
quality: ## Execute all quality analyses
quality: phpcs phpmd psalm phpstan

phpcs: ## Execute phpcs
	@$(PHP) vendor/bin/phpcs
phpcbf: ## Execute phpcbf (code beautifier) /!\ This could edit your code
	@$(PHP) vendor/bin/phpcbf

phpmd: ## Execute phpmd
	@$(PHP) vendor/bin/phpmd src,tests text ruleset.xml

psalm: ## Execute psalm
	@$(PHP) vendor/bin/psalm --show-info=false

phpstan: ## Execute phpstan analyse
	@$(PHP) vendor/bin/phpstan analyse --memory-limit=-1

## —— Integration 🗂️ ———————————————————————————————————————————————————————————————
integration: ## Execute all integration tests
integration: newman

newman: newman_prepare newman_execute## Execute newman

newman_prepare:
	@$(SYMFONY) --env=int app:update:labels
	@$(SYMFONY) --env=int app:update:games_and_dex
	@$(SYMFONY) --env=int app:update:pokemons
	@$(SYMFONY) --env=int app:update:regional_dex_numbers
	@$(SYMFONY) --env=int app:update:games_availabilities
	@$(SYMFONY) --env=int app:update:games_shinies_availabilities
	@$(SYMFONY) --env=int app:calculate:game_bundles_availabilities
	@$(SYMFONY) --env=int app:calculate:game_bundles_shinies_availabilities
	@$(SYMFONY) --env=int app:calculate:dex_availabilities

newman_execute:
	$(DOCKER) run --rm --name pokenini-api-newman \
		--network=pokenini-api_default \
		-v ./tests/integration:/etc/newman \
		-t postman/newman:alpine run collection.json

## —— Measures 📏 ———————————————————————————————————————————————————————————————
measures: ## Execute all measures tools
measures: coverage infection

coverage: ## Execute PHPUnit Coverage to check the score
	$(DOCKER_COMP) exec \
		-e XDEBUG_MODE=coverage -T php \
		php bin/phpunit --coverage-clover=coverage.xml
	@$(PHP_CONT) php tests/tools/coverage.php coverage.xml 100 true

htmlcoverage: ## Execute PHPUnit Coverage in HTML
	$(DOCKER_COMP) exec \
		-e XDEBUG_MODE=coverage -T php \
		php bin/phpunit --coverage-html=tests/coverage

infection: ## Execute Infection (Mutation testing)
	@$(PHP) vendor/bin/infection --threads=4 --show-mutations \
		--min-msi=100 --min-covered-msi=100 \
		--logger-html='tests/mutation/index.html'
