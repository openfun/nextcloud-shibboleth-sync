<?php

class XmlParser
{
  private $xml;

  public function __construct($xmlEndpoint)
  {
    $this->xml = new SimpleXMLElement($xmlEndpoint, null, true);
  }

  public function getEntityDescriptors(): SimpleXMLElement
  {
    return $this->xml->children('md', true);
  }
  public function getX509Cert(SimpleXMLElement $entityDescriptor): string
  {
    return trim((string) $entityDescriptor
      ->IDPSSODescriptor
      ->KeyDescriptor
      ->children('ds', true)
        ->KeyInfo
        ->X509Data
        ->X509Certificate);
  }

  public function getSingleSignOnService(SimpleXMLElement $entityDescriptor): string
  {

    $ssos = $entityDescriptor
    ->IDPSSODescriptor
    ->xpath('md:SingleSignOnService');

    foreach ($ssos as $sso) {
      $attributes = $sso->attributes();
      if((string) $attributes->Binding === 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST') {
        return $attributes->Location;
      }
    }
  }

  public function getSingleLogoutService(SimpleXMLElement $entityDescriptor): ?string
  {

    $ssos = $entityDescriptor
    ->IDPSSODescriptor
    ->xpath('md:SingleLogoutService');
    
    foreach ($ssos as $sso) {
      $attributes = $sso->attributes();
      if((string) $attributes->Binding === 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST') {
        return $attributes->Location;
      }
    }

    return null;
  }

  public function getIdpDisplayName(string $entityId, SimpleXMLElement $entityDescriptor): string
  {
    return $entityDescriptor
      ->IDPSSODescriptor
      ->Extensions
      ->children('mdui', true)
      ->xpath('mdui:DisplayName[@xml:lang="fr"]')[0] ?: $entityId;
  }
}
