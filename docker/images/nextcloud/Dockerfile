ARG NEXTCLOUD_VERSION
FROM fundocker/nextcloud:${NEXTCLOUD_VERSION}

ARG USER_ID=1000

# Install user_saml app
RUN cd /app/apps && \
  curl -Ls https://github.com/nextcloud/user_saml/releases/download/v3.0.1/user_saml-3.0.1.tar.gz | tar xz && \
  chown -R ${USER_ID}:root /app/apps/user_saml

COPY ./docker/images/nextcloud/conf/php/opcache.ini ${PHP_INI_DIR}/conf.d/opcache.ini

RUN touch /app/data/.ocdata
