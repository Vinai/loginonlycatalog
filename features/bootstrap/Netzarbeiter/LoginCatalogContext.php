<?php


namespace Netzarbeiter;

use Behat\Behat\Context\BehatContext;
use Netzarbeiter\LoginCatalog\CategoryNavigationContext;

class LoginCatalogContext extends BehatContext
{
    public function __construct(array $parameters)
    {
        $this->useContext('netzarbeiter_logincatalog', new CategoryNavigationContext($parameters));
    }
}