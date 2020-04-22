<?php
require_once(__DIR__ . '/src/Db.php');
require_once(__DIR__ . '/src/XmlParser.php');
require_once(__DIR__ . '/src/SyncCommand.php');

$url = getenv('NEXTCLOUD_IDPS_METADATA_URL');

if (empty($url)) {
  error_log('idps metadata url is missing. Set NEXTCLOUD_IDPS_METADATA_URL environment variable to provide it.');
  exit(1);
}

if (false === $x509cert = file_get_contents(getenv('SAML_SERVICE_PROVIDER_X509_CERT'))) {
  error_log('X509 certificate file can not be opened.');
  exit(1);
}

if (false === $privateKey = file_get_contents(getenv('SAML_SERVICE_PROVIDER_PK'))) {
  error_log('private key file can not be opened.');
  exit(1);
}

// Create database connection
try {
  $db = new Db(
    getenv('POSTGRES_HOST'),
    getenv('POSTGRES_PORT'),
    getenv('POSTGRES_DB') ?: 5432,
    getenv('POSTGRES_USER'),
    getenv('POSTGRES_PASSWORD')
  );
} catch (PDOException $e) {
  error_log(sprintf('Impossible to connect to postgresql: %s', $e->getMessage()));
  exit(1);
}

try {
  $xml = new XmlParser($url);
} catch (Exception $e) {
  error_log(sprintf('Impossible to parse XML with error: %s', $e->getMessage()));
  exit(1);
}


$syncCommand = new SyncCommand(
  $db,
  $xml,
  getenv('SAML_DISPLAYNAME_MAPPING'),
  getenv('SAML_EMAIL_MAPPING'),
  getenv('SAML_GROUP_MAPPING'),
  getenv('SAML_GENERAL_UID_MAPPING'),
  $x509cert,
  $privateKey
);

$syncCommand->execute();
