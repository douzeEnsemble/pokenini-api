# Executables (local)
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
.PHONY        = help build up start stop logs sh composer vendor sf cc tests quality integration measures

## —— 🎵 🐳 The Symfony-docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

install: ## Install requirements
install: build start data stop

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	$(DOCKER_COMP) build

start: ## Start the project
	$(DOCKER_COMP) up -d

stop: ## Stop the project
	$(DOCKER_COMP) down --remove-orphans

sh: ## Connect to the PHP FPM container
	@$(PHP_CONT) sh

waitup:
	$(DATABASE_CONT) pg_isready -U app
	while ! $(PHP_CONT) /usr/local/bin/docker-healthcheck; do \
		sleep 1; \
	done
	echo 'Wait is over'

## —— Data 💾 ————————————————————————————————————————————————————————————————
data: ## Initialize data
data: waitup init_db data_app

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
	$(SYMFONY) app:calculate:dex_availabilities

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf

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

newman: ## Execute newman
	@$(SYMFONY) --env=int app:update:labels
	@$(SYMFONY) --env=int app:update:games_and_dex
	@$(SYMFONY) --env=int app:update:pokemons
	@$(SYMFONY) --env=int app:update:regional_dex_numbers
	@$(SYMFONY) --env=int app:update:games_availabilities
	@$(SYMFONY) --env=int app:update:games_shinies_availabilities
	@$(SYMFONY) --env=int app:calculate:game_bundles_availabilities
	@$(SYMFONY) --env=int app:calculate:game_bundles_shinies_availabilities
	@$(SYMFONY) --env=int app:calculate:dex_availabilities
	$(DOCKER_COMP) --env-file .env.int run newman run collection.json

## —— Measures 📏 ———————————————————————————————————————————————————————————————
measures: ## Execute all measures tools
measures: coverage infection

coverage: ## Execute PHPUnit Coverage to check the score
	$(DOCKER_COMP) exec -e XDEBUG_MODE=coverage -T php php bin/phpunit --coverage-clover=coverage.xml
	@$(PHP_CONT) php tests/tools/coverage.php coverage.xml 100 true

htmlcoverage: ## Execute PHPUnit Coverage in HTML
	$(DOCKER_COMP) exec -e XDEBUG_MODE=coverage -T php php bin/phpunit --coverage-html=tests/coverage

infection: ## Execute Infection (Mutation testing)
	@$(PHP) vendor/bin/infection --threads=4 --show-mutations --min-msi=100 --min-covered-msi=100 --logger-html='tests/mutation/index.html'
