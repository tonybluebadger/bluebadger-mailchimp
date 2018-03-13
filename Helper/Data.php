<?php

namespace Bluebadger\MailChimp\Helper;

/**
 * @inheritdoc
 */
class Data extends \Ebizmarts\MailChimp\Helper\Data
{
    const MERGE_VARS = [
        0 => ['magento' => 'fname', 'mailchimp' => 'FNAME'],
        1 => ['magento' => 'lname', 'mailchimp' => 'LNAME'],
        2 => ['magento' => 'gender', 'mailchimp' => 'GENDER'],
        3 => ['magento' => 'dob', 'mailchimp' => 'DOB'],
        4 => ['magento' => 'billing_address', 'mailchimp' => 'BILLING'],
        5 => ['magento' => 'shipping_address', 'mailchimp' => 'SHIPPING'],
        6 => ['magento' => 'billing_telephone', 'mailchimp' => 'BTELEPHONE'],
        7 => ['magento' => 'shipping_telephone', 'mailchimp' => 'STELEPHONE'],
        8 => ['magento' => 'billing_company', 'mailchimp' => 'BCOMPANY'],
        9 => ['magento' => 'shipping_company', 'mailchimp' => 'SCOMPANY'],
        10 => ['magento' => 'group_id', 'mailchimp' => 'CGROUP'],
        11 => ['magento' => 'store_id', 'mailchimp' => 'STOREID'],
        12 => ['magento' => 'region', 'mailchimp' => 'REGION'],
    ];

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $_addressRepositoryInterface;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Ebizmarts\MailChimp\Model\Logger\Logger $logger
     * @param \Magento\Customer\Model\GroupRegistry $groupRegistry
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Module\ModuleList\Loader $loader
     * @param \Magento\Config\Model\ResourceModel\Config $config
     * @param \Mailchimp $api
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customer
     * @param \Ebizmarts\MailChimp\Model\MailChimpErrors $mailChimpErrors
     * @param \Ebizmarts\MailChimp\Model\MailChimpSyncEcommerceFactory $mailChimpSyncEcommerce
     * @param \Ebizmarts\MailChimp\Model\MailChimpSyncEcommerce $mailChimpSyncE
     * @param \Ebizmarts\MailChimp\Model\MailChimpSyncBatches $syncBatches
     * @param \Ebizmarts\MailChimp\Model\MailChimpStoresFactory $mailChimpStoresFactory
     * @param \Ebizmarts\MailChimp\Model\MailChimpStores $mailChimpStores
     * @param \Magento\Framework\Encryption\Encryptor $encryptor
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollection
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollection
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ebizmarts\MailChimp\Model\Logger\Logger $logger,
        \Magento\Customer\Model\GroupRegistry $groupRegistry,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Module\ModuleList\Loader $loader,
        \Magento\Config\Model\ResourceModel\Config $config,
        \Mailchimp $api,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customer,
        \Ebizmarts\MailChimp\Model\MailChimpErrors $mailChimpErrors,
        \Ebizmarts\MailChimp\Model\MailChimpSyncEcommerceFactory $mailChimpSyncEcommerce,
        \Ebizmarts\MailChimp\Model\MailChimpSyncEcommerce $mailChimpSyncE,
        \Ebizmarts\MailChimp\Model\MailChimpSyncBatches $syncBatches,
        \Ebizmarts\MailChimp\Model\MailChimpStoresFactory $mailChimpStoresFactory,
        \Ebizmarts\MailChimp\Model\MailChimpStores $mailChimpStores,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollection,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollection,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface,
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        $this->_addressRepositoryInterface = $addressRepositoryInterface;
        parent::__construct($context, $storeManager, $logger, $groupRegistry, $state, $loader, $config, $api, $cacheTypeList, $customer, $mailChimpErrors, $mailChimpSyncEcommerce, $mailChimpSyncE, $syncBatches, $mailChimpStoresFactory, $mailChimpStores, $encryptor, $subscriberCollection, $customerCollection, $addressRepositoryInterface, $resource);
    }

    /**
     * @param $map
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param $merge_vars
     * @return array
     */
    protected function _getCustomerMergeVarsValues($map, $customer, $merge_vars)
    {
        $customAtt = $map['magento'];
        $chimpTag = $map['mailchimp'];
        if ($chimpTag && $customAtt) {
            $key = strtoupper($chimpTag);
            switch ($customAtt) {
                case 'fname':
                    $val = $customer->getFirstname();
                    $merge_vars[$key] = $val;
                    break;
                case 'lname':
                    $val = $customer->getLastname();
                    $merge_vars[$key] = $val;
                    break;
                case 'gender':
                    $val = (int)$customer->getGender();
                    if ($val == 1) {
                        $merge_vars[$key] = 'Male';
                    } elseif ($val == 2) {
                        $merge_vars[$key] = 'Female';
                    }
                    break;
                case 'dob':
                    $dob = $customer->getDob();
                    if ($dob) {
                        $merge_vars[$key] = (substr($dob, 5, 2) . '/' . substr($dob, 8, 2));
                    }
                    break;
                case 'billing_address':
                case 'shipping_address':
                    $addr = explode('_', $customAtt);
                    $merge_vars = array_merge($merge_vars, $this->_updateMergeVars($key, ucfirst($addr[0]), $customer));
                    break;
                case 'billing_telephone':
                    try {
                        $address = $this->_addressRepositoryInterface->getById($customer->getDefaultBilling());
                        if ($address) {
                            $telephone = $address->getTelephone();
                            if ($telephone) {
                                $merge_vars[$key] = $telephone;
                            }
                        }
                    } catch (\Exception $e) {
                        $this->log($e->getMessage());
                    }
                    break;
                case 'billing_company':
                    try {
                        $address = $this->_addressRepositoryInterface->getById($customer->getDefaultBilling());
                        if ($address) {
                            $company = $address->getCompany();
                            if ($company) {
                                $merge_vars[$key] = $company;
                            }
                        }
                    } catch (\Exception $e) {
                        $this->log($e->getMessage());
                    }

                    break;
                case 'shipping_telephone':
                    try {
                        $address = $this->_addressRepositoryInterface->getById($customer->getDefaultShipping());
                        if ($address) {
                            $telephone = $address->getTelephone();
                            if ($telephone) {
                                $merge_vars[$key] = $telephone;
                            }
                        }
                    } catch (\Exception $e) {
                        $this->log($e->getMessage());
                    }
                    break;
                case 'shipping_company':
                    try {
                        $address = $this->_addressRepositoryInterface->getById($customer->getDefaultShipping());
                        if ($address) {
                            $company = $address->getCompany();
                            if ($company) {
                                $merge_vars[$key] = $company;
                            }
                        }
                    } catch (\Exception $e) {
                        $this->log($e->getMessage());
                    }
                    break;
                case 'group_id':
                    $merge_vars = array_merge($merge_vars, $this->_getCustomerGroup($customer, $key, $merge_vars));
                    break;
                case 'store_id':
                    $storeId = $customer->getStoreId();
                    if ($storeId) {
                        $merge_vars[$key] = $storeId;
                    }
                    break;
                case 'region':
                    try {
                        $addressId = ($customer->getDefaultBilling()) ? $customer->getDefaultBilling() : $customer->getDefaultShipping();
                        $address = $this->_addressRepositoryInterface->getById($addressId);
                        if ($address) {
                            $region = $address->getRegion();
                            if ($region) {
                                $merge_vars[$key] = $region->getRegionCode();
                            }
                        }
                    } catch (\Exception $e) {
                        $this->log($e->getMessage());
                    }
                    break;
            }
            return $merge_vars;
        }
    }
}
