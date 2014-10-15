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
 *
 * /**
 * @see Netzarbeiter_LoginCatalog_Model_Observer
 * @loadSharedFixture observer.yaml
 * @doNotIndexAll
 */
class Netzarbeiter_LoginCatalog_Test_Model_ObserverTest extends EcomDev_PHPUnit_Test_Case_Controller
{
    /**
     * @test
     * @singleton    logincatalog/observer
     * @helper       logincatalog
     * @dataProvider dataProvider
     */
    public function redirectOnProductLoad($storeCode)
    {
        // Dispatch page with product load
        $this->dispatch('catalog/product/view', array('id' => 1, '_store' => $storeCode));

        $this->assertEventDispatched('catalog_product_load_after');

        $expected = $this->expected('%s', $storeCode);

        if ($expected->getRedirect()) {
            // Assert response is a redirect
            $message = sprintf(
                'Expected but did not find redirect to "%s" in store "%s"',
                Mage::getUrl($expected->getRoute(), $expected->getParams()), $storeCode
            );
            $this->assertRedirect($message);

            // Assert redirect target matches expectations
            $expectedUrl = Mage::getUrl($expected->getRoute(), $expected->getparams());
            $redirectTarget = $this->_getRedirectTarget($this->app()->getResponse());
            $message = sprintf(
                'Expected redirect to "%s" but found target "%s"', $expectedUrl, $redirectTarget
            );
            $this->assertRedirectToUrl($expectedUrl, $message);
        } else {
            $redirectTarget = $this->_getRedirectTarget($this->app()->getResponse());
            $message = sprintf('Unexpected redirect for store "%s" to "%s"', $storeCode, $redirectTarget);
            $this->assertNotRedirect($message);
        }
    }

    /**
     * Return the value of the first found location header
     *
     * @param Mage_Core_Controller_Response_Http $response
     * @return string
     */
    protected function _getRedirectTarget($response)
    {
        $headers = $response->getHeaders();
        if ($headers) {
            foreach ($headers as $header) {
                if ('Location' === $header['name']) {
                    return $header['value'];
                }
            }
        }
        return '';
    }

    /**
     * Test no redirect is triggered on pages where no product is loaded.
     *
     * Note: a homepage without product references in the content is part of the fixtures.
     * Still, this test assumes that no products are loaded on CMS pages.
     *
     * @test
     */
    public function noRedirectOnHome()
    {
        $this->dispatch('cms/index/index', array('_store' => 'usa'));

        $this->assertEventNotDispatched('catalog_product_load_after');
        $this->assertNotRedirect();
    }
}
