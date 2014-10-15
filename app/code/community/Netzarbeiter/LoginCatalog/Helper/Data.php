<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension
 * to newer versions in the future.
 *
 * @category   Netzarbeiter
 * @package    Netzarbeiter_LoginCatalog
 * @copyright  Copyright (c) 2014 Vinai Kopp http://netzarbeiter.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netzarbeiter_LoginCatalog_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * If set to false logincatalog is disabled
     *
     * @var bool|null
     */
    protected $_moduleActive = null;

    /**
     * Return the config value for the passed key
     * 
     * @param string $key
     * @param null|Mage_Core_Model_Store|int $store
     * @return mixed
     */
    public function getConfig($key, $store = null)
    {
        $path = 'catalog/logincatalog/' . $key;
        return Mage::getStoreConfig($path, $store);
    }

    /**
     * Check if the extension has been disabled in the system configuration
     * 
     * @return bool
     */
    public function moduleActive()
    {
        // Only set module active if this is a http request
        if ('' == Mage::app()->getRequest()->getRequestUri()) {
            return false;
        }

        if (null !== $this->getModuleActiveFlag()) {
            return $this->getModuleActiveFlag();
        }
        return !(bool)$this->getConfig('disable_ext');
    }

    /**
     * Provide ability to (de)activate the extension on the fly
     *
     * @param bool $state
     * @return Netzarbeiter_LoginCatalog_Helper_Data
     */
    public function setModuleActive($state = true)
    {
        $this->_moduleActive = $state;
        return $this;
    }

    /**
     * Reset the module to use the system configuration activation state
     *
     * @return Netzarbeiter_LoginCatalog_Helper_Data
     */
    public function resetActivationState()
    {
        $this->_moduleActive = null;
        return $this;
    }

    /**
     * Return the value of the _moduleActive flag
     *
     * @return bool
     */
    public function getModuleActiveFlag()
    {
        return $this->_moduleActive;
    }

    /**
     * Check if the category navigation should be hidden
     *
     * @return bool
     */
    public function shouldHideCategoryNavigation()
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()
                && $this->moduleActive()
                && $this->getConfig('hide_categories')
        ) {
            return true;
        } else {
            return false;
        }
    }
}

