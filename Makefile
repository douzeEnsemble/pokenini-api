# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
ifeq (${CI}, true)
PHP_CONT = $(DOCKER_COMP) exec -T php
else
PHP_CONT = $(DOCKER_COMP) exec php
endif

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP_CONT) bin/console

# Misc
.DEFAULT_GOAL = help
.PHONY        = help build up start down logs sh composer vendor sf cc deploy

## —— 🎵 🐳 The Symfony-docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

install: ## Install requirements
install: build

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	$(DOCKER_COMP) build

start: ## Start the project
	$(DOCKER_COMP) up -d

stop: ## Stop the project
	$(DOCKER_COMP) down --remove-orphans

sh: ## Connect to the PHP FPM container
	@$(PHP_CONT) sh

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
tests: phpstan phpunit

phpstan: ## Execute phpstan analyse
	@$(PHP) vendor/bin/phpstan analyse --memory-limit=-1

phpunit: ## Execute unit test
	@$(PHP) bin/console doctrine:schema:update --force --env=test
	$(PHP) bin/phpunit

## —— Quality 👌 ———————————————————————————————————————————————————————————————
quality: ## Execute all quality analyses
quality: phpcs phpmd psalm

phpcs: ## Execute phpcs
	@$(PHP) vendor/bin/phpcs
phpcbf: ## Execute phpcbf (code beautifier) /!\ This could edit your code
	@$(PHP) vendor/bin/phpcbf

phpmd: ## Execute phpmd
	@$(PHP) vendor/bin/phpmd src,tests text ruleset.xml

psalm: ## Execute psalm
	@$(PHP) vendor/bin/psalm --show-info=false

## —— Integration 🗂️ ———————————————————————————————————————————————————————————————
integration: ## Execute all integration tests
integration: newman

newman: ## Execute newman
	@$(SYMFONY) --env=int app:update:labels
	@$(SYMFONY) --env=int app:update:games_and_dexes
	@$(SYMFONY) --env=int app:update:pokemon
	@$(SYMFONY) --env=int app:update:regional_dex_number
	@$(SYMFONY) --env=int app:update:game_availability
	@$(SYMFONY) --env=int app:calculate:game_bundle_availability
	@$(SYMFONY) --env=int app:calculate:dex_availability
	$(DOCKER_COMP) --env-file .env.int run newman run collection.json

## —— Deployment 🚀 ————————————————————————————————————————————————————————————————
deploy: ## Deployment
	rm -Rf ~/tmp/deploy/pokenini-api
	mkdir -p ~/tmp/deploy/pokenini-api
	heroku git:clone -a pokenini-api ~/tmp/deploy/pokenini-api/heroku
	git clone git@github.com:RenaudDouze/pokenini-api.git ~/tmp/deploy/pokenini-api/project
	rm -Rf ~/tmp/deploy/pokenini-api/project/.git
	cp -R ~/tmp/deploy/pokenini-api/project/* ~/tmp/deploy/pokenini-api/heroku/
	cd ~/tmp/deploy/pokenini-api/heroku; \
    	git add --all; \
		git commit --allow-empty -m "Deployment"; \
		git push heroku main
	rm -Rf ~/tmp/deploy/pokenini-api

runs: ## Run commands into production env
	mkdir -p ~/tmp/deploy/pokenini-api
	heroku git:clone -a pokenini-api ~/tmp/deploy/pokenini-api/heroku
	cd ~/tmp/deploy/pokenini-api/heroku; \
		heroku run php bin/console app:import:pokemon resources/data/pokemon_list.csv; \
		heroku run php bin/console app:import:game_availability resources/data/bulbapedia_availability.csv; \
		heroku run php bin/console app:calculate:game_bundle_availability; \
		heroku run php bin/console app:calculate:dex_availability
	rm -Rf ~/tmp/deploy/pokenini-api
