<?php

namespace Apigee\Mint;

use Apigee\Mint\Types\ApplicationStatusType as ApplicationStatusType;
use Apigee\Mint\Types\StatusType as StatusType;
use Apigee\Exceptions\ParameterException as ParameterException;

class Application extends Base\BaseObject {

  /**
   * @var string
   */
  private $description;

  /**
   * @var string
   */
  private $id;

  /**
   * @var string
   */
  private $key;

  /**
   * @var string
   */
  private $name;

  /**
   * @var array
   */
  private $products;

  /**
   * @var string
   */
  private $redirectUrl;

  /**
   * @var string
   */
  private $secret;

  /**
   * @var string
   */
  private $status;

  /**
   * @var \Apigee\Mint\ApplicationCategory
   */
  private $applicationCategory;

  /**
   * @var \Apigee\Mint\Organization
   */
  private $organization;

  /**
   * @var string
   * read-only
   */
  private $developerEmail;

  public function __construct($developer_email, \Apigee\Util\OrgConfig $config) {
    $baseUrl = '/mint/organizations/' . rawurlencode($config->orgName) . '/developers/' . rawurlencode($developer_email) . '/applications';
    $this->init($config, $baseUrl);
    $this->developerEmail = $developer_email;
    $this->wrapperTag = 'application';
    $this->idField = 'id';
    $this->idIsAutogenerated = FALSE;

    $this->initValues();
  }

  /**
   * Implements Base\BaseObject::init_values().
   *
   * @return void
   */
  public function initValues() {
    $this->description = NULL;
    $this->id = NULL;
    $this->key = NULL;
    $this->name = NULL;
    $this->products = array();
    $this->redirectUrl = NULL;
    $this->secret = NULL;
    $this->applicationCategory = NULL;
    $this->organization = NULL;
    $this->status = 'ACTIVE';
  }

  /**
   * Implements Base\BaseObject::instantiate_new().
   *
   * @return Application|Base\BaseObject
   */
  public function instantiateNew() {
    return new Application($this->developerEmail, $this->config);
  }

  /**
   * Implements Base\BaseObject::load_from_raw_data().
   *
   * @param array $data
   * @param bool $reset
   */
  public function loadFromRawData($data, $reset = FALSE) {
    if ($reset) {
      $this->initValues();
    }
    $excluded_properties = array('organization', 'product', 'applicationCategory');
    foreach (array_keys($data) as $property) {
      if (in_array($property, $excluded_properties)) {
        continue;
      }

      // form the setter method name to invoke setXxxx
      $setter_method = 'set' . ucfirst($property);

      if (method_exists($this, $setter_method)) {
        $this->$setter_method($data[$property]);
      }
      else {
        self::$logger->notice('No setter method was found for property "' . $property . '"');
      }
    }
    if (isset($data['product']) && is_array($data['product']) && count($data['product']) > 0) {
      foreach ($data['product'] as $product_item) {
        $product = new Product($this->config);
        $product->loadFromRawData($product_item);
        $this->products[] = $product;
      }
    }

    if (isset($data['organization'])) {
      $organization = new Organization($this->config);
      $organization->loadFromRawData($data['organization']);
      $this->organization = $organization;
    }

    if (isset($data['applicationCategory'])) {
      $appCat = new ApplicationCategory($this->config);
      $appCat->loadFromRawData($data['applicationCategory']);
      $this->applicationCategory = $appCat;
    }
  }

  /**
   * Implements Base\BaseObject::__toString().
   *
   * @return string
   */
  public function __toString() {
    $obj = array();
    $obj['organization'] = array('id' => $this->organization->getId());
    $obj['applicationCategory'] = array('id' => $this->applicationCategory->getId());
    $obj['product'] = array();
    foreach ($this->products as $product) {
      $obj['product'][] = array('id' => $product->getId());
    }
    $properties = array_keys(get_object_vars($this));
    $excluded_properties = array_keys(get_class_vars(get_parent_class($this)));
    foreach ($properties as $property) {
      if ($property == 'product' || $property == 'organization' || in_array($property, $excluded_properties)) {
        continue;
      }
      if (isset($this->$property)) {
        $obj[$property] = $this->$property;
      }
    }
    return json_encode($obj);
  }

  /*
   * accessors (getters/setters)
   */

  // TODO: validate many of the below properties

  public function getDescription() {
    return $this->description;
  }
  public function setDescription($desc) {
    $this->description = (string)$desc;
  }
  public function getId() {
    return $this->id;
  }
  public function setId($id) {
    $this->id = (string)$id;
  }
  public function getKey() {
    return $this->key;
  }
  public function setKey($key) {
    $this->key = (string)$key;
  }
  public function getName() {
    return $this->name;
  }
  public function setName($name) {
    $this->name = $name;
  }
  public function getProducts() {
    return $this->products;
  }
  public function addProduct($product) {
    if (!is_object($product) || !($product instanceof Product)) {
      if (!is_int($product) && !is_string($product)) {
        throw new ParameterException('Invalid product.');
      }
      $product_id = (string)$product;
      $product = new Product($this->config);
      $product->load($product_id);
    }
    $this->products[] = $product;
  }
  public function clearProducts() {
    $this->products = array();
  }
  public function getRedirectUrl() {
    return $this->redirectUrl;
  }
  public function setRedirectUrl($url) {
    if (!$this->validateUri($url)) {
      throw new ParameterException("$url is not a valid Redirect URL.");
    }
    $this->redirectUrl = $url;
  }
  public function getSecret() {
    return $this->secret;
  }
  public function setSecret($secret) {
    $this->secret = $secret;
  }
  public function getStatus() {
    return $this->status;
  }
  public function setStatus($status) {
    $this->status = ApplicationStatusType::get($status);
  }
  public function getOrganization() {
    return $this->organization;
  }
  public function setOrganization($organization) {
    $this->organization = $organization;
  }
  public function getApplicationCategory() {
    return $this->applicationCategory;
  }
  public function setApplicationCategory($application_category) {
    $this->applicationCategory = $application_category;
  }
}