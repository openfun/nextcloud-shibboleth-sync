# nextcloud-shibboleth-sync

This repository contains an app to synchronize a list of identity providers with the 
[user_saml](https://github.com/nextcloud/user_saml) Nextcloud application.

This app is written in pure PHP with no other dependency.
It supposes that your NextCloud instance is connected to a PostgreSQL database.

## How to build the image

The docker image will copy the `app` directory and the `CMD` instruction directly executes the script.

To build it, run:

```bash
$ docker build -t fundocker/nextcloud-shibboleth-sync:my_tag .
```

## How to use the image

To run the image, several environment variables should be used:

- `NEXTCLOUD_IDPS_METADATA_URL`: the XML url containing the identity providers list
- `SAML_DISPLAYNAME_MAPPING`: Attribute to map the displayname to
- `SAML_EMAIL_MAPPING`: Attribute to map the email address to
- `SAML_GENERAL_UID_MAPPING`: Attribute to map the UID to
- `SAML_GROUP_MAPPING`: Attribute to map the users groups to
- `SAML_SERVICE_PROVIDER_PK`: path to the service provider private key
- `SAML_SERVICE_PROVIDER_X509_CERT`: path to the service provider X509 certificate
- `POSTGRES_DB`: postgresql database name
- `POSTGRES_HOST`: postgresql database host
- `POSTGRES_PASSWORD`: postgresql database password
- `POSTGRES_PORT`: postgresql database port
- `POSTGRES_USER`: postgresql database username

A volume is also used to add the X509 certificate and the private key provided by the identity provider,
this volume must be mounted in the `/private` directory and the absolute path to each file added to the environment 
variables `SAML_SERVICE_PROVIDER_PK` and `SAML_SERVICE_PROVIDER_X509_CERT`.

A complete example is available in the [`docker-compose.yml`](./docker-compose.yml) file.

## Development workflow

To ease the development, a docker-compose configuration is added. To use this docker-compose environment
a `Makefile` is here to execute all needed operations.

First you need to bootstrap the project:

```bash
$ make bootstrap
```

This task will build all needed images and then install Nextcloud.

Once the project has been bootstrapped you can run the `sync` task:

```
$ make sync
```

If you want to run Nextcloud, you have to execute the `run` task and Nextcloud will be available at the following address: http://localhost:9000

```
$ make run
```
