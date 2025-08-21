# Task Manager

## How to run project locally
1. Clone the repository:
   ```bash
   git clone
   ```
2. Navigate to the project directory:
   ```bash
   cd task-manager
   ```
3. Docker:
    ```
    docker-compose up -d --build
   ```

## Tests
Before running the tests, do this;
```bash
docker compose exec app php bin/console --env=test doctrine:database:create --if-not-exists
docker compose exec app php bin/console --env=test doctrine:migrations:migrate -n
```
