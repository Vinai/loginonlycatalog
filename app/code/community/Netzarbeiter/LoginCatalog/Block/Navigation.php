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

/**
 * Ajust the cache for the catalog navigation to only be cached
 * depending on the login state of customers
 *
 * @category   Netzarbeiter
 * @package    Netzarbeiter_LoginCatalog
 * @author     Vinai Kopp <vinai@netzarbeiter.om>
 */
class Netzarbeiter_LoginCatalog_Block_Navigation extends Mage_Catalog_Block_Navigation
{
    protected function _construct()
    {
        $this->setData('module_name', 'Mage_Catalog');
        parent::_construct();
    }

    /**
     * Set this so the navigation is cached depending on the login state.
     * Otherwise, the cache navigation could be shown to a logged in customer, or vica versa.
     *
     * Use the customer group instead of the login state so this extension compatible
     * with the Netzarbeiter_GroupsCatalog extension, in case both extensions are
     * installled at the same time.
     */
    public function getCacheKey()
    {
        $session = Mage::getSingleton('customer/session');
        if (!$session->isLoggedIn()) {
            $customerGroupId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
        } else {
            $customerGroupId = $session->getCustomerGroupId();
        }
        return parent::getCacheKey() . $customerGroupId;
    }

    protected function _checkHideNavigation()
    {
        return Mage::helper('logincatalog')->shouldHideCategoryNavigation();
    }

    public function drawItem($category, $level = 0, $last = false)
    {
        if ($this->_checkHideNavigation()) {
            return '';
        }
        return parent::drawItem($category, $level, $last);
    }

    public function drawOpenCategoryItem($category)
    {
        if ($this->_checkHideNavigation()) {
            return '';
        }
        return parent::drawOpenCategoryItem($category);
    }

    public function renderCategoriesMenuHtml($level = 0, $outermostItemClass = '', $childrenWrapClass = '')
    {
        if ($this->_checkHideNavigation()) {
            return '';
        }
        return parent::renderCategoriesMenuHtml($level, $outermostItemClass, $childrenWrapClass);
    }
}
