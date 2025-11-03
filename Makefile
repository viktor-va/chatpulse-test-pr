.PHONY: up down rebuild sh-php artisan composer

up:
\tdocker compose up -d

down:
\tdocker compose down

rebuild:
\tdocker compose build --no-cache

sh-php:
\tdocker compose exec php bash

artisan:
\tdocker compose exec php php artisan $(cmd)

composer:
\tdocker compose exec php composer $(cmd)

ws-logs:
\tdocker compose logs -f reverb

queue-logs:
\tdocker compose logs -f queue-worker

