# Use the official PHP image with Apache
FROM php:8.4-apache-bullseye

# Install required PHP extensions
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy application files to the container
COPY . /var/www/html

# Set the working directory
WORKDIR /var/www/html

# Set permissions for the application
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80