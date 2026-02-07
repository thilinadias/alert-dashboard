FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    openssl \
    libicu-dev \
    openssl \
    netcat-openbsd \
    net-tools

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]

# Enable PHP-FPM error logging (Run as root)
RUN echo "catch_workers_output = yes" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "php_admin_flag[log_errors] = on" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "php_admin_value[error_log] = /proc/self/fd/2" >> /usr/local/etc/php-fpm.d/www.conf

# Ensure PHP-FPM listens on 9000 (Correct approach: modify www.conf directly)
RUN sed -i 's/listen = 127.0.0.1:9000/listen = 9000/' /usr/local/etc/php-fpm.d/www.conf || true

# Ensure entrypoint is executable (Run as root before user switch)
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh && chmod +x /usr/local/bin/docker-entrypoint.sh

# -----------------------------------------------
# Application Setup
# -----------------------------------------------

# Allow Composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Fix Git "dubious ownership" error for mounted volumes
RUN git config --system --add safe.directory '*'

# Copy existing application directory contents
COPY --chown=www-data:www-data . /var/www
