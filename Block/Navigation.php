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
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Netzarbeiter
 * @package    Netzarbeiter_LoginCatalog
 * @copyright  Copyright (c) 2008 Vinai Kopp http://netzarbeiter.com
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
	/**
	 * Set this so the navigation is cached depending on the login state
	 * otherwise, the cache navigation could be shown to a logged in
	 * customer, or vica versa.
	 */
    public function getCacheKey()
    {
		$key = parent::getCacheKey();
		$key .= intval(Mage::getSingleton('customer/session')->isLoggedIn());
        return $key;
    }

	public function drawItem($category, $level=0, $last=false)
	{
		if (! Mage::getSingleton('customer/session')->isLoggedIn()
			&& Mage::helper('logincatalog')->moduleActive()
			&& Mage::helper('logincatalog')->getConfig('hide_categories')) {
				return '';
		}
		return parent::drawItem($category, $level, $last);
	}

	public function drawOpenCategoryItem($category)
	{
		if (! Mage::getSingleton('customer/session')->isLoggedIn()
			&& Mage::helper('logincatalog')->moduleActive()
			&& Mage::helper('logincatalog')->getConfig('hide_categories')) {
				return '';
		}
		return parent::drawOpenCategoryItem($category);
	}
}
