build:
	docker-compose build
	docker-compose up -d

start:
	docker-compose up -d

stop:
	docker-compose down --remove-orphans

phpunit:
	docker-compose exec php bin/console doctrine:schema:update --force --env=test
	docker-compose exec php bin/phpunit
