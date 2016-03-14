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
class Netzarbeiter_LoginCatalog_Model_Observer
{
    const ROUTE_PART_MODULE = 0;
    const ROUTE_PART_CONTROLLER = 1;
    const ROUTE_PART_ACTION = 2;

    /**
     * Sentry flag to avoid redirect loop
     *
     * @var bool
     */
    private $_redirectSetFlag = false;

    /**
     * Cache processed configuration string
     *
     * @var array
     */
    private $_disabledRoutes = null;

    /**
     * Conditional rewrite to enable feature backward compatibility.
     *
     * Since Magento 1.7 it is no longer required to rewrite the catalog
     * navigation block to hide the category navigation.
     *
     * @param Varien_Event_Observer $observer
     */
    public function controllerFrontInitBefore(Varien_Event_Observer $observer)
    {
        if ($this->_shouldRewriteOldNavigationBlock()) {
            Mage::getConfig()->setNode(
                'global/blocks/catalog/rewrite/navigation',
                'Netzarbeiter_LoginCatalog_Block_Navigation'
            );
        }
    }

    /**
     * Hide the catalog navigation to logged out visitors if the feature is configured.
     *
     * @param Varien_Event_Observer $observer
     */
    public function pageBlockHtmlTopmenuGethtmlBefore(Varien_Event_Observer $observer)
    {
        if (Mage::helper('logincatalog')->shouldHideCategoryNavigation()) {
            /** @var $menu Varien_Data_Tree_Node */
            $menu = $observer->getData('menu');
            foreach ($menu->getChildren() as $key => $node) {
                if (strpos($key, 'category-') === 0) {
                    $menu->removeChild($node);
                }
            }
        }
    }

    /**
     * Is fired on catalog_product_load_after event, i.e. when
     * a customer views a product page.
     *
     * @param Varien_Event_Observer $observer
     */
    public function catalogProductLoadAfter(Varien_Event_Observer $observer)
    {
        $this->_handlePossibleRedirect();
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
        $this->_handlePossibleRedirect();
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function catalogCategoryLoadAfter(Varien_Event_Observer $observer)
    {
        if (Mage::helper('logincatalog')->getConfig('redirect_for_categories')) {
            if ($this->_requestedRouteMatches(array('catalog', 'category', 'view'))) {
                $this->_handlePossibleRedirect();
            }
        }
    }

    public function controllerActionPredispatch(Varien_Event_Observer $args)
    {
        if (Mage::helper('logincatalog')->getConfig('redirect_on_all_pages')) {
            $this->_handlePossibleRedirect();
        }
    }

    /**
     * Extension point to trigger redirects from other events
     */
    protected function handlePossibleRedirect()
    {
        $this->_handlePossibleRedirect();
    }

    /**
     * If the customer isn't logged in, redirect to account login page.
     */
    private function _handlePossibleRedirect()
    {
        if (!Mage::helper('logincatalog')->moduleActive()) {
            return;
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return;
        }

        if ($this->_isNotApplicableForRequest()) {
            return;
        }

        // Display message if configured
        $this->_addSplashMessageToSession();
        $this->_setAfterAuthUrl();

        $url = $this->_getRedirectTargetUrl();
        Mage::app()->getResponse()->setRedirect($url);

        $this->_redirectSetFlag = true;
    }

    /**
     * Return the configured redirect target URL
     *
     * @return string
     */
    private function _getRedirectTargetUrl()
    {
        if (Mage::helper('logincatalog')->getConfig('redirect_to_page')) {
            return $this->_getCmsPageRedirectTargetUrl();
        } else {
            return $this->_getLoginPareRedirectTargetUrl();
        }
    }

    /**
     * Return true if the request is made via the api
     *
     * @return bool
     */
    private function _isApiRequest()
    {
        return $this->_requestedRouteMatches(array('api'));
    }

    /**
     * Check if redirects for the currently requested route are disabled
     * in the system configuration.
     *
     * @return bool
     */
    private function _isRedirectDisabledForRoute()
    {
        if (!isset($this->_disabledRoutes)) {
            $this->_initializeListOfDisabledRoutes();
        }
        foreach ($this->_disabledRoutes as $route) {
            if ($this->_requestedRouteMatches($route)) {
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
    private function _isLoginPageRequest()
    {
        return
            $this->_requestedRouteMatches(array('customer', 'account', 'login')) ||
            $this->_requestedRouteMatches(array('customer', 'account', 'loginPost')) ||
            $this->_requestedRouteMatches(array('customer', 'account', 'create')) ||
            $this->_requestedRouteMatches(array('customer', 'account', 'createPost')) ||
	    $this->_requestedRouteMatches(array('customer', 'account', 'logoutSuccess')) ||
            $this->_requestedRouteMatches(array('customer', 'account', 'forgotpassword')) ||
            $this->_requestedRouteMatches(array('customer', 'account', 'forgotpasswordPost'));
    }

    /**
     * Check if the current request matches the specified route.
     *
     * @param array $route Format: array('route name', 'controller', 'action')
     * @return bool
     */
    private function _requestedRouteMatches(array $route)
    {
        switch (count($route)) {
            case 1:
                return $this->_moduleMatches($route);
            case 2:
                return $this->_moduleAndControllerMatches($route);
            case 3:
                return $this->_moduleAndControllerAndActionMatches($route);
            default:
                return false;
        }
    }

    private function _setAfterAuthUrl()
    {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $currentUrl = Mage::getSingleton('core/url')->sessionUrlVar($currentUrl);
        Mage::getSingleton('customer/session')->setAfterAuthUrl($currentUrl);
    }

    private function _addSplashMessageToSession()
    {
        $message = Mage::helper('logincatalog')->getConfig('message');
        if (mb_strlen($message, 'UTF-8') > 0 && !$this->_isMessageSetOnSession($message)) {
            Mage::getSingleton('customer/session')->addNotice($message);
        }
    }

    private function _isMessageSetOnSession($message)
    {
        foreach ($this->_getCustomerSessionNotices() as $messageToCheck) {
            if ($messageToCheck->getCode() === $message) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Mage_Core_Model_Message_Abstract[]
     */
    private function _getCustomerSessionNotices()
    {
        return Mage::getSingleton('customer/session')->getMessages()->getItems('notice');
    }

    /**
     * @return bool
     */
    private function _isNotApplicableForRequest()
    {
        return
            $this->_redirectSetFlag ||
            $this->_isLoginPageRequest() ||
            $this->_isApiRequest() ||
            $this->_isRedirectDisabledForRoute();
    }

    /**
     * @param array $route
     * @return bool
     */
    private function _moduleMatches(array $route)
    {
        $moduleName = Mage::app()->getRequest()->getModuleName();
        return $moduleName === $route[self::ROUTE_PART_MODULE];
    }

    /**
     * @param array $route
     * @return bool
     */
    private function _moduleAndControllerMatches(array $route)
    {
        $controllerName = Mage::app()->getRequest()->getControllerName();
        return $this->_moduleMatches($route) && $controllerName === $route[self::ROUTE_PART_CONTROLLER];
    }

    /**
     * @param array $route
     * @return bool
     */
    private function _moduleAndControllerAndActionMatches(array $route)
    {
        $actionName = Mage::app()->getRequest()->getActionName();
        return $this->_moduleAndControllerMatches($route) && $actionName === $route[self::ROUTE_PART_ACTION];
    }

    /**
     * @return string
     */
    private function _getCmsPageRedirectTargetUrl()
    {
        $helper = Mage::helper('logincatalog');

        $page = Mage::getModel('cms/page');
        $page->setStoreId(Mage::app()->getStore()->getId())
            ->load($helper->getConfig('cms_page'), 'identifier');
        if (!$page->getId()) {
            $message = $helper->__('Invalid CMS page configured as a redirect landing page.');
            Mage::throwException($message);
        }
        $params = array('_nosid' => true, '_direct' => $page->getIdentifier());
        return Mage::getUrl(null, $params);
    }

    /**
     * @return string
     */
    private function _getLoginPareRedirectTargetUrl()
    {
        $route = 'customer/account/login';
        $params = array('_nosid' => true);
        return Mage::getUrl($route, $params);
    }

    /**
     * @return bool
     */
    private function _shouldRewriteOldNavigationBlock()
    {
        $isOld = version_compare(Mage::getVersion(), '1.7', '<');
        return $isOld && Mage::helper('logincatalog')->shouldHideCategoryNavigation();
    }

    /**
     * @return null
     */
    private function _initializeListOfDisabledRoutes()
    {
        $this->_disabledRoutes = array();
        if ($routes = Mage::helper('logincatalog')->getConfig('disable_on_routes')) {
            foreach (explode("\n", $routes) as $route) {
                $this->_disabledRoutes[] = explode('/', trim($route));
            }
        }
    }
}

