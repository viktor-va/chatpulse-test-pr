# ChatPulse - Laravel Chat Backend

ChatPulse is a scalable chat backend built with **Laravel 12**, designed to demonstrate production-grade backend engineering for interviews and portfolio purposes.
It starts as a **modular monolith** and evolves into a **hybrid mono-micro architecture**. Structured as 2 modules: `Modules/Auth`, `Modules/Org` and `chat-api` microservice (all in monorepo). With full observability, CI/CD, and test coverage.


## 🚀 Tech Stack

| Category         | Technology                                   |
|------------------|----------------------------------------------|
| Core             | PHP 8.3 + Laravel 12                         |
| Containerization | Docker & Docker Compose                      |
| Database         | PostgreSQL                                   |
| Cache / Queue    | Redis                                        |
| Realtime         | Laravel Reverb (WebSockets)                  |
| Web Server       | Nginx                                        |
| Observability    | Prometheus + Grafana, Elasticsearch + Kibana |
| CI/CD            | GitHub Actions + local `act`                 |
| Tests            | PHPUnit                                      |
| Load Test        | k6 (manual baseline)                         |

🐳 All components (App, Chat-API, Reverb, Nginx, Postgres, Redis, Prometheus, Grafana, Elasticsearch, Kibana) run as Docker containers for full local reproducibility.


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

App demo: http://chatpulse.localhost:8080/demo

Prometheus: http://localhost:9090, Grafana: http://localhost:3000

Kibana: http://localhost:5601


## API Samples

### 1. Authenticate & get token

```bash
curl -X POST http://chatpulse.localhost:8080/oauth/token \
  -H "Content-Type: application/json" \
  -d '{
        "grant_type": "password",
        "client_id": "<client_id>",
        "client_secret": "<client_secret>",
        "username": "dev@chatpulse.local",
        "password": "password"
      }'
```

### 2. Get current authenticated user

```bash
curl -H "Authorization: Bearer $TOKEN" \
     http://chatpulse.localhost:8080/api/me
```

### 3. List chat rooms for user

```bash
curl -H "Authorization: Bearer $TOKEN" \
     http://chatpulse.localhost:8080/api/chat/rooms
```

### 4. List messages in a room

```bash
curl -H "Authorization: Bearer $TOKEN" \
     http://chatpulse.localhost:8080/api/chat/rooms/$ROOM_ID/messages
```

### 5. Post a new message

```bash
curl -X POST http://chatpulse.localhost:8080/api/chat/messages \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "room_id=$ROOM_ID&body=Test message from curl"
```

### 6. Add a room member

```bash
USER_ID="01K8P1VHQWJMQ5TZ5D0RB5V8KZ"
curl -X POST http://chatpulse.localhost:8080/api/chat/rooms/$ROOM_ID/members \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "user_id=$USER_ID"
```

### 7. Remove a room member

```bash
curl -X DELETE http://chatpulse.localhost:8080/api/chat/rooms/$ROOM_ID/members/$USER_ID \
  -H "Authorization: Bearer $TOKEN"
```

## Observability

/metrics (Prometheus exposition)

Nginx & Laravel JSON logs → Filebeat → Elasticsearch → Kibana (Discover: filebeat-*)

Correlation: X-Request-Id across Nginx ⇄ Laravel logs


## Test Coverage

| Scope | Tests |
|-------|--------|
| Auth | `MeEndpointTest` |
| Chat Domain | `PostMessageTest`, `RoomMessagesTest`, `BroadcastMessageTest` |
| Gateway | `GatewaySecretTest` |

Run tests:

```bash
docker compose exec php php artisan test
```

## Performance Baseline (Manual)

```bash
docker run --rm --network=host \
  -e BASE_URL=http://chatpulse.localhost:8080 \
  -e TOKEN=$TOKEN \
  -e ROOM_ID=$ROOM_ID \
  -v "$(pwd)/loadtest:/loadtest" \
  grafana/k6 run /loadtest/chat-messages.js
```
Check metrics in Prometheus (localhost:9000) and logs in Kibana (localhost:5601).


## CI (GitHub Actions)

.github/workflows/ci.yml runs PHPUnit on PHP 8.3 with SQLite.

## License

MIT (for interview/demo use)
