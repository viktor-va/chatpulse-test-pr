# ChatPulse - Laravel Chat Backend

ChatPulse is a scalable chat backend built with **Laravel 12**, designed to demonstrate production-grade backend engineering for interviews and portfolio purposes.
It starts as a **modular monolith** and evolves into a **hybrid mono-micro architecture**. Structured as 2 modules: `Modules/Auth`, `Modules/Org` and `chat-api` microservice (all in monorepo). With full observability, CI/CD, and test coverage.


## Tech Stack

| Category         | Technology                                   |
|------------------|----------------------------------------------|
| Core             | PHP 8.3 + Laravel 12                         |
| Auth             | Laravel Sanctum (Bearer Tokens)              |
| Containerization | Docker & Docker Compose                      |
| Database         | PostgreSQL                                   |
| Cache / Queue    | Redis                                        |
| Realtime         | Laravel Reverb (WebSockets)                  |
| Web Server       | Nginx                                        |
| Observability    | Prometheus + Grafana, Elasticsearch + Kibana |
| CI/CD            | GitHub Actions (or use `act` for local)      |
| Tests            | PHPUnit                                      |
| Load Test        | k6 (manual baseline)                         |

🐳 All components (App, Chat-API, Reverb, Nginx, Postgres, Redis, Prometheus, Grafana, Elasticsearch, Kibana) run as Docker containers for full local reproducibility.


## Quick start

### You will need installed docker and git to follow the steps:

```bash
# get repo & create .env
git clone https://github.com/viktor-va/chatpulse-test-pr.git
cd chatpulse-test-pr
cp src/.env.example src/.env

# build & run
docker compose up -d --build

# install PHP deps and app key
docker compose exec php composer install --no-interaction --prefer-dist
docker compose exec php php artisan key:generate

# migrate & seed
docker compose exec php php artisan migrate:fresh --seed

# build assets
docker compose exec php bash -lc 'npm ci && npm run build'

# reset caches and start containers that depends on php artisan
docker compose exec php php artisan optimize:clear
docker compose up -d


```

App demo: http://chatpulse.localhost:8080/demo (will work after API Samples step 1)

Prometheus: http://localhost:9090, Grafana: http://localhost:3000

Kibana: http://localhost:5601


## API Samples

### 1. Get token

```bash
TOKEN=$(curl -s -X POST http://chatpulse.localhost:8080/api/token \
  -d "email=dev@chatpulse.local" -d "password=secret" | jq -r .token)
echo "$TOKEN"

# add token to src/.env
grep -q '^DEMO_USER_TOKEN=' src/.env && sed -i '' "s/^DEMO_USER_TOKEN=.*/DEMO_USER_TOKEN=$TOKEN/" src/.env || echo "DEMO_USER_TOKEN=$TOKEN" >> src/.env

#reset caches
docker compose exec php php artisan optimize:clear

# so now you can check http://chatpulse.localhost:8080/demo
```

### 2. Get current authenticated user

```bash
curl -H "Authorization: Bearer $TOKEN" http://chatpulse.localhost:8080/api/me | jq
```

### 3. List your organizations

```bash
curl -s -H "Authorization: Bearer $TOKEN" http://chatpulse.localhost:8080/api/organizations | jq
# save ORG_ID
```


### 3. List chat rooms for organization

```bash
curl -s -H "Authorization: Bearer $TOKEN" \
  "http://chatpulse.localhost:8080/api/chat/organizations/$ORG_ID/rooms" | jq
# save ROOM_ID (better to use one from demo page)
```

### 4. List messages in a room

```bash

curl -H "Authorization: Bearer $TOKEN" \
     http://chatpulse.localhost:8080/api/chat/rooms/$ROOM_ID/messages | jq
```

### 5. Post a new message

```bash
curl -X POST http://chatpulse.localhost:8080/api/chat/messages \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "room_id=$ROOM_ID&body=Test message from curl" | jq
# check demo page or list messages in room to see posted message
```

### 6. List all users

```bash
curl -s -H "Authorization: Bearer $TOKEN" \
  "http://chatpulse.localhost:8080/api/users" | jq
# pick one user and save it to USER_ID for further endpoint requests
```

### 7. Add a room member

```bash
curl -X POST http://chatpulse.localhost:8080/api/chat/rooms/$ROOM_ID/members \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "user_id=$USER_ID" | jq
```

### 8. Check room members

```bash
curl -s -H "Authorization: Bearer $TOKEN" \
  "http://chatpulse.localhost:8080/api/chat/rooms/$ROOM_ID/members" | jq
```

### 9. Remove a room member

```bash
curl -X DELETE http://chatpulse.localhost:8080/api/chat/rooms/$ROOM_ID/members/$USER_ID \
  -H "Authorization: Bearer $TOKEN"
# you can check room members again if you want  
```

### 10. Logout

```bash
curl -i -X DELETE \
  -H "Authorization: Bearer $TOKEN" http://chatpulse.localhost:8080/api/token
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
  -e BASE_URL=http://localhost:8080 \
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
