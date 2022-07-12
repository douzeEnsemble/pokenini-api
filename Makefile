help:
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

build: ## Build the project (be careful can take time)
	docker-compose build

start: ## Start the project
	docker-compose up -d

stop: ## Stop the project
	docker-compose down --remove-orphans

phpunit: ## Execute unit test
	docker-compose exec php bin/console doctrine:schema:update --force --env=test
	docker-compose exec php bin/phpunit
