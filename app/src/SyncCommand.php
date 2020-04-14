<?php

require_once(__DIR__ . '/Db.php');
require_once(__DIR__ . '/XmlParser.php');

class SyncCommand 
{
  private $db;
  private $samlDisplaynameMapping;
  private $samlEmailMapping;
  private $samlGroupMapping;
  private $samlGeneralUidMapping;
  private $spX509Cert;
  private $spPrivateKey;
  private $xml;
  private $providerIds;

  public function __construct(
    Db $db,
    XmlParser $xml,
    string $samlDisplaynameMapping,
    string $samlEmailMapping,
    string $samlGroupMapping,
    string $samlGeneralUidMapping,
    string $spX509Cert,
    string $spPrivateKey
  ){
    $this->db = $db;
    $this->xml = $xml;
    $this->samlDisplaynameMapping = $samlDisplaynameMapping;
    $this->samlEmailMapping = $samlEmailMapping;
    $this->samlGroupMapping = $samlGroupMapping;
    $this->samlGeneralUidMapping = $samlGeneralUidMapping;
    $this->spX509Cert = $spX509Cert;
    $this->spPrivateKey = $spPrivateKey;
  }

  public function execute()
  {
    echo "Start executing syncro script".PHP_EOL;
    // First fetch the current propertyIds parameter and save it in class property
    $this->providerIds = $this->db->getProviderIds();
    // Prepared statement checking if a given entity provider already exists
    $identityProviderExistsStatement = $this->db->getConnection()->prepare(<<<SQL
SELECT configkey
FROM appconfig
WHERE appid='user_saml'
AND configkey ~ '^[0-9]*-?idp-entityId$'
AND configvalue=:entityId
SQL
);

    // Iterate other all providers present in the XML file
    foreach ($this->xml->getEntityDescriptors() as $entityDescriptor) {
      $entityId = $entityDescriptor->attributes()->entityID;
      // Execute the prepared statement with the current entity provider
      $identityProviderExistsStatement->execute(['entityId' => $entityId]);

      // If the entity provider does not exist, create it.
      if (false === $idpEntityIdConfigKey = $identityProviderExistsStatement->fetchColumn()) {
        $this->insertIdentityProvider($entityId, $entityDescriptor);
      } else {
        // If the entity provider exists, update it.
        $this->updateIdentityProvider($idpEntityIdConfigKey, $entityId, $entityDescriptor);
      }
    }
  }

  private function updateIdentityProvider(string $idpEntityIdConfigKey, string $entityId, SimpleXMLElement $entityDescriptor) 
  {
    echo sprintf('Updating identity provider: %s', $entityId) . PHP_EOL;
    // Retrieve provider id from $idpEntityIdConfigKey
    preg_match('/^([0-9]*)-?idp-entityId$/', $idpEntityIdConfigKey, $matches);
    $id = $matches[1] ?: 1;
    $prefix = $id === '1' ? '' : $id . '-';
    
    $this->processIdentityProvider($prefix, $entityId, $entityDescriptor);
  }

  private function insertIdentityProvider(string $entityId, SimpleXMLElement $entityDescriptor)
  {
    echo sprintf('Adding new identity provider: %s', $entityId) . PHP_EOL;
    $id = end($this->providerIds)?: 0;
    array_push($this->providerIds, ++$id);
    $prefix = $id === 1 ? '' : $id . '-';
    
    // idp-entityId
    $this->db->insertOrUpdateAppConfigRecord(
      $prefix . 'idp-entityId',
      $entityId
    );

    $this->processIdentityProvider($prefix, $entityId, $entityDescriptor);

    $this->db->updateProviderIds($this->providerIds);
  }

  private function processIdentityProvider(string $prefix, string $entityId, SimpleXMLElement $entityDescriptor)
  {
    // idp-x509cert
    $this->db->insertOrUpdateAppConfigRecord(
      $prefix . 'idp-x509cert',
      $this->xml->getX509Cert($entityDescriptor)
    );

    // idp-singleSignOnService.url
    $this->db->insertOrUpdateAppConfigRecord(
      $prefix . 'idp-singleSignOnService.url',
      $this->xml->getSingleSignOnService($entityDescriptor)
    );
    // idp-singleLogoutService.url
    if (null !== $logoutUrl = $this->xml->getSingleLogoutService($entityDescriptor)) {
      $this->db->insertOrUpdateAppConfigRecord(
        $prefix . 'idp-singleLogoutService.url',
        $logoutUrl
      );
    }
    // general-idp0_display_name
    $this->db->insertOrUpdateAppConfigRecord(
      $prefix . 'general-idp0_display_name',
      $this->xml->getIdpDisplayName($entityId, $entityDescriptor)
    );
    // saml-attribute-mapping-displayName_mapping
    $this->db->insertOrUpdateAppConfigRecord(
      $prefix . 'saml-attribute-mapping-displayName_mapping',
      $this->samlDisplaynameMapping
    );
    // saml-attribute-mapping-email_mapping
    $this->db->insertOrUpdateAppConfigRecord(
      $prefix . 'saml-attribute-mapping-email_mapping',
      $this->samlEmailMapping
    );
    // saml-attribute-mapping-group_mapping
    $this->db->insertOrUpdateAppConfigRecord(
      $prefix . 'saml-attribute-mapping-group_mapping',
      $this->samlGroupMapping
    );
    // general-uid_mapping
    $this->db->insertOrUpdateAppConfigRecord(
      $prefix . 'general-uid_mapping',
      $this->samlGeneralUidMapping
    );
    // sp-x509cert
    $this->db->insertOrUpdateAppConfigRecord(
      $prefix . 'sp-x509cert',
      $this->spX509Cert
    );
    // sp-privateKey
    $this->db->insertOrUpdateAppConfigRecord(
      $prefix . 'sp-privateKey',
      $this->spPrivateKey
    );
  }
}
