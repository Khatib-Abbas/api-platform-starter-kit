#!/bin/sh

# Remove all running containers
docker compose down -v

# Update Docker images
docker compose build --no-cache --pull

# Update deps
docker compose run php /bin/sh -c 'composer update; composer outdated'

# Update Symfony recipes
cd api
composer recipes:update
