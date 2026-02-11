FROM php:8.2-apache

# Install dependencies and extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application source code
COPY . /var/www/html

# Expose port 80
EXPOSE 80
