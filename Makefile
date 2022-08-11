# Executables (local)
DOCKER_COMP = docker-compose

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
	@$(PHP) vendor/bin/phpstan analyse

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
	@$(PHP) vendor/bin/psalm --show-info=true


## —— Integration 🗂️ ———————————————————————————————————————————————————————————————
integration: ## Execute all integration tests
integration: newman

newman: ## Execute newman
ifeq (${CI}, true)
	$(DOCKER_COMP) up -d
	@$(SYMFONY) app:import:pokemon resources/data/pokemon_list.csv
	@$(SYMFONY) app:import:game_availability resources/data/bulbapedia_availability.csv
	@$(SYMFONY) app:calculate:game_bundle_availability
	@$(SYMFONY) app:calculate:dex_availability
	$(DOCKER_COMP) run -T newman run collection.json
else
	$(DOCKER_COMP) run newman run collection.json
endif


## —— Deployment 🚀 ————————————————————————————————————————————————————————————————
deploy: ## Deployment
	rm -Rf ~/tmp/deploy/pokenini-api
	mkdir -p ~/tmp/deploy
	git clone git@github.com:RenaudDouze/pokenini-api.git ~/tmp/deploy/pokenini-api
	cd ~/tmp/deploy/pokenini-api/api; \
	    git init -b main; \
	    heroku git:remote -a pokenini-api; \
        git add --all; \
		git commit --allow-empty -m "Deployment"; \
		git push heroku main
	rm -Rf ~/tmp/deploy/pokenini-api
