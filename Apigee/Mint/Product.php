<?php

namespace Apigee\Mint;

use Apigee\Exceptions\ParameterException;

class Product extends Base\BaseObject {

  /**
   * Product id
   * @var string
   */
  private $id;

  /**
   * Product Name
   * @var string
   */
  private $name;

  /**
   * Display Name
   * @var string
   */
  private $displayName;

  /**
   * Description
   * @var string
   */
  private $description;

  /**
   * Environment
   * @var string
   */
  private $environment;

  /**
   * Supports Refund
   * @var boolean
   */
  private $supportsRefund;

  /**
   * Credit terms - in how many days payment is due (for postpaid)
   * @var int
   */
  private $paymentDueDays;

  /**
   * Status
   * @var string
   */
  private $status;

  /**
   * Tax Category
   * @var string
   */
  private $taxCategory;

  /**
   * Custom Payment Term
   * @var boolean
   */
  private $customPaymentTerm;

  /**
   * Product price points
   * @var array Array of instances of \Apigee\Mint\PricePoint
   */
  private $pricePoints = array();

  /**
   * The following directive break the apix create application.. commenting out
   * @var array Array of instances of \Apigee\Mint\DataStructures\SuborgProduct
   */
  private $suborgProducts = array();

  /**
   * Organization
   * @var \Apigee\Mint\Organization
   */
  private $organization;

  /**
   * Transaction Success Criteria
   * @var string
   */
  private $transactionSuccessCriteria;

  /**
   * Refound Success Criteria
   * @var string
   */
  private $refundSuccessCriteria;

  /**
   * Transaction TTL
   * @var int
   */
  private $transactionTtl;

  /**
   * Custom Attribute 1 Name
   * @var string
   */
  private $customAtt1Name;

  /**
   * Custom Attribute 2 Name
   * @var string
   */
  private $customAtt2Name;

  /**
   * Custom Attribute 3 Name
   * @var string
   */
  private $customAtt3Name;

  /**
   * Custom Attribute 4 Name
   * @var string
   */
  private $customAtt4Name;

  /**
   * Custom Attribute 5 Name
   * @var string
   */
  private $customAtt5Name;

  /**
   * Developer categories
   * @var array Array of \Apigee\Mint\DeveloperCategory
   */
  private $developerCategories = array();

  /**
   * Developers defined as Brokers
   * @var array Array of \Apigee\Mint\Developer
   */
  private $brokers = array();

  public function __construct(\Apigee\Util\OrgConfig $config) {

    $base_url = '/mint/organizations/' . rawurlencode($config->orgName) . '/products';
    $this->init($config, $base_url);

    $this->wrapperTag = 'product';
    $this->idField = 'id';
    $this->idIsAutogenerated = FALSE;

    $this->initValues();
  }

  public function instantiateNew() {
    return new Product($this->config);
  }

  public function loadFromRawData($data, $reset = FALSE) {
    if ($reset) {
      $this->initValues();
    }
    $excluded_properties = array('pricePoints', 'suborgProducts', 'organization', 'developerCategory', 'broker');
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

    if (isset($data['pricePoints']) && is_array($data['pricePoints']) && count($data['pricePoints']) > 0) {
      foreach ($data['pricePoints'] as $price_point_item) {
        $price_point = new PricePoint($this->id, $this->config);
        $price_point->loadFromRawData($price_point_item);
        $this->pricePoints[] = $price_point;
      }
    }

    if (isset($data['suborgProducts']) && is_array($data['suborgProducts']) && count($data['suborgProducts']) > 0) {
      foreach ($data['suborgProducts'] as $suborg_product_item) {
        $suborg_product = new SuborgProduct($this->id, $this->config);
        $suborg_product->loadFromRawData($suborg_product_item);
        $this->suborgProducts[] = $suborg_product;
      }
    }

    if (isset($data['organization'])) {
      $organization = new Organization($this->config);
      $organization->loadFromRawData($data['organization']);
      $this->organization = $organization;
    }

    if (isset($data['developerCategory']) && is_array($data['developerCategory']) && count($data['developerCategory']) > 0) {
      foreach ($data['developerCategory'] as $cat_item) {
        $category = new DeveloperCategory($this->config);
        $category->loadFromRawData($cat_item);
        $this->developerCategories[] = $category;
      }
    }

    // TODO verify that brokers are Developers
    if (isset($data['broker']) && is_array($data['broker']) && count($data['broker']) > 0) {
      foreach ($data['broker'] as $broker_item) {
        $broker = new Developer($this->config);
        $broker->loadFromRawData($broker_item);
        $this->brokers[] = $broker;
      }
    }
  }

  protected function initValues() {
    $this->name = '';
    $this->display_name = '';
    $this->description = '';
    $this->environment = '';
    $this->supportsRefund = FALSE;
    $this->paymentDueDays = 0;
    $this->status = 'ACTIVE';
    $this->taxCategory = NULL;
    $this->customPaymentTerm = FALSE;
    $this->pricePoints = array();
    $this->suborgProducts = array();
    $this->organization = NULL;
    $this->transactionSuccessCriteria = '';
    $this->refundSuccessCriteria = '';
    $this->transactionTtl = 0;
    $this->customAtt1Name = '';
    $this->customAtt2Name = '';
    $this->customAtt3Name = '';
    $this->customAtt4Name = '';
    $this->customAtt5Name = '';
    $this->developerCategories = array();
    $this->brokers = array();

  }

  public function __toString() {
    // @TODO Verify
    $obj = array();
    $properties = array_keys(get_object_vars($this));
    $excluded_properties = array('org', 'developerCategory', 'broker');
    $excluded_properties = array_merge(array_keys(get_class_vars(get_parent_class($this))), $excluded_properties);
    foreach ($properties as $property) {
      if (in_array($property, $excluded_properties)) {
        continue;
      }
      if (isset($this->$property)) {
        if (is_object($this->$property)) {
          $obj[$property] = json_decode((string) $this->$property, TRUE);
        }
        else {
          $obj[$property] = $this->$property;
        }
      }
    }
    return json_encode($obj);
  }

  // getters/setters

  /**
   * Get Product id
   * @return string
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Get Product Name
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Get Display Name
   * @return string
   */
  public function getDisplayName() {
    return $this->displayName;
  }

  /**
   * Get Description
   * @return string
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * Get Environment
   * @return string
   */
  public function getEnvironment() {
    return $this->environment;
  }

  /**
   * Get Supports Refund
   * @return boolean
   */
  public function getSupportsRefund() {
    return $this->supportsRefund;
  }

  /**
   * Get Credit terms - in how many days payment is due (for postpaid)
   * @return int
   */
  public function getPaymentDueDays() {
    return $this->paymentDueDays;
  }

  /**
   * Get Status
   * @return string
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * Get Tax Category
   * @return string
   */
  public function getTaxCategory() {
    return $this->taxCategory;
  }

  /**
   * Get Custom Payment Term
   * @return boolean
   */
  public function getCustomPaymentTerm() {
    return $this->customPaymentTerm;
  }

  /**
   * Get Product Price Points
   * @return array Array of instances of \Apigee\Mint\PricePoint
   */
  public function getPricePoints($product_id = NULL, $refresh = FALSE) {
    if (empty($this->pricePoints) || $refresh) {
      if ($product_id == NULL) {
        $product_id = $this->id;
      }
      else {
        throw new ParameterException('Product Id not specified');
      }
      $pricePoints = new PricePoint($product_id, $this->config);
      $this->pricePoints = $pricePoints->getList();
    }
    return $this->pricePoints;
  }

  /**
   *
   * @return array Array of instances of \Apigee\Mint\SuborgProduct
   */
  public function getSuborgProducts() {
    return $this->suborgProducts;
  }

  /**
   * Get Organization
   * @return \Apigee\Mint\Organization
   */
  public function getOrganization() {
    return $this->organization;
  }

  /**
   * Get Transaction Success Criteria
   * @return string
   */
  public function getTransactionSuccessCriteria() {
    return $this->transactionSuccessCriteria;
  }

  /**
   * Get Refound Success Criteria
   * @return string
   */
  public function getRefundSuccessCriteria() {
    return $this->refundSuccessCriteria;
  }

  /**
   * Get Transaction TTL
   * @return int
   */
  public function getTransactionTTL() {
    return $this->transactionTtl;
  }

  /**
   * Get Custom Attribute 1 Name
   * @return string
   */
  public function getCustomAtt1Name() {
    return $this->customAtt1Name;
  }

  /**
   * Get Custom Attribute 2 Name
   * @return string
   */
  public function getCustomAtt2Name() {
    return $this->customAtt2Name;
  }

  /**
   * Get Custom Attribute 3 Name
   * @return string
   */
  public function getCustomAtt3Name() {
    return $this->customAtt3Name;
  }

  /**
   * Get Custom Attribute 4 Name
   * @return string
   */
  public function getCustomAtt4Name() {
    return $this->customAtt4Name;
  }

  /**
   * Get Custom Attribute 5 Name
   * @return string
   */
  public function getCustomAtt5Name() {
    return $this->customAtt5Name;
  }

  /**
   * Get Developer categories
   * @return array Array of \Apigee\Mint\DeveloperCategory
   */
  public function getDeveloperCategories() {
    return $this->developerCategories;
  }

  /**
   * Get Developers defined as Brokers
   * @return array Array of \Apigee\Mint\Developer
   */
  public function getBrokers() {
    return $this->brokers;
  }

  /**
   * Set Product id
   * @param string $id
   * @return void
   */
  public function setId($id) {
    $this->id = $id;
  }

  /**
   * Set Product Name
   * @param string $name
   * @return void
   */
  public function setName($name) {
    $this->name = $name;
  }

  /**
   * Set Display Name
   * @param string $display_name
   */
  public function setDisplayName($display_name) {
    $this->displayName = $display_name;
  }

  /**
   * Set Description
   * @param string $description
   * @return void
   */
  public function setDescription($description) {
    $this->description = $description;
  }

  /**
   * Set Environment
   * @param string $environment
   * @return void
   */
  public function setEnvironment($environment) {
    $this->environment = $environment;
  }

  /**
   * Set Supports Refund
   * @param boolean $supports_refund
   * @return void
   */
  public function setSupportsRefund($supports_refund) {
    $this->supportsRefund = $supports_refund;
  }

  /**
   * Set Credit terms - in how many days payment is due (for postpaid)
   * @param int $payment_due_days
   * @return void
   */
  public function setPaymentDueDays($payment_due_days) {
    $this->paymentDueDays = $payment_due_days;
  }

  /**
   * Set Status
   * @param string $status Possible values CREATED|INACTIVE|ACTIVE
   * @return void
   * @throws \Apigee\Exceptions\ParameterException
   */
  public function setStatus($status) {
    $status = strtoupper($status);
    if (!in_array($status, array('CREATED', 'INACTIVE', 'ACTIVE'))) {
      throw new ParameterException('Invalid product status value: ' . $status);
    }
    $this->status = $status;
  }

  /**
   * Set Tax Category
   * @param string $tax_category Possible values INFORMATION_SERVICES|ECOMMERCE
   * @return void
   * @throws \Apigee\Exceptions\ParameterException
   */
  public function setTaxCategory($tax_category) {
    $tax_category = strtoupper($tax_category);
    if (!in_array($tax_category, array('INFORMATION_SERVICES', 'ECOMMERCE'))) {
      throw new ParameterException('Invalid product tax category value: ' . $tax_category);
    }
    $this->taxCategory = $tax_category;
  }

  /**
   * Set Custom Payment Term
   * @param boolean $custom_payment_term
   * @return void
   */
  public function setCustomPaymentTerm($custom_payment_term) {
    $this->customPaymentTerm = $custom_payment_term;
  }

  /**
   * Add Product Price Point
   * @param \Apigee\Mint\PricePoint $price_point
   * @return void
   */
  public function addPricePoints(PricePoint $price_point) {
    $this->pricePoints[] = $price_point;
  }

  /**
   * Remove all PricePoints from this Product
   * @return void
   */
  public function clearPricePoints() {
    $this->pricePoints = array();
  }

  /**
   * Add SuborgProduct
   * @param \Apigee\Mint\SuborgProduct $suborg_product
   * @return void
   */
  public function addSuborgProduct(SuborgProduct $suborg_product) {
    $this->suborgProducts[] = $suborg_product;
  }

  /**
   * Remove all SuborgProducts from this product
   * @return void
   */
  public function clearSuborgProducts() {
    $this->suborgProducts = array();
  }

  /**
   * Set Organization
   * @param \Apigee\Mint\Organization $organization
   * @return void
   */
  public function setOrganization(Organization $organization) {
    $this->organization = $organization;
  }

  /**
   * Set Transaction Success Criteria
   * @param string $transaction_success_criteria
   * @return void
   */
  public function setTransactionSuccessCriteria($transaction_success_criteria) {
    $this->transactionSuccessCriteria = $transaction_success_criteria;
  }

  /**
   * Set Refound Success Criteria
   * @param string $refund_success_criteria
   * @return void
   */
  public function setRefundSuccessCriteria($refund_success_criteria) {
    $this->refundSuccessCriteria = $refund_success_criteria;
  }

  /**
   * Set Transaction TTL
   * @param int $transaction_ttl
   * @return void
   */
  public function setTransactionTTL($transaction_ttl) {
    $this->transactionTtl = $transaction_ttl;
  }

  /**
   * Set Custom Attribute 1 Name
   * @param string $custom_att1_name
   * @return void
   */
  public function setCustomAtt1Name($custom_att1_name) {
    $this->customAtt1Name = $custom_att1_name;
  }

  /**
   * Set Custom Attribute 2 Name
   * @param string $custom_att2_name
   * @return void
   */
  public function setCustomAtt2Name($custom_att2_name) {
    $this->customAtt2Name = $custom_att2_name;
  }

  /**
   * Set Custom Attribute 3 Name
   * @param string $custom_att3_name
   * @return void
   */
  public function setCustomAtt3Name($custom_att3_name) {
    $this->customAtt3Name = $custom_att3_name;
  }

  /**
   * Set Custom Attribute 4 Name
   * @param string $custom_att4_name
   * @return void
   */
  public function setCustomAtt4Name($custom_att4_name) {
    $this->customAtt4Name = $custom_att4_name;
  }

  /**
   * Set Custom Attribute 5 Name
   * @param string $custom_att5_name
   * @return void
   */
  public function setCustomAtt5Name($custom_att5_name) {
    $this->customAtt5Name = $custom_att5_name;
  }

  /**
   * Add DeveloperCategory
   * @param \Apigee\Mint\DeveloperCategory
   * @return void
   */
  public function addDeveloperCategory($developer_category) {
    $this->developerCategories[] = $developer_category;
  }

  /**
   * Remove all DeveloperCategory objects from this product
   * @return void
   */
  public function clearDeveloperCategories() {
    $this->developerCategories = array();
  }

  /**
   * Add Developers defined as Brokers
   * @param \Apigee\Mint\Developer $broker
   * @return void
   */
  public function addBroker($broker) {
    $this->brokers[] = $broker;
  }

  /**
   * Remove all Brokers from this product
   * @return void
   */
  public function clearBrokers() {
    $this->brokers = array();
  }
}