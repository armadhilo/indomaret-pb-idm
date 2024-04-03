FROM php:8.1-fpm-alpine

USER root

# Install dependencies
# RUN apk --no-cache add wget unzip libaio libnsl curl

# Install additional dependencies
RUN apk add --no-cache \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    curl-dev \
    oniguruma-dev \
    postgresql-dev \
    autoconf \
    g++ \
    make \
    linux-headers 


# Install Oracle Instant Client
ENV LD_LIBRARY_PATH /usr/local/instantclient
RUN apk add --no-cache libaio && \
    curl -o /tmp/instantclient-basic.zip https://download.oracle.com/otn_software/linux/instantclient/211000/instantclient-basic-linux-arm64v8-21.1.0.0.0.zip && \
    curl -o /tmp/instantclient-sdk.zip https://download.oracle.com/otn_software/linux/instantclient/211000/instantclient-sdk-linux-arm64v8-21.1.0.0.0.zip && \
    mkdir -p /usr/local/instantclient && \
    unzip /tmp/instantclient-basic.zip -d /usr/local/instantclient && \
    unzip /tmp/instantclient-sdk.zip -d /usr/local/instantclient && \
    ln -s /usr/local/instantclient/sqlplus /usr/bin/sqlplus && \
    ln -s /usr/local/instantclient/sqlplus /usr/bin/sqlplus64 && \
    rm -rf /tmp/instantclient-*.zip

# Install OCI8 extension
RUN docker-php-ext-configure oci8 --with-oci8=instantclient,/usr/local/instantclient \
    && docker-php-ext-install oci8


# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        zip \
        pdo \
        pdo_mysql \
        mysqli \
        mbstring \
        xml \
        curl \
        intl \
        opcache \
        pdo_pgsql

# Install Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Clean up
RUN apk del autoconf g++ make \
    && rm -rf /tmp/* /var/cache/apk/*



# Download and install Oracle Instant Client and SDK
# RUN mkdir /opt/oracle \
#     && wget https://download.oracle.com/otn_software/linux/instantclient/213000/instantclient-basiclite-linux.x64-21.3.0.0.0.zip -O /opt/oracle/instantclient.zip \
#     && wget https://download.oracle.com/otn_software/linux/instantclient/213000/instantclient-sdk-linux.x64-21.3.0.0.0.zip -O /opt/oracle/instantclient-sdk.zip \
#     && unzip /opt/oracle/instantclient.zip -d /opt/oracle \
#     && unzip /opt/oracle/instantclient-sdk.zip -d /opt/oracle \
#     && rm /opt/oracle/instantclient.zip /opt/oracle/instantclient-sdk.zip \
#     && ln -s /opt/oracle/instantclient_21_3 /opt/oracle/instantclient \
#     && ln -s /opt/oracle/instantclient/sdk/include/*.h /usr/include

# Add Oracle Instant Client path to environment
# ENV LD_LIBRARY_PATH /opt/oracle/instantclient

# Install the Oracle extension
# RUN docker-php-ext-configure oci8 --with-oci8=instantclient,/opt/oracle/instantclient \
#     && docker-php-ext-install oci8


# RUN apk add --no-cache postgresql-dev \
#     && docker-php-ext-install pdo pdo_pgsql
    
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Clean up
# RUN apk del wget unzip

# Your additional configurations and application setup go here...

# Expose port 9000 and start PHP-FPM server
EXPOSE 9000
CMD ["php-fpm"]

