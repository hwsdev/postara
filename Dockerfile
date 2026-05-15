FROM dunglas/frankenphp:1-php8.3-alpine

LABEL org.opencontainers.image.source="https://github.com/hwsdev/postara"
LABEL org.opencontainers.image.description="Postara — Open source email service platform"
LABEL org.opencontainers.image.licenses="AGPL-3.0"

# Install PHP extensions
RUN install-php-extensions \
    pdo_pgsql \
    pdo_mysql \
    redis \
    intl \
    opcache \
    pcntl \
    zip \
    gd \
    bcmath \
    exif

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Node + pnpm for asset build
RUN apk add --no-cache nodejs npm \
    && npm install -g pnpm@latest

WORKDIR /app

# Install PHP dependencies (cached layer)
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --optimize-autoloader

# Install JS dependencies and build assets (cached layer)
COPY package.json pnpm-lock.yaml* package-lock.json* ./
RUN pnpm install --frozen-lockfile 2>/dev/null || npm ci --ignore-scripts

# Copy application source
COPY . .

# Optimise autoloader
RUN composer dump-autoload --optimize --no-dev

# Build frontend assets
RUN pnpm run build 2>/dev/null || npm run build

# Set permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Entrypoint — clears stale cache and runs migrations on every container start
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Expose port
EXPOSE 8000

# Default: serve via FrankenPHP
ENTRYPOINT ["/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/app/docker/frankenphp/Caddyfile"]
