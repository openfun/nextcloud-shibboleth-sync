<?php

class Db {
  private $pdo;

  public function __construct(
    string $dbHost,
    string $dbPort,
    string $dbName,
    string $dbUser,
    string $dbPassword
  ) {
    $dsn = sprintf(
      'pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s',
      $dbHost,
      $dbPort,
      $dbName,
      $dbUser,
      $dbPassword,
    );
    $this->pdo = new PDO($dsn);
  }

  /**
   * Update an existing appconfig record by updating its value based on the
   * given configkey
   */
  public function insertOrUpdateAppConfigRecord(string $configKey, string $configValue)
  {
    $statement = $this->pdo->prepare(<<<SQL
INSERT INTO appconfig(appid, configkey, configvalue)
VALUES ('user_saml', :configkey, :configvalue)
ON CONFLICT (appid, configkey) DO UPDATE SET configvalue=:configvalue WHERE appconfig.appid='user_saml' AND appconfig.configkey=:configkey
SQL
    );
    $statement->execute([
      'configvalue' => $configValue,
      'configkey' => $configKey,
    ]);
  }

  /**
   * Return an array containing all the active provider ids.
   * This array is sorted from the lowest id to the higher id.
   */
  public function getProviderIds(): array
  {
    $providerIdsStatement = $this->pdo->prepare(<<<SQL
SELECT configvalue
FROM appconfig
WHERE appid='user_saml'
AND configkey='providerIds'
SQL
);
    $providerIdsStatement->execute();
    $providerIds = explode(',', $providerIdsStatement->fetchColumn());
    sort($providerIds, SORT_NUMERIC);

    return $providerIds;
  }

  /**
   * Save in database an updated list of providerIds
   */
  public function updateProviderIds(array $providerIds)
  {
    $providerIds = array_filter($providerIds);
    $this->insertOrUpdateAppConfigRecord(
      'providerIds',
      implode(',', $providerIds)
    );
  }

  /**
   * Return the PDO instance used to interact with the database.
   */
  public function getConnection(): \PDO
  {
    return $this->pdo;
  }
}
