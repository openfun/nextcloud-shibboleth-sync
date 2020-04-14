<?php
$CONFIG = array (
  'apps_paths' => 
  array (
    0 => 
    array (
      'path' => '/app/apps',
      'url' => '/apps',
      'writable' => false,
    ),
  ),
  'appstoreenabled' => false,
  'config_is_read_only' => true,
  'datadirectory' => '/app/data',
  'dbtype' => 'pgsql',
  'dbname' => 'nextcloud',
  'dbhost' => 'db',
  'dbport' => '5432',
  'dbtableprefix' => '',
  'dbuser' => 'nextcloud_user',
  'dbpassword' => 'pass',
  'debug' => true,
  'installed' => true,
  'instanceid' => 'foobarbaz',
  'log_type' => 'file',
  'loglevel' => '0',
  'memcache.local' => '\\OC\\Memcache\\APCu',
  'passwordsalt' => 'salt',
  'secret' => 'secret',
  'trusted_domains' => 
  array (
    0 => 'localhost',
    1 => 'localhost',
    2 => 'localhost',
  ),
  'updatechecker' => false,
  'updater.server.url' => 'https://updates.nextcloud.com/updater_server/',
  'updater.release.channel' => 'stable',
  'upgrade.disable-web' => true,
  'version' => '18.0.3.0',
  'maintenance' => false,
);
