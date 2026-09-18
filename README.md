# Laravel Docker Deployment

A Laravel 13 application containerized with Docker and deployed locally using a production Docker Compose configuration and images hosted on GitHub Container Registry (GHCR).

## Stack

* Laravel 13.26.1
* PHP 8.4-FPM
* Nginx 1.31
* MariaDB 11
* Redis
* Docker
* Docker Compose
* GitHub Container Registry (GHCR)

## Architecture

```text
                         Browser
                            |
                            | HTTP :8082
                            v
                  +-------------------+
                  |   Nginx Container |
                  |      Port 80      |
                  +---------+---------+
                            |
                            | FastCGI :9000
                            v
                  +-------------------+
                  | PHP-FPM Container |
                  | Laravel + PHP 8.4 |
                  +---------+---------+
                            |
                  +---------+---------+
                  |                   |
                  v                   v
          +-------------+       +-----------+
          |   MariaDB   |       |   Redis   |
          |   Database  |       |   Cache   |
          +-------------+       +-----------+
```

## Docker Images

The application uses two custom Docker images.

### PHP / Laravel Image

```text
ghcr.io/zainmajeedch/laravel-docker-php:1.0
```

This image contains:

* PHP 8.4-FPM
* Required PHP extensions
* Composer
* Laravel application
* Production Composer dependencies
* Laravel storage permissions
* PHP-FPM running on port 9000

### Nginx Image

```text
ghcr.io/zainmajeedch/laravel-docker-nginx:1.0
```

This image contains:

* Nginx
* Laravel Nginx configuration
* Laravel `public` directory

Nginx serves the web application and forwards PHP requests to the PHP-FPM container.

## GHCR

The custom Docker images are stored in GitHub Container Registry.

PHP / Laravel:

```text
ghcr.io/zainmajeedch/laravel-docker-php:1.0
```

Nginx:

```text
ghcr.io/zainmajeedch/laravel-docker-nginx:1.0
```

The production Compose configuration pulls these images from GHCR instead of building them locally.

## Production Compose

The production deployment is defined in:

```text
compose.prod.yaml
```

The production Compose file uses the following services:

* `php`
* `nginx`
* `mariadb`
* `redis`

The PHP and Nginx services use the custom images hosted on GHCR.

```yaml
php:
  image: ghcr.io/zainmajeedch/laravel-docker-php:1.0

nginx:
  image: ghcr.io/zainmajeedch/laravel-docker-nginx:1.0
```

## Network

All application services communicate through the Docker network:

```text
laravel-network
```

Docker Compose service names are used for internal communication.

For example:

```text
DB_HOST=mariadb
REDIS_HOST=redis
```

Nginx communicates with PHP-FPM using:

```text
php:9000
```

## Volumes

Named Docker volumes are used for persistent application data:

```text
laravel_storage
laravel_mariadb_data
laravel_redis_data
```

The MariaDB volume persists database data, while the storage volume persists Laravel application storage.

## Production Configuration

The production Compose configuration sets:

```text
APP_ENV=production
APP_DEBUG=false
```

The application uses:

```text
Database: MariaDB
Cache: Redis
Sessions: Database
Queue: Database
```

The application key is supplied through the `APP_KEY` environment variable rather than being stored directly in the Compose file.

## Running the Production Deployment

Make sure Docker is running.

From the project directory:

```bash
docker compose -f compose.prod.yaml up -d
```

Check the containers:

```bash
docker compose -f compose.prod.yaml ps
```

The application should be available at:

```text
http://localhost:8082
```

## Verification

### Check HTTP response

```bash
curl -I http://localhost:8082
```

A successful deployment returns:

```text
HTTP/1.1 200 OK
```

### Check Laravel information

```bash
docker exec laravel-php php artisan about
```

This can be used to verify the Laravel environment, PHP version, database, cache, and production configuration.

### Optimize Laravel

```bash
docker exec laravel-php php artisan optimize
```

This caches Laravel configuration, events, routes, and views for the production deployment.

### Check database connection

```bash
docker exec laravel-php php artisan db:show
```

This verifies that Laravel can connect to the MariaDB service.

### Check migrations

```bash
docker exec laravel-php php artisan migrate:status
```

## Docker Build and GHCR Workflow

The project follows this workflow:

```text
Laravel Source Code
        |
        v
Dockerfile
        |
        v
Build Docker Image
        |
        v
Tag Image
        |
        v
Push Image to GHCR
        |
        v
Production Compose
        |
        v
Pull GHCR Images
        |
        v
Run Production Containers
        |
        v
Verify Application
```

## `.dockerignore`

The project includes a `.dockerignore` file to prevent unnecessary or sensitive files from being included in the Docker build context.

Important excluded files/directories include:

```text
.env
.env.*
vendor
node_modules
.git
.github
```

Production dependencies are installed during the Docker image build.

## Project Files

Important Docker-related files:

```text
docker/
├── php/
│   └── Dockerfile
└── nginx/
    ├── Dockerfile
    └── default.conf

compose.prod.yaml
.dockerignore
README.md
```

## Result

The Laravel application was successfully:

1. Containerized with Docker.
2. Packaged into a custom PHP/Laravel Docker image.
3. Packaged into a custom Nginx Docker image.
4. Pushed to GitHub Container Registry.
5. Deployed locally using the production Compose configuration.
6. Verified through HTTP, Laravel, container health, and database checks.
