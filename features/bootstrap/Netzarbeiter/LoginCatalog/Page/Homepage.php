<?php


namespace Netzarbeiter\LoginCatalog\Page;


use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Homepage extends Page
{
    protected $path = '/';

    protected $elements = array(
        'Category Navigation' => array('css' => 'li.level0'),
    );
}