# Docker setup

This folder runs the gateway test services without Laravel Sail.

- auth-service: http://127.0.0.1:3000
- product-service mock 1: http://127.0.0.1:3001
- order-service: http://127.0.0.1:3002
- product-service mock 2: http://127.0.0.1:3003
- product-service mock 3: http://127.0.0.1:3004
- product-service mock 4: http://127.0.0.1:3005
- Adminer: http://127.0.0.1:${ADMINER_PORT:-8081}
- MySQL: 127.0.0.1:${FORWARD_DB_PORT:-3306}

Auth and order are Laravel containers. The product ports are lightweight mock instances for gateway load-balancing tests. All Laravel services use one MySQL database: `services_db`.

## Start

Run these commands from this `docker` folder:

```powershell
docker compose up -d --build
```

To start only the product mock instances:

```powershell
docker compose up -d --build product-service product-service-1 product-service-2 product-service-3
```

## Product mock

The product mock lives in `product-mock/server.php` and returns static JSON. It uses the shared lightweight PHP image, but does not boot Laravel, connect to MySQL, run migrations, or load Composer autoload, so it is much lighter for gateway testing.

Useful test URLs:

```powershell
curl.exe http://127.0.0.1:3001/api/products
curl.exe http://127.0.0.1:3003/api/products
curl.exe http://127.0.0.1:3004/api/products
curl.exe http://127.0.0.1:3005/api/products
```

Supported mock routes:

- `GET /health`
- `GET /api/health`
- `GET /api/products`
- `GET /api/product/{id}`
- `POST /api/service-accounts/token`
- `POST /api/product/create`
- `PUT /api/product/update/{id}`
- `DELETE /api/product/delete/{id}`

Each response includes `instance`, for example `product-mock-3001`, so gateway load balancing can be checked easily. In `../go-api-gateway/.env`, set `PRODUCT_SERVICE_URLS` to the four mock URLs: `http://localhost:3001/api,http://localhost:3003/api,http://localhost:3004/api,http://localhost:3005/api`.


## Order mock

The order mock lives in `order-mock/server.php` and returns static JSON. It uses the shared lightweight PHP image, but does not boot Laravel, connect to MySQL, run migrations, or load Composer autoload.

Useful test URLs:

```powershell
curl.exe http://127.0.0.1:3002/api/health
curl.exe http://127.0.0.1:3002/api/orders
curl.exe http://127.0.0.1:3002/api/order/1
```

Supported mock routes:

- `GET /health`
- `GET /api/health`
- `GET /api/orders`
- `GET /api/order/{id}`
- `POST /api/service-accounts/token`
- `POST /api/order/create`
- `PUT /api/order/update/{id}`
- `DELETE /api/order/delete/{id}`

Each response includes `instance`, for example `order-mock-3002`.
## Migrate and seed

Run migrations for the Laravel services that are not mocked:

```powershell
docker compose exec auth-service php artisan migrate --force
```

Run seeders:

```powershell
docker compose exec auth-service php artisan db:seed --force
```

Product mock and order mock do not need migrate/seed. If you switch `product-service` back to the real Laravel product service, run:

```powershell
docker compose exec product-service php artisan migrate --force
docker compose exec product-service php artisan db:seed --force
```

The Docker compose file overrides the database/session/cache settings inside Laravel containers:

- `DB_HOST=mysql`
- `DB_DATABASE=services_db`
- `SESSION_DRIVER=file`
- `CACHE_STORE=file`

So the services can run even if a local service `.env` still has an old database name.

## Adminer login

Open http://127.0.0.1:8081 by default, or the port set in `ADMINER_PORT`, and use:

- System: MySQL
- Server: mysql
- Username: root
- Password: leave empty
- Database: services_db

To change the Adminer port, edit `.env`:

```dotenv
ADMINER_PORT=8082
```

Then restart Adminer:

```powershell
docker compose up -d adminer
```

## Useful commands

```powershell
docker compose ps
docker compose logs -f product-service
docker compose down
docker compose down -v
```

Use `docker compose down -v` only when you want to delete the MySQL data volume and start with a fresh database.
