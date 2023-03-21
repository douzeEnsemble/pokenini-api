release: bash ./release-tasks.sh
web: vendor/bin/heroku-php-apache2 public/
worker: php bin/console messenger:consume async