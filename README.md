## Setup con Docker

Requisitos:
- Docker
- Docker Compose

### Levantar proyecto
docker compose up -d --build

### Instalar dependencias
docker compose exec app composer install
docker compose exec app npm install

### Migraciones
docker compose exec app php artisan migrate
