# Minimal image to run the test suite: cli PHP + composer.
FROM php:8.5-cli

# unzip + git let composer extract dist packages.
RUN apt-get update && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer/composer:2-bin /composer /usr/bin/composer

WORKDIR /app
