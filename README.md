# ChatPulse (Laravel 12 • Modular Monolith)

Scalable chat backend for interview demos. Stack: **Docker, Nginx, Laravel 12, PostgreSQL, Redis, Reverb (WebSockets), Passport (OAuth2), Prometheus + Grafana, Elasticsearch + Kibana, Filebeat**. Structured as modules: `Modules/Auth`, `Modules/Org`, `Modules/Chat`.

## Quick start

```bash
cp .env.example .env
# set APP_URL, DB_*, REDIS_*, PASSPORT keys, REVERB_*, DEMO_USER_TOKEN, etc.

# build & run
docker compose up -d --build

# install PHP deps
docker compose exec php composer install
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate
docker compose exec php php artisan passport:install

# build assets
docker compose exec php bash -lc 'npm ci && npm run build'

# seed demo org/room/messages (dev)
docker compose exec php php artisan db:seed
```

## Open:

App demo: http://chatpulse.localhost:8080/demo

Prometheus: http://localhost:9090, Grafana: http://localhost:3000

Elasticsearch: http://localhost:9200, Kibana: http://localhost:5601


## API (sample)

POST /api/messages (room_id, body) — Bearer token (Passport)

GET /api/rooms/{room}/messages — cursor pagination

GET /api/organizations/{org}/rooms — rooms for current user

POST /api/rooms — create room (owner/admin), auto-membership

POST /api/rooms/{room}/members / DELETE .../{user} — manage members


## Observability

/metrics (Prometheus exposition)

Nginx & Laravel JSON logs → Filebeat → Elasticsearch → Kibana (Discover: filebeat-*)

Correlation: X-Request-Id across Nginx ⇄ Laravel logs


## Development

Modular PSR-4 namespaces: Modules\Auth, Modules\Org, Modules\Chat

Queue worker + Reverb (WebSockets) via Redis scaling


## Testing

```bash
docker compose exec php php artisan test
```

PHPUnit uses SQLite :memory:; see phpunit.xml overrides

Example tests: MeEndpointTest, PostMessageTest

## CI (GitHub Actions)

.github/workflows/ci.yml runs PHPUnit on PHP 8.3 with SQLite.

Extend with Node build and Docker image stages if desired.

## License

MIT (for interview/demo use)
