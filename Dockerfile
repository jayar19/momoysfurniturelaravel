FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip zip git curl libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Install Node + build assets
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install \
    && npm run build

# Expose port
EXPOSE 10000

# Run Laravel
CMD php artisan serve --host=0.0.0.0 --port=10000