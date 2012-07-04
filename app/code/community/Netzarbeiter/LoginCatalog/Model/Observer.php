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
 * @copyright  Copyright (c) 2012 Vinai Kopp http://netzarbeiter.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netzarbeiter_LoginCatalog_Model_Observer
{
	/**
	 * Sentry flag to avoid redirect loop
	 *
	 * @var bool
	 */
	protected $_redirectSetFlag = false;

	/**
	 * Cache processed configuration string
	 *
	 * @var array
	 */
	protected $_disabledRoutes = null;

	/**
	 * Is fired on catalog_product_load_after event, i.e. when
	 * a customer views a product page.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function catalogProductLoadAfter(Varien_Event_Observer $observer)
	{
		$this->_checkLoginStatus();
	}

	/**
	 * Is fired on catalog_product_collection_load_after event, i.e.
	 * when viewing a catalog page with products, or when viewing
	 * a search page.
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function catalogProductCollectionLoadAfter(Varien_Event_Observer $observer)
	{
		$this->_checkLoginStatus();
	}

	/**
	 * If the customer isn't logged in, redirect to account login page.
	 */
	protected function _checkLoginStatus()
	{
		if (!Mage::helper('logincatalog')->moduleActive()) return;

		if (!Mage::getSingleton('customer/session')->isLoggedIn())
		{
			// Redirect to login page
			$this->_redirectToLoginPage();
		}
	}

	/**
	 * Redirects the customer to the login page
	 */
	protected function _redirectToLoginPage()
	{
		if ($this->_redirectSetFlag ||
			$this->_isLoginPageRequest() ||
			$this->_isApiRequest() ||
			$this->_redirectDisabledForRoute()
		)
		{
			return;
		}

		// Display message if configured
		$message = Mage::helper('logincatalog')->getConfig('message');
		if (mb_strlen($message, 'UTF-8') > 0)
		{
			Mage::getSingleton('core/session')->addNotice($message);
		}

		// Thanks to kimpecov for this line! (http://www.magentocommerce.com/boards/viewthread/16743/)
		// Use after_auth_url here, otherwise there is a problem with deactivated customers and the Mage_Captcha module
		$currentUrl = Mage::helper('core/url')->getCurrentUrl();
		//$currentUrl = Mage::getUrl('*/*/*', array('_current' => true, '_nosid' => true));
		$currentUrl = Mage::getSingleton('core/url')->sessionUrlVar($currentUrl);
		Mage::getSingleton('customer/session')->setAfterAuthUrl($currentUrl);

		$url = Mage::getUrl("customer/account/login", array('_nosid' => true));
		Mage::app()->getResponse()->setRedirect($url);

		$this->_redirectSetFlag = true;
	}

	/**
	 * Return true if the request is made via the api
	 *
	 * @return bool
	 */
	protected function _isApiRequest()
	{
		return $this->_requestedRouteMatches(array('api'));
	}

	/**
	 * Check if redirects for the currently requested route are disabled
	 * in the system configuration.
	 *
	 * @return bool
	 */
	protected function _redirectDisabledForRoute()
	{
		if (!isset($this->_disabledRoutes))
		{
			$routes = Mage::helper('logincatalog')->getConfig('disable_on_routes');
			foreach (explode("\n", $routes) as $route)
			{
				$this->_disabledRoutes[] = explode('/', trim($route));
			}
		}
		foreach ($this->_disabledRoutes as $route)
		{
			if ($this->_requestedRouteMatches($route))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Return true if the current request is to the login page.
	 * Avoid redirect loops in case products are displayed on the login page.
	 *
	 * @return bool
	 */
	protected function _isLoginPageRequest()
	{
		return
			$this->_requestedRouteMatches(array('customer', 'account', 'login')) ||
			$this->_requestedRouteMatches(array('customer', 'account', 'loginPost')) ||
			$this->_requestedRouteMatches(array('customer', 'account', 'create')) ||
			$this->_requestedRouteMatches(array('customer', 'account', 'createPost'));
	}

	/**
	 * Check if the current request matches the specified route.
	 *
	 * @param array $route Format: array('route name', 'controller', 'action')
	 * @return bool
	 */
	protected function _requestedRouteMatches(array $route)
	{
		$req = Mage::app()->getRequest();
		if (isset($route[0]))
		{
			if ($req->getModuleName() === $route[0])
			{

				if (isset($route[1]))
				{
					if ($req->getControllerName() === $route[1])
					{

						if (isset($route[2]))
						{
							if ($req->getActionName() === $route[2])
							{
								return true; // all parts match

							}
							else return false; // all except action match
						}
						else return true; // only module route and controller specified and both match

					}
					else return false; // module route matches but controller doesn't match
				}
				else return true; // only module route specified and matches

			}
			else return false; // module route specified but doesn't match
		}
		else return false; // no module route specified
	}
}

