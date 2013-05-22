<?php


namespace Netzarbeiter\LoginCatalog;

use MageTest\MagentoExtension\Context\MagentoContext,
    Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Exception\PendingException;

class CategoryNavigationContext extends MagentoContext
{
    /**
     * @Given /^I am not logged in as a customer$/
     */
    public function iAmNotLoggedInAsACustomer()
    {
        $this->getSessionService()->customerLogout();
        //$this->getSession()->setCookie('frontend', '');
    }

    /**
     * @Given /^I clear the view cache$/
     */
    public function iClearTheViewCache()
    {
        $this->getCacheManager()->addSection(
            'block_html', new \MageTest\MagentoExtension\Service\Cache\BlockHtmlCache($this->getApp())
        );
        $this->getCacheManager()->addSection(
            'layout', new \MageTest\MagentoExtension\Service\Cache\LayoutCache($this->getApp())
        );
        $this->getCacheManager()->clear();
    }

    /**
     * @Then /^I should not see the category navigation$/
     */
    public function iShouldNotSeeTheCategoryNavigation()
    {
        $page = $this->getMink()->getSession()->getPage();
        if ($page->has('css', '#nav')) {
            throw new \Exception('The category navigation is visible even though it should be hidden.');
        }
    }

    /**
     * @Then /^I should see the category navigation$/
     */
    public function iShouldSeeTheCategoryNavigation()
    {
        $page = $this->getMink()->getSession()->getPage();

        if (! $page->has('css', '#nav')) {
            throw new \Exception('The category navigation is not visible even though it should be.');
        }
    }
}