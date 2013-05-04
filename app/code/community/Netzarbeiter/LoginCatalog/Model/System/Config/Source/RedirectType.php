<?php


class Netzarbeiter_LoginCatalog_Model_System_Config_Source_RedirectType
{
    public function toOptionArray()
    {
        /* @var $helper Netzarbeiter_LoginCatalog_Helper_Data */
        $helper = Mage::helper('logincatalog');
        return array(
            array('value' => 0, 'label' => $helper->__('Login Page')),
            array('value' => 1, 'label' => $helper->__('CMS Page')),
        );
    }
}