up: docker-up
down: docker-down
restart: docker-down docker-up
init: composer-install migrations fixtures

composer-install:
	docker compose exec -T -u www-data -e XDEBUG_MODE=off tournament-php-fpm composer install

migrations:
	docker compose exec -T -u www-data -e XDEBUG_MODE=off tournament-php-fpm php bin/console do:mig:mig -n

fixtures:
	docker compose exec -T -u www-data -e XDEBUG_MODE=off tournament-php-fpm php bin/console do:fix:loa -n

tests:
	docker compose exec -T -u www-data -e XDEBUG_MODE=off tournament-php-fpm php bin/phpunit

docker-up:
	docker compose up -d

docker-down:
	docker compose down --remove-orphans