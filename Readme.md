# Task Manager

## Requirements
- Symfony 7+
- GraphQL
- Docker
- Event Sourcing
- CQRS
- Use Strategy Design Pattern
- Use Factory Design Pattern

### Features

- Fetch users from [jsonplaceholder](https://jsonplaceholder.typicode.com/users) API
- User Authentication
- Task create ENDPOINT
- Task update status ENDPOINT
- Task list ENDPOINTS
- Task change history ENDPOINT

## How to run project locally
1. Clone the repository:
   ```bash
   git clone
   ```
2. Navigate to the project directory:
   ```bash
   cd task-manager
   ```
3. Env:
    ```bash
    cp .env .env.local
    ```
    Update the `.env` file with your database credentials and other necessary configurations.
4. Docker:
    ```
    docker compose up -d --build
    docker compose exec app composer install --optimize-autoloader --no-interaction
   ```
   
5. Database:
    ```bash
    docker compose exec app php bin/console doctrine:database:create --if-not-exists
    docker compose exec app php bin/console doctrine:migrations:migrate
    ```

6. Fake users from jsonplaceholder API:
    ```bash
    docker compose exec app php bin/console app:fetch-users
    ```

## Tests
Before running the tests, do this;
```bash
docker compose exec app php bin/console --env=test doctrine:database:create --if-not-exists
docker compose exec app php bin/console --env=test doctrine:migrations:migrate -n
```
