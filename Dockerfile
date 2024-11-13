# Dockerfile

# Use an official PHP runtime as a parent image
FROM php:8.2.12-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the application code
COPY . .

# Install application dependencies
RUN composer install --no-scripts --no-autoloader

# Ensure permissions are correct
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/vendor

# Expose port 9000 and start PHP-FPM server
EXPOSE 9000
CMD ["php-fpm"]
